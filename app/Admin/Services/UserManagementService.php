<?php

declare(strict_types=1);

namespace App\Admin\Services;

use App\Compliance\ComplianceService;
use App\Core\Auth\UserRepository;
use App\Core\Database;
use App\Exam\ExamAttemptRepository;

class UserManagementService
{
    private const USER_TYPES = ['admin', 'inductee'];
    private const STATUSES = ['active', 'inactive', 'suspended'];

    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(?string $userType = null, ?string $search = null): array
    {
        return $this->users->allWithProfiles($userType, $search);
    }

    public function find(int $id): ?array
    {
        return $this->users->findWithProfile($id);
    }

    /**
     * An administrator creates the account directly, so the address is treated as already
     * verified and no confirmation email is sent.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function create(array $data): array
    {
        $errors = $this->validate($data, null);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $userId = $this->users->create([
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'user_type' => $data['user_type'],
            'status' => $data['status'],
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        if ($data['user_type'] === 'admin') {
            $this->users->createAdminProfile($userId, $data['first_name'], $data['last_name']);
        } else {
            $this->users->createInducteeProfile($userId, $data['first_name'], $data['last_name']);
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function update(int $id, array $data): array
    {
        $existing = $this->users->findById($id);
        if (!$existing) {
            return ['success' => false, 'errors' => ['form' => 'User not found.']];
        }

        $errors = $this->validate($data, $id);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->users->updateEmail($id, $data['email']);
        $this->users->updateStatus($id, $data['status']);
        $this->users->updateProfile($id, $existing['user_type'], $data['first_name'], $data['last_name']);

        if (!empty($data['password'])) {
            $this->users->updatePassword($id, password_hash($data['password'], PASSWORD_DEFAULT));
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * Updates the account password (the users table). Email lives in the
     * same table but is not self-editable, so password is the only field
     * the Account card can change; kept as its own method, separate from
     * updateProfileName(), so a self-service "My Profile" page can offer
     * one form per underlying table.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function updatePassword(int $id, array $data): array
    {
        if (!$this->users->findById($id)) {
            return ['success' => false, 'errors' => ['form' => 'User not found.']];
        }

        $errors = $this->validatePassword($data, false);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        if (!empty($data['password'])) {
            $this->users->updatePassword($id, password_hash($data['password'], PASSWORD_DEFAULT));
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * Updates only the profile fields (the admin_profiles/inductee_profiles
     * table: first and last name). Kept separate from updateAccount() for
     * the same reason -- the fields live in a different table.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function updateProfileName(int $id, array $data): array
    {
        $existing = $this->users->findById($id);
        if (!$existing) {
            return ['success' => false, 'errors' => ['form' => 'User not found.']];
        }

        $errors = $this->validateNames($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->users->updateProfile($id, $existing['user_type'], trim($data['first_name']), trim($data['last_name']));

        return ['success' => true, 'errors' => []];
    }

    /**
     * Deletes a user account. A user with exam attempts or compliance
     * records is blocked unless $cascade is set, since those rows would
     * otherwise be orphaned; the user's admin/inductee profile is removed
     * automatically via its ON DELETE CASCADE foreign key.
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function delete(int $id, bool $cascade = false): array
    {
        if (!$this->users->findById($id)) {
            return ['success' => false, 'errors' => ['form' => 'User not found.']];
        }

        $attempts = new ExamAttemptRepository();
        $compliance = new ComplianceService();

        $attemptCount = $attempts->countForUser($id);
        $complianceCount = $compliance->countForUser($id);

        if (($attemptCount > 0 || $complianceCount > 0) && !$cascade) {
            return ['success' => false, 'errors' => ['form' => sprintf(
                'This user has %d exam attempt(s) and %d compliance record(s). Enable cascade delete to remove them together, or they must be removed first.',
                $attemptCount,
                $complianceCount
            )]];
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $compliance->deleteAllForUser($id);
            $attempts->deleteForUser($id);
            $this->users->delete($id);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array<string, string>
     */
    private function validate(array $data, ?int $ignoreUserId): array
    {
        $errors = [];

        if ($emailError = $this->validateEmail($data['email'] ?? '', $ignoreUserId)) {
            $errors['email'] = $emailError;
        }

        $errors += $this->validateNames($data);

        if ($ignoreUserId === null && !in_array($data['user_type'] ?? '', self::USER_TYPES, true)) {
            $errors['user_type'] = 'Select a valid user type.';
        }

        if (!in_array($data['status'] ?? '', self::STATUSES, true)) {
            $errors['status'] = 'Select a valid status.';
        }

        $errors += $this->validatePassword($data, $ignoreUserId === null);

        return $errors;
    }

    private function validateEmail(string $email, ?int $ignoreUserId): ?string
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter a valid email address.';
        }

        $existing = $this->users->findByEmail($email);
        if ($existing && (int) $existing['id'] !== $ignoreUserId) {
            return 'An account with this email already exists.';
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function validateNames(array $data): array
    {
        $errors = [];

        if (trim($data['first_name'] ?? '') === '') {
            $errors['first_name'] = 'First name is required.';
        }

        if (trim($data['last_name'] ?? '') === '') {
            $errors['last_name'] = 'Last name is required.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    private function validatePassword(array $data, bool $required): array
    {
        $errors = [];
        $password = $data['password'] ?? '';

        if ($required || $password !== '') {
            if (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            } elseif ($password !== ($data['password_confirmation'] ?? '')) {
                $errors['password_confirmation'] = 'Passwords do not match.';
            }
        }

        return $errors;
    }
}
