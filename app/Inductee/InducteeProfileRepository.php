<?php

declare(strict_types=1);

namespace App\Inductee;

use App\Core\Database;
use PDO;

class InducteeProfileRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * The inductee's profile with their email and profile_completed flag.
     * An account without a profile row yet (new registrations, accounts
     * created by an administrator) returns its email with empty fields.
     */
    public function find(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.email, u.profile_completed,
                    ip.first_name, ip.last_name, ip.job_position, ip.company, ip.employment_type,
                    ip.contact_number
             FROM users u
             LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
             WHERE u.id = ?'
        );
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        return $profile ?: null;
    }

    /**
     * Saves the profile, creating the row on the inductee's first save.
     */
    public function save(int $userId, array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO inductee_profiles
                (user_id, first_name, last_name, job_position, company, employment_type, contact_number)
             VALUES
                (:user_id, :first_name, :last_name, :job_position, :company, :employment_type, :contact_number)
             ON DUPLICATE KEY UPDATE
                first_name = VALUES(first_name), last_name = VALUES(last_name),
                job_position = VALUES(job_position), company = VALUES(company),
                employment_type = VALUES(employment_type), contact_number = VALUES(contact_number)'
        );

        $stmt->execute([
            'user_id' => $userId,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'job_position' => $data['job_position'] !== '' ? $data['job_position'] : null,
            'company' => $data['company'] !== '' ? $data['company'] : null,
            'employment_type' => $data['employment_type'] !== '' ? $data['employment_type'] : null,
            'contact_number' => $data['contact_number'] !== '' ? $data['contact_number'] : null,
        ]);
    }

    /**
     * Inductees with their profile columns and account information,
     * filtered by search query, employment type, status, and profile completion.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allWithUsers(
        ?string $search = null,
        ?string $employmentType = null,
        ?string $status = null,
        ?string $profileStatus = null,
        ?string $sort = null,
        ?string $dir = null
    ): array {
        $sql = "SELECT u.id, u.email, u.status, u.profile_completed, u.email_verified_at,
                       u.created_at AS user_created_at, u.updated_at AS user_updated_at,
                       ip.user_id, ip.first_name, ip.last_name, ip.contact_number,
                       ip.job_position, ip.company, ip.employment_type,
                       ip.created_at AS profile_created_at, ip.updated_at AS profile_updated_at,
                       ip.created_at, ip.updated_at
                FROM users u
                LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
                WHERE u.user_type = 'inductee'";
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' AND (u.email LIKE :s1
                           OR ip.first_name LIKE :s2
                           OR ip.last_name LIKE :s3
                           OR CONCAT(COALESCE(ip.first_name, ""), " ", COALESCE(ip.last_name, "")) LIKE :s4
                           OR ip.company LIKE :s5
                           OR ip.job_position LIKE :s6
                           OR ip.contact_number LIKE :s7)';
            $like = '%' . $search . '%';
            $params['s1'] = $like;
            $params['s2'] = $like;
            $params['s3'] = $like;
            $params['s4'] = $like;
            $params['s5'] = $like;
            $params['s6'] = $like;
            $params['s7'] = $like;
        }

        if ($employmentType !== null && $employmentType !== '') {
            $sql .= ' AND ip.employment_type = :employment_type';
            $params['employment_type'] = $employmentType;
        }

        if ($status !== null && $status !== '') {
            $sql .= ' AND u.status = :status';
            $params['status'] = $status;
        }

        if ($profileStatus !== null && $profileStatus !== '') {
            if ($profileStatus === 'complete' || $profileStatus === '1') {
                $sql .= ' AND u.profile_completed = 1';
            } elseif ($profileStatus === 'incomplete' || $profileStatus === '0') {
                $sql .= ' AND u.profile_completed = 0';
            }
        }

        $sortColumnMap = [
            'name' => "CONCAT(COALESCE(ip.first_name, ''), ' ', COALESCE(ip.last_name, ''))",
            'email' => 'u.email',
            'contact_number' => 'ip.contact_number',
            'job_position' => 'ip.job_position',
            'company' => 'ip.company',
            'employment_type' => 'ip.employment_type',
            'status' => 'u.status',
            'profile_completed' => 'u.profile_completed',
            'created_at' => 'COALESCE(ip.created_at, u.created_at)',
        ];

        $sortKey = strtolower($sort ?? '');
        $orderExpr = $sortColumnMap[$sortKey] ?? 'COALESCE(ip.created_at, u.created_at)';
        $direction = strtolower($dir ?? '') === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY {$orderExpr} {$direction}, u.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * One inductee with their profile columns and account information.
     */
    public function findWithUser(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.email, u.user_type, u.status, u.profile_completed, u.email_verified_at,
                    u.created_at AS user_created_at, u.updated_at AS user_updated_at,
                    ip.user_id, ip.first_name, ip.last_name, ip.contact_number,
                    ip.job_position, ip.company, ip.employment_type,
                    ip.created_at AS profile_created_at, ip.updated_at AS profile_updated_at,
                    ip.created_at, ip.updated_at
             FROM users u
             LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
             WHERE u.id = ? AND u.user_type = 'inductee'"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Distinct company names across inductee profiles with inductee counts,
     * ordered by usage frequency (most used first), then alphabetically.
     *
     * @return array<int, array{company: string, count: int}>
     */
    public function distinctCompaniesWithCounts(): array
    {
        $stmt = $this->db->query(
            "SELECT company, COUNT(*) AS count
             FROM inductee_profiles
             WHERE company IS NOT NULL AND TRIM(company) != ''
             GROUP BY company
             ORDER BY count DESC, company ASC"
        );

        return array_map(static fn(array $row): array => [
            'company' => (string) $row['company'],
            'count' => (int) $row['count'],
        ], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }
}

