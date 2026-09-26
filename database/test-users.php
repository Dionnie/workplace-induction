<?php

declare(strict_types=1);

/**
 * Temporary test accounts for end-to-end testing (docs/rules/testing.md §2).
 *
 * Usage:
 *   php database/test-users.php create admin|inductee [--incomplete]
 *   php database/test-users.php list
 *   php database/test-users.php delete <email>|--all
 *
 * "create" makes an active, email-verified account with a random password
 * and prints the login. Inductees get a completed profile unless
 * --incomplete is passed (to test profile completion). No emails are sent.
 *
 * Every test account's email is "e2e-<type>-<8 hex>@test.invalid". The
 * .invalid domain can never receive email, and "delete" refuses any account
 * without that marker, so this script can't touch a real account. Deleting
 * also removes the account's exam attempts and compliance records.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script is CLI-only.');
}

require __DIR__ . '/../bootstrap.php';

use App\Admin\Services\UserManagementService;
use App\Core\Auth\UserRepository;
use App\Core\Database;
use App\Inductee\InducteeProfileService;

const TEST_EMAIL_PATTERN = '/^e2e-(admin|inductee)-[0-9a-f]{8}@test\.invalid$/';

function fail(string $message): never
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

/**
 * @return array<int, array<string, mixed>>
 */
function test_accounts(): array
{
    $rows = Database::connection()
        ->query("SELECT id, email, user_type, profile_completed, created_at FROM users WHERE email LIKE 'e2e-%@test.invalid' ORDER BY id")
        ->fetchAll();

    return array_values(array_filter($rows, fn (array $row): bool => preg_match(TEST_EMAIL_PATTERN, $row['email']) === 1));
}

function delete_test_account(array $account): void
{
    $result = (new UserManagementService())->delete((int) $account['id'], true);
    if (!$result['success']) {
        fail("Could not delete {$account['email']}: " . ($result['errors']['form'] ?? 'unknown error'));
    }
    echo "Deleted {$account['email']} (user {$account['id']}).\n";
}

$command = $argv[1] ?? '';

if ($command === 'create') {
    $type = $argv[2] ?? '';
    if (!in_array($type, ['admin', 'inductee'], true)) {
        fail('Usage: php database/test-users.php create admin|inductee [--incomplete]');
    }
    $complete = !in_array('--incomplete', $argv, true);

    $email = "e2e-{$type}-" . bin2hex(random_bytes(4)) . '@test.invalid';
    $password = bin2hex(random_bytes(8));

    $users = new UserRepository();
    $id = $users->create([
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'user_type' => $type,
        'status' => 'active',
        'profile_completed' => $type === 'admin',
        'email_verified_at' => date('Y-m-d H:i:s'),
    ]);

    if ($type === 'admin') {
        $users->updateProfile($id, 'admin', 'E2E', 'Test Admin');
    } elseif ($complete) {
        (new InducteeProfileService())->update($id, [
            'first_name' => 'E2E',
            'last_name' => 'Test Inductee',
            'job_position' => '',
            'company' => 'E2E Test',
            'employment_type' => 'Other',
            'contact_number' => '0000 000 000',
        ]);
    }

    $profile = $type === 'admin' ? '' : ($complete ? ', profile complete' : ', profile incomplete');
    echo "Created {$type} (user {$id}{$profile})\n";
    echo "  email:    {$email}\n";
    echo "  password: {$password}\n";
    echo "Delete when done: php database/test-users.php delete {$email}\n";
    exit(0);
}

if ($command === 'list') {
    $accounts = test_accounts();
    foreach ($accounts as $account) {
        $profile = $account['profile_completed'] ? 'complete' : 'incomplete';
        echo "{$account['id']}\t{$account['email']}\t{$account['user_type']}\tprofile {$profile}\tcreated {$account['created_at']}\n";
    }
    echo count($accounts) . " test account(s).\n";
    exit(0);
}

if ($command === 'delete') {
    $target = $argv[2] ?? '';

    if ($target === '--all') {
        $accounts = test_accounts();
        array_walk($accounts, 'delete_test_account');
        echo count($accounts) . " test account(s) deleted.\n";
        exit(0);
    }

    if (preg_match(TEST_EMAIL_PATTERN, $target) !== 1) {
        fail("Refused: {$target} is not a test account (e2e-<type>-<8 hex>@test.invalid).");
    }

    $account = (new UserRepository())->findByEmail($target);
    if (!$account) {
        fail("No account with email {$target}.");
    }

    delete_test_account($account);
    exit(0);
}

fail("Usage:\n"
    . "  php database/test-users.php create admin|inductee [--incomplete]\n"
    . "  php database/test-users.php list\n"
    . "  php database/test-users.php delete <email>|--all");
