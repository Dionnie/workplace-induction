<?php

declare(strict_types=1);

/**
 * Migration: fills inductee_profiles.contact_number for inductees imported
 * from the previous induction system, whose mobile number wasn't imported.
 * Reads the previous system's contacts export and matches it to accounts by
 * email (ignoring case). Only profiles with no contact number are filled, so
 * a number an inductee has since entered is kept, and running it twice
 * changes nothing. A number that fails the profile's phone check is skipped
 * and listed. updated_at is kept: this isn't an edit by the inductee.
 *
 * Back up the database and test on a copy before applying (see
 * docs/rules/data-protection.md). Dry run, then write:
 *   php database/migrations/2026-09-27-legacy-contact-numbers.php path/to/contacts_admin_view_rows.json
 *   php database/migrations/2026-09-27-legacy-contact-numbers.php path/to/contacts_admin_view_rows.json --commit
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script is CLI-only.');
}

require __DIR__ . '/../../bootstrap.php';

use App\Core\Database;
use App\Inductee\InducteeProfileService;

$args = array_slice($argv, 1);
$commit = in_array('--commit', $args, true);
$jsonPath = null;
foreach ($args as $arg) {
    if ($arg !== '--commit') {
        $jsonPath = $arg;
    }
}

if ($jsonPath === null || !is_file($jsonPath)) {
    fwrite(STDERR, "Usage: php database/migrations/2026-09-27-legacy-contact-numbers.php path/to/contacts_admin_view_rows.json [--commit]\n");
    exit(1);
}

$rows = json_decode((string) file_get_contents($jsonPath), true);
if (!is_array($rows)) {
    fwrite(STDERR, "Could not parse {$jsonPath} as a JSON array.\n");
    exit(1);
}

$db = Database::connection();

// Inductee profiles by lower-cased email.
$profiles = [];
$stmt = $db->query(
    "SELECT u.id, LOWER(u.email) AS email, ip.contact_number
     FROM users u
     JOIN inductee_profiles ip ON ip.user_id = u.id
     WHERE u.user_type = 'inductee'"
);
foreach ($stmt->fetchAll() as $row) {
    $profiles[$row['email']] = $row;
}

$toFill = [];
$alreadySet = [];
$noAccount = [];
$invalid = [];

foreach ($rows as $row) {
    $email = strtolower(trim((string) ($row['email'] ?? '')));
    $number = trim((string) ($row['mobile_number'] ?? ''));

    if (!isset($profiles[$email])) {
        $noAccount[] = $email;
        continue;
    }
    if (trim((string) $profiles[$email]['contact_number']) !== '') {
        $alreadySet[] = $email;
        continue;
    }
    if (!preg_match(InducteeProfileService::PHONE_PATTERN, $number)) {
        $invalid[] = "{$email} (\"{$number}\")";
        continue;
    }

    $toFill[] = ['user_id' => (int) $profiles[$email]['id'], 'email' => $email, 'number' => $number];
}

echo "=== Legacy contact numbers " . ($commit ? '(COMMIT)' : '(DRY RUN)') . " ===\n";
echo "Export rows: " . count($rows) . "\n";
echo "To fill: " . count($toFill) . "\n";
echo "Skipped, profile already has a number: " . count($alreadySet) . "\n";
foreach ($alreadySet as $email) {
    echo "  - {$email}\n";
}
echo "Skipped, no inductee profile with this email: " . count($noAccount) . "\n";
foreach ($noAccount as $email) {
    echo "  - {$email}\n";
}
echo "Skipped, number fails the profile's phone check: " . count($invalid) . "\n";
foreach ($invalid as $entry) {
    echo "  - {$entry}\n";
}

if (!$commit) {
    echo "\nDry run only -- nothing written. Re-run with --commit to apply.\n";
    exit(0);
}

$db->beginTransaction();
try {
    $stmt = $db->prepare(
        "UPDATE inductee_profiles
         SET contact_number = :number, updated_at = updated_at
         WHERE user_id = :user_id AND (contact_number IS NULL OR contact_number = '')"
    );
    $filled = 0;
    foreach ($toFill as $item) {
        $stmt->execute(['number' => $item['number'], 'user_id' => $item['user_id']]);
        $filled += $stmt->rowCount();
    }

    if ($filled !== count($toFill)) {
        throw new RuntimeException("Expected to fill " . count($toFill) . " profiles, filled {$filled}.");
    }

    $db->commit();
    echo "\nCommitted: {$filled} contact number(s) filled.\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, 'Migration failed, rolled back: ' . $e->getMessage() . "\n");
    exit(1);
}
