<?php

declare(strict_types=1);

namespace App\MediaLibrary;

use App\Core\Database;
use PDO;

class MediaCategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM media_categories ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM media_categories WHERE id = ?');
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM media_categories WHERE name = ?');
        $stmt->execute([$name]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    public function create(string $name): int
    {
        $stmt = $this->db->prepare('INSERT INTO media_categories (name) VALUES (?)');
        $stmt->execute([$name]);
        return (int) $this->db->lastInsertId();
    }

    public function rename(int $id, string $name): void
    {
        $stmt = $this->db->prepare('UPDATE media_categories SET name = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM media_categories WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function countItems(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM media_items WHERE category_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
