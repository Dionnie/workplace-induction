<?php

declare(strict_types=1);

namespace App\Admin\Services;

use App\Compliance\ComplianceService;
use App\Core\Auth\UserRepository;
use App\Core\Database;
use App\Exam\ExamAttemptRepository;
use App\Inductee\InducteeProfileService;

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
     * Creates the account (the users table only). An administrator creates
     * it directly, so the address is treated as already verified and no
     * confirmation email is sent. An inductee then completes their own
     * profile when they first log in; administrators have no profile
     * requirements, so theirs counts as complete (docs/core/auth.md #3, #12).
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function create(array $data): array
    {
        $errors = $this->validate($data, null);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->users->create([
            'email' => trim($data['email']),
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'user_type' => $data['user_type'],
            'status' => $data['status'],
            'profile_completed' => $data['user_type'] === 'admin',
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Updates the account (the users table only). Profile Completed can be
     * turned off to have an inductee review their profile again, but only
     * turned on when their profile has every required field. Email Verified
     * can only be turned on (e.g. when the verification email never arrived).
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function update(int $id, array $data): array
    {
        $existing = $this->users->findById($id);
        if (!$existing) {
            return ['success' => false, 'errors' => ['form' => 'User not found.']];
        }

        $errors = $this->validate($data, $id);

        $isInductee = $existing['user_type'] === 'inductee';
        $profileCompleted = !empty($data['profile_completed']);
        if ($isInductee && $profileCompleted && empty($existing['profile_completed'])) {
            $missing = (new InducteeProfileService())->missingFields($id);
            if ($missing) {
                $errors['profile_completed'] = 'Their profile is missing: ' . implode(', ', $missing)
                    . '. They are asked for these when they next log in.';
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->users->updateEmail($id, trim($data['email']));
        $this->users->updateStatus($id, $data['status']);

        if ($isInductee) {
            $this->users->setProfileCompleted($id, $profileCompleted);
        }

        if ($existing['email_verified_at'] === null && !empty($data['email_verified'])) {
            $this->users->markEmailVerified($id);
        }

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
     * table: first and last name), creating the profile row if the account
     * has none yet. Kept separate from updatePassword() for the same reason
     * -- the fields live in a different table. Used by an administrator's
     * own My Profile page; a name is all an administrator's profile needs.
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
        $this->users->setProfileCompleted($id, true);

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
