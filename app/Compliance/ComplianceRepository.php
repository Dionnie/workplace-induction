<?php

declare(strict_types=1);

namespace App\Compliance;

use App\Core\Database;
use PDO;

class ComplianceRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Lazily transitions any lapsed "active" records to "expired". Cheap to run
     * on every relevant read since there is no background job runner.
     */
    public function expireLapsed(): void
    {
        $this->db->exec(
            "UPDATE compliance_records SET status = 'expired'
             WHERE status = 'active' AND expiry_date < CURDATE()"
        );
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO compliance_records
                (user_id, induction_id, exam_attempt_id, verification_token, certificate_number,
                 issue_date, expiry_date, status, renewed_from_id)
             VALUES
                (:user_id, :induction_id, :exam_attempt_id, :verification_token, :certificate_number,
                 :issue_date, :expiry_date, :status, :renewed_from_id)'
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'induction_id' => $data['induction_id'],
            'exam_attempt_id' => $data['exam_attempt_id'],
            'verification_token' => $data['verification_token'],
            'certificate_number' => $data['certificate_number'],
            'issue_date' => $data['issue_date'],
            'expiry_date' => $data['expiry_date'],
            'status' => $data['status'],
            'renewed_from_id' => $data['renewed_from_id'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateCertificateNumber(int $id, string $certificateNumber): void
    {
        $stmt = $this->db->prepare('UPDATE compliance_records SET certificate_number = ? WHERE id = ?');
        $stmt->execute([$certificateNumber, $id]);
    }

    public function markSuperseded(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE compliance_records SET status = 'superseded' WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function markRevoked(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE compliance_records SET status = 'revoked' WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM compliance_records WHERE id = ?');
        $stmt->execute([$id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public function findByVerificationToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT cr.*, i.title AS induction_title, i.code AS induction_code,
                    COALESCE(ap.first_name, ip.first_name) AS first_name,
                    COALESCE(ap.last_name, ip.last_name) AS last_name
             FROM compliance_records cr
             JOIN inductions i ON i.id = cr.induction_id
             JOIN users u ON u.id = cr.user_id
             LEFT JOIN admin_profiles ap ON ap.user_id = u.id
             LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
             WHERE cr.verification_token = ?'
        );
        $stmt->execute([$token]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * Latest record for this user+induction, of any status. Used to find what
     * to supersede/renew from.
     */
    public function latestForUserAndInduction(int $userId, int $inductionId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM compliance_records
             WHERE user_id = ? AND induction_id = ?
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$userId, $inductionId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT cr.*, i.title AS induction_title, i.code AS induction_code
             FROM compliance_records cr
             JOIN inductions i ON i.id = cr.induction_id
             WHERE cr.user_id = ?
             ORDER BY cr.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Latest record per induction for this user, of any status -- one row per
     * induction the user has ever attempted, used to drive the inductee
     * dashboard's status badge.
     *
     * @return array<int, array<string, mixed>> keyed by induction_id
     */
    public function latestPerInductionForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT cr.* FROM compliance_records cr
             INNER JOIN (
                 SELECT induction_id, MAX(created_at) AS max_created
                 FROM compliance_records
                 WHERE user_id = :user_id_1
                 GROUP BY induction_id
             ) latest ON latest.induction_id = cr.induction_id AND latest.max_created = cr.created_at
             WHERE cr.user_id = :user_id_2'
        );
        $stmt->execute(['user_id_1' => $userId, 'user_id_2' => $userId]);

        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[(int) $row['induction_id']] = $row;
        }

        return $rows;
    }

    /**
     * All compliance records across inductees, for admin management.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(?string $status = null, ?int $inductionId = null, ?string $search = null): array
    {
        $sql = 'SELECT cr.*, i.title AS induction_title, i.code AS induction_code, u.email,
                       COALESCE(ap.first_name, ip.first_name) AS first_name,
                       COALESCE(ap.last_name, ip.last_name) AS last_name
                FROM compliance_records cr
                JOIN inductions i ON i.id = cr.induction_id
                JOIN users u ON u.id = cr.user_id
                LEFT JOIN admin_profiles ap ON ap.user_id = u.id
                LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
                WHERE 1 = 1';
        $params = [];

        if ($status !== null) {
            $sql .= ' AND cr.status = :status';
            $params['status'] = $status;
        }

        if ($inductionId !== null) {
            $sql .= ' AND cr.induction_id = :induction_id';
            $params['induction_id'] = $inductionId;
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND (u.email LIKE :search_1
                       OR COALESCE(ap.first_name, ip.first_name) LIKE :search_2
                       OR COALESCE(ap.last_name, ip.last_name) LIKE :search_3
                       OR cr.certificate_number LIKE :search_4)';
            $like = '%' . $search . '%';
            $params['search_1'] = $like;
            $params['search_2'] = $like;
            $params['search_3'] = $like;
            $params['search_4'] = $like;
        }

        $sql .= ' ORDER BY cr.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Active records expiring within the given window that have not already
     * had an expiry reminder sent, for the expiry reminder notification.
     *
     * @return array<int, array<string, mixed>>
     */
    public function expiringWithoutReminder(int $days): array
    {
        $stmt = $this->db->prepare(
            "SELECT cr.*, i.title AS induction_title, u.email,
                    COALESCE(ap.first_name, ip.first_name) AS first_name,
                    COALESCE(ap.last_name, ip.last_name) AS last_name
             FROM compliance_records cr
             JOIN inductions i ON i.id = cr.induction_id
             JOIN users u ON u.id = cr.user_id
             LEFT JOIN admin_profiles ap ON ap.user_id = u.id
             LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
             WHERE cr.status = 'active'
               AND cr.expiry_reminder_sent_at IS NULL
               AND cr.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)"
        );
        $stmt->bindValue('days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markReminderSent(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE compliance_records SET expiry_reminder_sent_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function findOwnedByUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT cr.*, i.title AS induction_title, i.code AS induction_code
             FROM compliance_records cr
             JOIN inductions i ON i.id = cr.induction_id
             WHERE cr.id = ? AND cr.user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        $record = $stmt->fetch();
        return $record ?: null;
    }
}
