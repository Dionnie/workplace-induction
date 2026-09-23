<?php

declare(strict_types=1);

namespace App\Admin\Services;

use App\Core\Auth\UserRepository;

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
     * @return array<string, string>
     */
    private function validate(array $data, ?int $ignoreUserId): array
    {
        $errors = [];

        $email = trim($data['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } else {
            $existing = $this->users->findByEmail($email);
            if ($existing && (int) $existing['id'] !== $ignoreUserId) {
                $errors['email'] = 'An account with this email already exists.';
            }
        }

        if (trim($data['first_name'] ?? '') === '') {
            $errors['first_name'] = 'First name is required.';
        }

        if (trim($data['last_name'] ?? '') === '') {
            $errors['last_name'] = 'Last name is required.';
        }

        if ($ignoreUserId === null && !in_array($data['user_type'] ?? '', self::USER_TYPES, true)) {
            $errors['user_type'] = 'Select a valid user type.';
        }

        if (!in_array($data['status'] ?? '', self::STATUSES, true)) {
            $errors['status'] = 'Select a valid status.';
        }

        $password = $data['password'] ?? '';
        $isNewUser = $ignoreUserId === null;

        if ($isNewUser || $password !== '') {
            if (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            } elseif ($password !== ($data['password_confirmation'] ?? '')) {
                $errors['password_confirmation'] = 'Passwords do not match.';
            }
        }

        return $errors;
    }
}
