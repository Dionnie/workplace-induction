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
}
