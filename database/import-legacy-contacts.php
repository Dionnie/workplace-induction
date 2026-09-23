<?php

declare(strict_types=1);

/**
 * One-off CLI import of legacy contacts + their completed inductions from
 * the previous (Supabase-based) induction system, into this app's
 * users / inductee_profiles / compliance_records tables.
 *
 * Usage:
 *   php database/import-legacy-contacts.php path/to/contacts_admin_view_rows.json [--commit]
 *
 * Without --commit, this only parses, normalizes, and prints a summary --
 * nothing is written. Pass --commit to actually perform the writes (inside
 * a single transaction, all-or-nothing).
 *
 * Every legacy contact becomes an inductee account here, regardless of
 * their legacy "role" -- granting admin access in this app based on old
 * CSV data is a separate decision nobody asked for.
 *
 * A contact with a completed session (non-null session_id) also gets a
 * compliance_records row, backdated to when they actually completed it --
 * not to today -- and its password is the legacy session_id itself. A
 * contact with no session gets a random, unguessable password instead
 * (they'd use "Forgot Password" to gain access).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script is CLI-only.');
}

require __DIR__ . '/../bootstrap.php';

use App\Core\Database;

const TARGET_INDUCTION_ID = 1;

const EMPLOYMENT_TYPES = [
    'Full-time', 'Part-time', 'Casual', 'Contractor', 'Sub-contractor', 'Apprentice', 'Trainee', 'Shift-worker', 'Other',
];

/**
 * Every distinct legacy "company" value that means Stark Food Systems,
 * however it was spelled -- reviewed and approved against the full 148-row
 * dataset before this script was written. Every other company value is
 * imported verbatim, even if it has its own minor casing/typo variants.
 */
const STARK_FOOD_SYSTEMS_VARIANTS = [
    'SFS', 'SFS Contractor', 'SFS contractor', "SFS`", 'SFS SSS',
    'Sfs', 'sfs',
    'STRAK FOOD SYSTEM', 'Stack Food systems',
    'Stark Food System', 'Stark Food Systems', 'Stark Food Systems Pty Ltd',
    'Stark food Systems', 'Stark food system', 'Stark food systems', 'stark food systems',
    'Stark foods', 'Stark foods systems', 'stark food services',
    'StarkFoodSystems', 'Starks Food Syatems',
];

function normalizeCompany(string $company): string
{
    $trimmed = trim($company);
    return in_array($trimmed, STARK_FOOD_SYSTEMS_VARIANTS, true) ? 'Stark Food Systems' : $trimmed;
}

/**
 * Legacy timestamps look like "2025-01-13 05:23:55.359403+00". Returns
 * ['datetime' => 'Y-m-d H:i:s', 'date' => 'Y-m-d'].
 *
 * @return array{datetime: string, date: string}
 */
function parseLegacyTimestamp(string $raw): array
{
    $dt = new DateTimeImmutable($raw);
    return ['datetime' => $dt->format('Y-m-d H:i:s'), 'date' => $dt->format('Y-m-d')];
}

$args = array_slice($argv, 1);
$commit = in_array('--commit', $args, true);
$jsonPath = null;
foreach ($args as $arg) {
    if ($arg !== '--commit' && $arg !== '--dry-run') {
        $jsonPath = $arg;
    }
}

if ($jsonPath === null || !is_file($jsonPath)) {
    fwrite(STDERR, "Usage: php database/import-legacy-contacts.php path/to/contacts_admin_view_rows.json [--commit]\n");
    exit(1);
}

$rows = json_decode((string) file_get_contents($jsonPath), true);
if (!is_array($rows)) {
    fwrite(STDERR, "Could not parse {$jsonPath} as a JSON array.\n");
    exit(1);
}

$db = Database::connection();

$existingEmails = [];
foreach ($db->query('SELECT LOWER(email) AS email FROM users')->fetchAll(PDO::FETCH_COLUMN) as $email) {
    $existingEmails[$email] = true;
}

$plan = [];
$skippedExisting = [];
$invalidEmploymentType = [];
$companyChanges = [];

foreach ($rows as $row) {
    $email = trim((string) $row['email']);
    $emailKey = strtolower($email);

    if (isset($existingEmails[$emailKey])) {
        $skippedExisting[] = $email;
        continue;
    }

    $employmentType = trim((string) $row['employment_type']);
    if (!in_array($employmentType, EMPLOYMENT_TYPES, true)) {
        $invalidEmploymentType[] = $email . ' (' . $employmentType . ')';
        continue;
    }

    $company = normalizeCompany((string) $row['company']);
    if ($company !== trim((string) $row['company'])) {
        $companyChanges[trim((string) $row['company'])] = $company;
    }

    $hasSession = $row['session_id'] !== null;
    $password = $hasSession ? (string) $row['session_id'] : bin2hex(random_bytes(16));

    $compliance = null;
    if ($hasSession) {
        $issued = parseLegacyTimestamp((string) $row['session_created_at']);
        $compliance = [
            'legacy_id' => (string) $row['session_id'],
            'issue_date' => $issued['date'],
            'created_at' => $issued['datetime'],
            'expiry_date' => (string) $row['session_valid_until'],
        ];
    }

    $plan[] = [
        'email' => $email,
        'password' => $password,
        'first_name' => trim((string) $row['first_name']),
        'last_name' => trim((string) $row['last_name']),
        'company' => $company,
        'employment_type' => $employmentType,
        'compliance' => $compliance,
        'legacy_role' => (string) $row['role'],
    ];

    // Guard against two rows in this same import batch sharing an email.
    $existingEmails[$emailKey] = true;
}

$toImport = count($plan);
$withCompliance = count(array_filter($plan, fn ($p) => $p['compliance'] !== null));

echo "=== Legacy contact import " . ($commit ? '(COMMIT)' : '(DRY RUN)') . " ===\n";
echo "Source rows: " . count($rows) . "\n";
echo "To import: {$toImport} (with compliance record: {$withCompliance})\n";
echo "Skipped (email already exists): " . count($skippedExisting) . "\n";
foreach ($skippedExisting as $email) {
    echo "  - {$email}\n";
}
if ($invalidEmploymentType) {
    echo "HALTED-ON-ROW employment_type not in canonical list (fix the source data or the EMPLOYMENT_TYPES list): \n";
    foreach ($invalidEmploymentType as $entry) {
        echo "  - {$entry}\n";
    }
    exit(1);
}
echo "Company values normalized to \"Stark Food Systems\":\n";
foreach ($companyChanges as $from => $to) {
    echo "  - \"{$from}\" -> \"{$to}\"\n";
}

if (!$commit) {
    echo "\nDry run only -- nothing written. Re-run with --commit to apply.\n";
    exit(0);
}

$db->beginTransaction();
try {
    $createdUsers = 0;
    $createdCompliance = 0;

    foreach ($plan as $item) {
        $stmt = $db->prepare(
            'INSERT INTO users (email, password, user_type, status, email_verified_at)
             VALUES (:email, :password, \'inductee\', \'active\', NOW())'
        );
        $stmt->execute([
            'email' => $item['email'],
            'password' => password_hash($item['password'], PASSWORD_DEFAULT),
        ]);
        $userId = (int) $db->lastInsertId();
        $createdUsers++;

        $stmt = $db->prepare(
            'INSERT INTO inductee_profiles (user_id, first_name, last_name, company, employment_type)
             VALUES (:user_id, :first_name, :last_name, :company, :employment_type)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'first_name' => $item['first_name'],
            'last_name' => $item['last_name'],
            'company' => $item['company'],
            'employment_type' => $item['employment_type'],
        ]);

        if ($item['compliance'] !== null) {
            $c = $item['compliance'];

            $stmt = $db->prepare(
                'INSERT INTO compliance_records
                    (user_id, induction_id, exam_attempt_id, verification_token, certificate_number,
                     issue_date, expiry_date, status, renewed_from_id, legacy_id, created_at)
                 VALUES
                    (:user_id, :induction_id, NULL, :verification_token, \'PENDING\',
                     :issue_date, :expiry_date, \'active\', NULL, :legacy_id, :created_at)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'induction_id' => TARGET_INDUCTION_ID,
                'verification_token' => bin2hex(random_bytes(32)),
                'issue_date' => $c['issue_date'],
                'expiry_date' => $c['expiry_date'],
                'legacy_id' => $c['legacy_id'],
                'created_at' => $c['created_at'],
            ]);
            $complianceId = (int) $db->lastInsertId();

            $issueYear = substr($c['issue_date'], 0, 4);
            $certificateNumber = sprintf('CERT-%s-%06d', $issueYear, $complianceId);
            $stmt = $db->prepare('UPDATE compliance_records SET certificate_number = ? WHERE id = ?');
            $stmt->execute([$certificateNumber, $complianceId]);

            $createdCompliance++;
        }
    }

    $db->commit();
    echo "\nCommitted: {$createdUsers} user(s), {$createdCompliance} compliance record(s).\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, 'Import failed, rolled back: ' . $e->getMessage() . "\n");
    exit(1);
}
