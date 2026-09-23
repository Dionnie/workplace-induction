<?php

declare(strict_types=1);

namespace App\Exam;

use App\Core\Database;
use PDO;

class ExamRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(?string $status = null, ?string $search = null): array
    {
        $sql = 'SELECT * FROM exams WHERE 1 = 1';
        $params = [];

        if ($status !== null) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND title LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM exams WHERE id = ?');
        $stmt->execute([$id]);
        $exam = $stmt->fetch();
        return $exam ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO exams (title, description, status, pass_percentage, exam_blocks)
             VALUES (:title, :description, :status, :pass_percentage, :exam_blocks)'
        );

        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'status' => $data['status'],
            'pass_percentage' => $data['pass_percentage'],
            'exam_blocks' => $data['exam_blocks'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE exams
             SET title = :title, description = :description, status = :status,
                 pass_percentage = :pass_percentage, exam_blocks = :exam_blocks
             WHERE id = :id'
        );

        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'status' => $data['status'],
            'pass_percentage' => $data['pass_percentage'],
            'exam_blocks' => $data['exam_blocks'],
            'id' => $id,
        ]);
    }
}
