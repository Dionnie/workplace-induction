<?php

declare(strict_types=1);

namespace App\Induction;

use App\Core\Database;
use PDO;

class InductionRepository
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
        $sql = 'SELECT * FROM inductions WHERE 1 = 1';
        $params = [];

        if ($status !== null) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND (title LIKE :search_1 OR code LIKE :search_2)';
            $like = '%' . $search . '%';
            $params['search_1'] = $like;
            $params['search_2'] = $like;
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM inductions WHERE id = ?');
        $stmt->execute([$id]);
        $induction = $stmt->fetch();
        return $induction ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM inductions WHERE code = ?');
        $stmt->execute([$code]);
        $induction = $stmt->fetch();
        return $induction ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO inductions (title, code, description, exam_id, validity_months, content_blocks, status)
             VALUES (:title, :code, :description, :exam_id, :validity_months, :content_blocks, :status)'
        );

        $stmt->execute([
            'title' => $data['title'],
            'code' => $data['code'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'exam_id' => $data['exam_id'],
            'validity_months' => $data['validity_months'],
            'content_blocks' => $data['content_blocks'] ?? '[]',
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Updates induction metadata only. Content blocks have their own save
     * path (updateContentBlocks()) via the Studio editor, so this never
     * touches that column.
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE inductions
             SET title = :title, code = :code, description = :description, exam_id = :exam_id,
                 validity_months = :validity_months, status = :status
             WHERE id = :id'
        );

        $stmt->execute([
            'title' => $data['title'],
            'code' => $data['code'],
            'description' => $data['description'] !== '' ? $data['description'] : null,
            'exam_id' => $data['exam_id'],
            'validity_months' => $data['validity_months'],
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public function updateContentBlocks(int $id, string $json): void
    {
        $stmt = $this->db->prepare('UPDATE inductions SET content_blocks = :content_blocks WHERE id = :id');
        $stmt->execute(['content_blocks' => $json, 'id' => $id]);
    }
}
