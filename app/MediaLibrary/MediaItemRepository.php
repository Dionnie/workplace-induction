<?php

declare(strict_types=1);

namespace App\MediaLibrary;

use App\Core\Database;
use PDO;

class MediaItemRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(?int $categoryId = null, ?string $search = null): array
    {
        $sql = 'SELECT * FROM media_items WHERE 1 = 1';
        $params = [];

        if ($categoryId !== null) {
            $sql .= ' AND category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND original_filename LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM media_items WHERE id = ?');
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO media_items (category_id, filename, original_filename, mime_type, size)
             VALUES (:category_id, :filename, :original_filename, :mime_type, :size)'
        );

        $stmt->execute([
            'category_id' => $data['category_id'],
            'filename' => $data['filename'],
            'original_filename' => $data['original_filename'],
            'mime_type' => $data['mime_type'],
            'size' => $data['size'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM media_items WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * @param array<int, int> $ids
     */
    public function updateCategoryForIds(array $ids, ?int $categoryId): int
    {
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("UPDATE media_items SET category_id = ? WHERE id IN ({$placeholders})");
        $stmt->execute([$categoryId, ...$ids]);

        return $stmt->rowCount();
    }
}
