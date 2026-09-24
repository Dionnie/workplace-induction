<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Database;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*,
                    COALESCE(ap.first_name, ip.first_name) AS first_name,
                    COALESCE(ap.last_name, ip.last_name) AS last_name
             FROM users u
             LEFT JOIN admin_profiles ap ON ap.user_id = u.id
             LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
             WHERE u.id = ?'
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users
                (email, password, user_type, status, email_verified_at, email_verification_token, email_verification_expires_at)
             VALUES
                (:email, :password, :user_type, :status, :email_verified_at, :email_verification_token, :email_verification_expires_at)'
        );

        $stmt->execute([
            'email' => $data['email'],
            'password' => $data['password'],
            'user_type' => $data['user_type'],
            'status' => $data['status'] ?? 'active',
            'email_verified_at' => $data['email_verified_at'] ?? null,
            'email_verification_token' => $data['email_verification_token'] ?? null,
            'email_verification_expires_at' => $data['email_verification_expires_at'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createInducteeProfile(
        int $userId,
        string $firstName,
        string $lastName,
        ?string $company = null,
        ?string $employmentType = null
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO inductee_profiles (user_id, first_name, last_name, company, employment_type)
             VALUES (:user_id, :first_name, :last_name, :company, :employment_type)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company' => $company,
            'employment_type' => $employmentType,
        ]);
    }

    public function createAdminProfile(int $userId, string $firstName, string $lastName): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO admin_profiles (user_id, first_name, last_name) VALUES (:user_id, :first_name, :last_name)'
        );
        $stmt->execute(['user_id' => $userId, 'first_name' => $firstName, 'last_name' => $lastName]);
    }

    public function updateProfile(int $userId, string $userType, string $firstName, string $lastName): void
    {
        $table = $userType === 'admin' ? 'admin_profiles' : 'inductee_profiles';
        $stmt = $this->db->prepare(
            "UPDATE {$table} SET first_name = :first_name, last_name = :last_name WHERE user_id = :user_id"
        );
        $stmt->execute(['first_name' => $firstName, 'last_name' => $lastName, 'user_id' => $userId]);
    }

    public function findByVerificationToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email_verification_token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function markEmailVerified(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET email_verified_at = NOW(), email_verification_token = NULL, email_verification_expires_at = NULL
             WHERE id = ?'
        );
        $stmt->execute([$id]);
    }

    public function setPasswordResetToken(int $id, string $token, string $expiresAt): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET password_reset_token = :token, password_reset_expires_at = :expires_at WHERE id = :id'
        );
        $stmt->execute(['token' => $token, 'expires_at' => $expiresAt, 'id' => $id]);
    }

    public function findByResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE password_reset_token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function updatePassword(int $id, string $hashedPassword): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET password = :password, password_reset_token = NULL, password_reset_expires_at = NULL
             WHERE id = :id'
        );
        $stmt->execute(['password' => $hashedPassword, 'id' => $id]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function updateEmail(int $id, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE users SET email = ? WHERE id = ?');
        $stmt->execute([$email, $id]);
    }

    /**
     * The user's admin/inductee profile row is removed automatically via its
     * ON DELETE CASCADE foreign key.
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    /**
     * Inductee accounts created in the period, for the admin report.
     *
     * @return array<int, array<string, mixed>>
     */
    public function inducteesRegisteredBetween(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.email, u.created_at, ip.first_name, ip.last_name, ip.company
             FROM users u
             JOIN inductee_profiles ip ON ip.user_id = u.id
             WHERE u.user_type = 'inductee'
               AND u.created_at >= :from AND u.created_at < :to
             ORDER BY u.created_at"
        );
        $stmt->execute(['from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    public function allWithProfiles(?string $userType = null, ?string $search = null): array
    {
        $sql = "SELECT u.id, u.email, u.user_type, u.status, u.email_verified_at, u.created_at,
                       COALESCE(ap.first_name, ip.first_name) AS first_name,
                       COALESCE(ap.last_name, ip.last_name) AS last_name
                FROM users u
                LEFT JOIN admin_profiles ap ON ap.user_id = u.id
                LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
                WHERE 1 = 1";
        $params = [];

        if ($userType !== null) {
            $sql .= ' AND u.user_type = :user_type';
            $params['user_type'] = $userType;
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND (u.email LIKE :search_1
                           OR ap.first_name LIKE :search_2 OR ap.last_name LIKE :search_3
                           OR ip.first_name LIKE :search_4 OR ip.last_name LIKE :search_5)';
            $like = '%' . $search . '%';
            $params['search_1'] = $like;
            $params['search_2'] = $like;
            $params['search_3'] = $like;
            $params['search_4'] = $like;
            $params['search_5'] = $like;
        }

        $sql .= ' ORDER BY u.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findWithProfile(int $id): ?array
    {
        return $this->findById($id);
    }
}
