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
}
