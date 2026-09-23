<?php

declare(strict_types=1);

namespace App\Exam;

use App\Core\Database;
use PDO;

class ExamAttemptRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO exam_attempts (user_id, induction_id, exam_id, score, total_score, result)
             VALUES (:user_id, :induction_id, :exam_id, :score, :total_score, :result)'
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'induction_id' => $data['induction_id'],
            'exam_id' => $data['exam_id'],
            'score' => $data['score'],
            'total_score' => $data['total_score'],
            'result' => $data['result'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allForUserAndInduction(int $userId, int $inductionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM exam_attempts WHERE user_id = ? AND induction_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId, $inductionId]);
        return $stmt->fetchAll();
    }

    public function latestForUserAndInduction(int $userId, int $inductionId): ?array
    {
        $attempts = $this->allForUserAndInduction($userId, $inductionId);
        return $attempts[0] ?? null;
    }

    public function countForUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM exam_attempts WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function countForInduction(int $inductionId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM exam_attempts WHERE induction_id = ?');
        $stmt->execute([$inductionId]);
        return (int) $stmt->fetchColumn();
    }

    public function countForExam(int $examId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM exam_attempts WHERE exam_id = ?');
        $stmt->execute([$examId]);
        return (int) $stmt->fetchColumn();
    }

    public function deleteForUser(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM exam_attempts WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    public function deleteForInduction(int $inductionId): void
    {
        $stmt = $this->db->prepare('DELETE FROM exam_attempts WHERE induction_id = ?');
        $stmt->execute([$inductionId]);
    }

    public function deleteForExam(int $examId): void
    {
        $stmt = $this->db->prepare('DELETE FROM exam_attempts WHERE exam_id = ?');
        $stmt->execute([$examId]);
    }

    /**
     * All exam attempts across inductees, for admin management.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(?int $inductionId = null, ?int $examId = null, ?string $result = null, ?string $search = null): array
    {
        $sql = 'SELECT ea.*, i.title AS induction_title, e.title AS exam_title, u.email,
                       COALESCE(ap.first_name, ip.first_name) AS first_name,
                       COALESCE(ap.last_name, ip.last_name) AS last_name
                FROM exam_attempts ea
                JOIN inductions i ON i.id = ea.induction_id
                JOIN exams e ON e.id = ea.exam_id
                JOIN users u ON u.id = ea.user_id
                LEFT JOIN admin_profiles ap ON ap.user_id = u.id
                LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
                WHERE 1 = 1';
        $params = [];

        if ($inductionId !== null) {
            $sql .= ' AND ea.induction_id = :induction_id';
            $params['induction_id'] = $inductionId;
        }

        if ($examId !== null) {
            $sql .= ' AND ea.exam_id = :exam_id';
            $params['exam_id'] = $examId;
        }

        if ($result !== null) {
            $sql .= ' AND ea.result = :result';
            $params['result'] = $result;
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND (u.email LIKE :search_1
                       OR COALESCE(ap.first_name, ip.first_name) LIKE :search_2
                       OR COALESCE(ap.last_name, ip.last_name) LIKE :search_3)';
            $like = '%' . $search . '%';
            $params['search_1'] = $like;
            $params['search_2'] = $like;
            $params['search_3'] = $like;
        }

        $sql .= ' ORDER BY ea.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ea.*, i.title AS induction_title, e.title AS exam_title, u.email,
                    COALESCE(ap.first_name, ip.first_name) AS first_name,
                    COALESCE(ap.last_name, ip.last_name) AS last_name
             FROM exam_attempts ea
             JOIN inductions i ON i.id = ea.induction_id
             JOIN exams e ON e.id = ea.exam_id
             JOIN users u ON u.id = ea.user_id
             LEFT JOIN admin_profiles ap ON ap.user_id = u.id
             LEFT JOIN inductee_profiles ip ON ip.user_id = u.id
             WHERE ea.id = ?'
        );
        $stmt->execute([$id]);
        $attempt = $stmt->fetch();
        return $attempt ?: null;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM exam_attempts WHERE id = ?');
        $stmt->execute([$id]);
    }
}
