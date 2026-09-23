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

    public function find(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ip.*, u.email
             FROM inductee_profiles ip
             JOIN users u ON u.id = ip.user_id
             WHERE ip.user_id = ?'
        );
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        return $profile ?: null;
    }

    public function update(int $userId, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE inductee_profiles
             SET first_name = :first_name, last_name = :last_name, job_position = :job_position,
                 company = :company, employment_type = :employment_type,
                 contact_number = :contact_number, emergency_contact_name = :emergency_contact_name,
                 emergency_contact_phone = :emergency_contact_phone
             WHERE user_id = :user_id'
        );

        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'job_position' => $data['job_position'] !== '' ? $data['job_position'] : null,
            'company' => $data['company'] !== '' ? $data['company'] : null,
            'employment_type' => $data['employment_type'] !== '' ? $data['employment_type'] : null,
            'contact_number' => $data['contact_number'] !== '' ? $data['contact_number'] : null,
            'emergency_contact_name' => $data['emergency_contact_name'] !== '' ? $data['emergency_contact_name'] : null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] !== '' ? $data['emergency_contact_phone'] : null,
            'user_id' => $userId,
        ]);
    }
}
