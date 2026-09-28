<?php

declare(strict_types=1);

/**
 * Migration: updates users.created_at to match legacy registration timestamps,
 * and imports any new users along with their inductee profiles and compliance records.
 *
 * Background:
 * When legacy users were previously imported, users.created_at was stamped with
 * the import time rather than their originating creation date from the legacy system.
 * This migration:
 * 1. Matches users by email (case-insensitive).
 * 2. Updates users.created_at (and inductee_profiles.created_at) to match the file's created_at.
 * 3. Identifies new users not present in the database, inserts their user account,
 *    inductee profile, and their legacy compliance record (storing session_id in legacy_id).
 *
 * Back up the database and test on a copy before applying (see docs/rules/data-protection.md).
 * Dry run, then write:
 *   php database/migrations/2026-09-28-legacy-users-update.php [path/to/legacy_users.json]
 *   php database/migrations/2026-09-28-legacy-users-update.php [path/to/legacy_users.json] --commit
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script is CLI-only.');
}

require __DIR__ . '/../../bootstrap.php';

use App\Core\Database;

$args = array_slice($argv, 1);
$commit = in_array('--commit', $args, true);
$jsonPath = null;
foreach ($args as $arg) {
    if ($arg !== '--commit') {
        $jsonPath = $arg;
    }
}

if ($jsonPath === null) {
    $defaultPath = __DIR__ . '/../legacy-users-updated.json';
    if (is_file($defaultPath)) {
        $jsonPath = $defaultPath;
    } else {
        fwrite(STDERR, "Usage: php database/migrations/2026-09-28-legacy-users-update.php [path/to/legacy_users.json] [--commit]\n");
        exit(1);
    }
}

if (!is_file($jsonPath)) {
    fwrite(STDERR, "Error: File not found: {$jsonPath}\n");
    exit(1);
}

$rows = json_decode((string) file_get_contents($jsonPath), true);
if (!is_array($rows) || empty($rows)) {
    fwrite(STDERR, "Error: Could not parse {$jsonPath} as a non-empty JSON array.\n");
    exit(1);
}

$db = Database::connection();

// Verify induction ID 1 exists (target for Stark Food Systems induction)
$stmt = $db->query("SELECT id, title FROM inductions WHERE id = 1");
$targetInduction = $stmt->fetch();
if (!$targetInduction) {
    fwrite(STDERR, "Error: Induction ID 1 not found in database.\n");
    exit(1);
}

// 1. Fetch current users keyed by lowercase email
$stmt = $db->query("SELECT u.id, LOWER(u.email) AS email, u.created_at, u.user_type,
                           ip.first_name, ip.last_name, ip.created_at AS profile_created_at
                    FROM users u
                    LEFT JOIN inductee_profiles ip ON ip.user_id = u.id");
$existingUsers = [];
foreach ($stmt->fetchAll() as $row) {
    $existingUsers[$row['email']] = $row;
}

// 2. Fetch existing compliance records keyed by legacy_id
$stmt = $db->query("SELECT id, user_id, legacy_id, certificate_number, issue_date, expiry_date, status
                    FROM compliance_records
                    WHERE legacy_id IS NOT NULL AND legacy_id != ''");
$existingCompliance = [];
foreach ($stmt->fetchAll() as $row) {
    $existingCompliance[$row['legacy_id']] = $row;
}

$usersToUpdateCreatedAt = [];
$newUsersToCreate = [];
$missingComplianceForExistingUsers = [];

foreach ($rows as $row) {
    $email = strtolower(trim((string) ($row['email'] ?? '')));
    if ($email === '') {
        continue;
    }

    $fileCreatedAtRaw = (string) ($row['created_at'] ?? '');
    try {
        $dt = new DateTimeImmutable($fileCreatedAtRaw);
        $fileCreatedAt = $dt->format('Y-m-d H:i:s');
    } catch (Throwable $e) {
        fwrite(STDERR, "Invalid created_at '{$fileCreatedAtRaw}' for email {$email}: {$e->getMessage()}\n");
        continue;
    }

    $sessionId = !empty($row['session_id']) ? trim((string) $row['session_id']) : null;

    if (isset($existingUsers[$email])) {
        $existing = $existingUsers[$email];
        $currentCreatedAt = $existing['created_at'];

        if ($currentCreatedAt !== $fileCreatedAt) {
            $usersToUpdateCreatedAt[] = [
                'id' => (int) $existing['id'],
                'email' => $email,
                'current_created_at' => $currentCreatedAt,
                'new_created_at' => $fileCreatedAt,
                'has_profile' => $existing['first_name'] !== null,
            ];
        }

        // Check if existing user has a session_id in the file that isn't yet in compliance_records
        if ($sessionId !== null && !isset($existingCompliance[$sessionId])) {
            $missingComplianceForExistingUsers[] = [
                'user_id' => (int) $existing['id'],
                'email' => $email,
                'row' => $row,
            ];
        }
    } else {
        $newUsersToCreate[] = [
            'email' => $email,
            'created_at' => $fileCreatedAt,
            'row' => $row,
        ];
    }
}

echo "=== Legacy Users & Compliance Update " . ($commit ? '(COMMIT)' : '(DRY RUN)') . " ===\n";
echo "Source JSON file: {$jsonPath}\n";
echo "Total records in file: " . count($rows) . "\n";
echo "Existing users in DB: " . count($existingUsers) . "\n";
echo "Users with created_at to update: " . count($usersToUpdateCreatedAt) . "\n";
echo "New users to create: " . count($newUsersToCreate) . "\n";
echo "Existing users missing compliance record: " . count($missingComplianceForExistingUsers) . "\n\n";

if (!empty($newUsersToCreate)) {
    echo "--- New Users To Create ---\n";
    foreach ($newUsersToCreate as $nu) {
        $r = $nu['row'];
        $hasSession = !empty($r['session_id']) ? "yes ({$r['session_id']})" : "no";
        echo "  - {$nu['email']}: {$r['first_name']} {$r['last_name']}, company: {$r['company']}, type: {$r['employment_type']}, created: {$nu['created_at']}, compliance session: {$hasSession}\n";
    }
    echo "\n";
}

if (!empty($usersToUpdateCreatedAt)) {
    echo "--- Sample Created_at Updates (first 10 of " . count($usersToUpdateCreatedAt) . ") ---\n";
    foreach (array_slice($usersToUpdateCreatedAt, 0, 10) as $u) {
        echo "  - User {$u['id']} ({$u['email']}): {$u['current_created_at']} -> {$u['new_created_at']}\n";
    }
    echo "\n";
}

if (!$commit) {
    echo "Dry run only -- no changes made. Re-run with --commit to apply.\n";
    exit(0);
}

// EXECUTE IN TRANSACTION
$db->beginTransaction();

try {
    // Step 1: Update created_at on users and inductee_profiles for existing accounts
    $stmtUpdateUser = $db->prepare(
        "UPDATE users
         SET created_at = :created_at, updated_at = updated_at
         WHERE id = :id"
    );
    $stmtUpdateProfile = $db->prepare(
        "UPDATE inductee_profiles
         SET created_at = :created_at, updated_at = updated_at
         WHERE user_id = :id"
    );

    $updatedUsersCount = 0;
    foreach ($usersToUpdateCreatedAt as $item) {
        $stmtUpdateUser->execute([
            'created_at' => $item['new_created_at'],
            'id' => $item['id'],
        ]);
        if ($item['has_profile']) {
            $stmtUpdateProfile->execute([
                'created_at' => $item['new_created_at'],
                'id' => $item['id'],
            ]);
        }
        $updatedUsersCount++;
    }

    echo "Updated created_at for {$updatedUsersCount} existing users (and profiles).\n";

    // Step 2: Insert new users, profiles, and compliance records
    $stmtInsertUser = $db->prepare(
        "INSERT INTO users
            (email, password, user_type, status, profile_completed, email_verified_at, created_at, updated_at)
         VALUES
            (:email, :password, :user_type, :status, :profile_completed, :email_verified_at, :created_at, :updated_at)"
    );

    $stmtInsertProfile = $db->prepare(
        "INSERT INTO inductee_profiles
            (user_id, first_name, last_name, contact_number, job_position, company, employment_type, created_at, updated_at)
         VALUES
            (:user_id, :first_name, :last_name, :contact_number, :job_position, :company, :employment_type, :created_at, :updated_at)"
    );

    $stmtInsertCompliance = $db->prepare(
        "INSERT INTO compliance_records
            (user_id, induction_id, exam_attempt_id, verification_token, certificate_number,
             issue_date, expiry_date, status, renewed_from_id, legacy_id, expiry_reminder_sent_at, created_at, updated_at)
         VALUES
            (:user_id, :induction_id, :exam_attempt_id, :verification_token, :certificate_number,
             :issue_date, :expiry_date, :status, :renewed_from_id, :legacy_id, :expiry_reminder_sent_at, :created_at, :updated_at)"
    );

    $stmtUpdateCertNumber = $db->prepare(
        "UPDATE compliance_records SET certificate_number = :certificate_number, updated_at = updated_at WHERE id = :id"
    );

    $validEmploymentTypes = [
        'Full-time', 'Part-time', 'Casual', 'Contractor',
        'Sub-contractor', 'Apprentice', 'Trainee', 'Shift-worker', 'Other'
    ];

    $createdUsersCount = 0;
    $createdComplianceCount = 0;

    foreach ($newUsersToCreate as $nu) {
        $r = $nu['row'];
        $userCreatedAt = $nu['created_at'];

        // Random unusable password (imported users set up password via reset/welcome)
        $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $userType = strtolower((string) ($r['role'] ?? '')) === 'admin' ? 'admin' : 'inductee';

        $stmtInsertUser->execute([
            'email' => $nu['email'],
            'password' => $passwordHash,
            'user_type' => $userType,
            'status' => 'active',
            'profile_completed' => 1,
            'email_verified_at' => $userCreatedAt,
            'created_at' => $userCreatedAt,
            'updated_at' => $userCreatedAt,
        ]);

        $userId = (int) $db->lastInsertId();

        // Normalize employment type
        $empType = trim((string) ($r['employment_type'] ?? ''));
        if (!in_array($empType, $validEmploymentTypes, true)) {
            $empType = 'Other';
        }

        $stmtInsertProfile->execute([
            'user_id' => $userId,
            'first_name' => trim((string) ($r['first_name'] ?? '')),
            'last_name' => trim((string) ($r['last_name'] ?? '')),
            'contact_number' => trim((string) ($r['mobile_number'] ?? '')) ?: null,
            'job_position' => null,
            'company' => trim((string) ($r['company'] ?? '')) ?: null,
            'employment_type' => $empType,
            'created_at' => $userCreatedAt,
            'updated_at' => $userCreatedAt,
        ]);

        $createdUsersCount++;

        // Insert compliance record if session_id is provided
        $sessionId = !empty($r['session_id']) ? trim((string) $r['session_id']) : null;
        if ($sessionId !== null) {
            $sessionCreatedAtRaw = (string) ($r['session_created_at'] ?? $r['created_at']);
            $sessionValidUntilRaw = (string) ($r['session_valid_until'] ?? '');

            $dtSessionCreated = new DateTimeImmutable($sessionCreatedAtRaw);
            $dtSessionExpiry = new DateTimeImmutable($sessionValidUntilRaw);

            $issueDate = $dtSessionCreated->format('Y-m-d');
            $expiryDate = $dtSessionExpiry->format('Y-m-d');
            $recordCreatedAt = $dtSessionCreated->format('Y-m-d H:i:s');
            $verificationToken = bin2hex(random_bytes(32));

            $stmtInsertCompliance->execute([
                'user_id' => $userId,
                'induction_id' => 1,
                'exam_attempt_id' => null,
                'verification_token' => $verificationToken,
                'certificate_number' => 'PENDING',
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
                'status' => 'active',
                'renewed_from_id' => null,
                'legacy_id' => $sessionId,
                'expiry_reminder_sent_at' => null,
                'created_at' => $recordCreatedAt,
                'updated_at' => $recordCreatedAt,
            ]);

            $complianceId = (int) $db->lastInsertId();
            $certificateNumber = sprintf('CERT-%s-%06d', $dtSessionCreated->format('Y'), $complianceId);
            $stmtUpdateCertNumber->execute([
                'certificate_number' => $certificateNumber,
                'id' => $complianceId,
            ]);

            $createdComplianceCount++;
            echo "Created User {$userId} ({$nu['email']}) and Compliance Record {$complianceId} ({$certificateNumber}).\n";
        } else {
            echo "Created User {$userId} ({$nu['email']}) without compliance record (no session_id).\n";
        }
    }

    // Step 3: Insert any missing compliance records for existing users
    foreach ($missingComplianceForExistingUsers as $m) {
        $r = $m['row'];
        $sessionId = trim((string) $r['session_id']);
        $sessionCreatedAtRaw = (string) ($r['session_created_at'] ?? $r['created_at']);
        $sessionValidUntilRaw = (string) ($r['session_valid_until'] ?? '');

        $dtSessionCreated = new DateTimeImmutable($sessionCreatedAtRaw);
        $dtSessionExpiry = new DateTimeImmutable($sessionValidUntilRaw);

        $issueDate = $dtSessionCreated->format('Y-m-d');
        $expiryDate = $dtSessionExpiry->format('Y-m-d');
        $recordCreatedAt = $dtSessionCreated->format('Y-m-d H:i:s');
        $verificationToken = bin2hex(random_bytes(32));

        $stmtInsertCompliance->execute([
            'user_id' => $m['user_id'],
            'induction_id' => 1,
            'exam_attempt_id' => null,
            'verification_token' => $verificationToken,
            'certificate_number' => 'PENDING',
            'issue_date' => $issueDate,
            'expiry_date' => $expiryDate,
            'status' => 'active',
            'renewed_from_id' => null,
            'legacy_id' => $sessionId,
            'expiry_reminder_sent_at' => null,
            'created_at' => $recordCreatedAt,
            'updated_at' => $recordCreatedAt,
        ]);

        $complianceId = (int) $db->lastInsertId();
        $certificateNumber = sprintf('CERT-%s-%06d', $dtSessionCreated->format('Y'), $complianceId);
        $stmtUpdateCertNumber->execute([
            'certificate_number' => $certificateNumber,
            'id' => $complianceId,
        ]);

        $createdComplianceCount++;
        echo "Added missing Compliance Record {$complianceId} ({$certificateNumber}) for existing user {$m['user_id']} ({$m['email']}).\n";
    }

    $db->commit();

    echo "\n=== Migration SUCCESS ===\n";
    echo "Users updated: {$updatedUsersCount}\n";
    echo "New users created: {$createdUsersCount}\n";
    echo "Compliance records created: {$createdComplianceCount}\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, "\nMigration failed and was rolled back: " . $e->getMessage() . "\n");
    exit(1);
}
