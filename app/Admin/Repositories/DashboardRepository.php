<?php

declare(strict_types=1);

namespace App\Admin\Repositories;

use App\Core\Database;
use PDO;

class DashboardRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function countActiveInductees(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE user_type = 'inductee' AND status = 'active'");
        return (int) $stmt->fetchColumn();
    }

    public function countActiveInductions(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM inductions WHERE status = 'active'");
        return (int) $stmt->fetchColumn();
    }

    public function countComplianceByStatus(string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM compliance_records WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public function countExpiringSoon(int $days): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM compliance_records
             WHERE status = 'active' AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<int, array{active: int, expired: int}> keyed by induction_id
     */
    public function perInductionComplianceCounts(): array
    {
        $stmt = $this->db->query(
            "SELECT induction_id, status, COUNT(*) AS total
             FROM compliance_records
             WHERE status IN ('active', 'expired')
             GROUP BY induction_id, status"
        );

        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $inductionId = (int) $row['induction_id'];
            if (!isset($counts[$inductionId])) {
                $counts[$inductionId] = ['active' => 0, 'expired' => 0];
            }
            $counts[$inductionId][$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Soonest-expiring active compliance records, for the admin's action list.
     *
     * @return array<int, array<string, mixed>>
     */
    public function expiringSoon(int $days, int $limit): array
    {
        $stmt = $this->db->prepare(
            "SELECT cr.expiry_date, i.title AS induction_title,
                    COALESCE(ap.first_name, ip.first_name) AS first_name,
                    COALESCE(ap.last_name, ip.last_name) AS last_name,
                    u.email
             FROM compliance_records cr
             JOIN inductions i ON i.id = cr.induction_id
             JOIN users u ON u.id = cr.user_id
             LEFT JOIN admin_profiles ap ON ap.user_id = u.id
             LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
             WHERE cr.status = 'active' AND cr.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY cr.expiry_date ASC
             LIMIT :limit"
        );
        $stmt->bindValue('days', $days, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
