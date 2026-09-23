<?php

declare(strict_types=1);

namespace App\MediaLibrary;

use App\Core\Database;
use PDO;

class MediaSettingsRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function get(): array
    {
        $stmt = $this->db->query('SELECT * FROM media_settings WHERE id = 1');
        $settings = $stmt->fetch();
        return $settings ?: $this->defaults();
    }

    public function update(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO media_settings (id, max_file_size_mb, allowed_types)
             VALUES (1, :max_file_size_mb, :allowed_types)
             ON DUPLICATE KEY UPDATE
                max_file_size_mb = VALUES(max_file_size_mb),
                allowed_types = VALUES(allowed_types)'
        );

        $stmt->execute([
            'max_file_size_mb' => $data['max_file_size_mb'],
            'allowed_types' => $data['allowed_types'],
        ]);
    }

    private function defaults(): array
    {
        return [
            'id' => 1,
            'max_file_size_mb' => 5,
            'allowed_types' => 'jpg,jpeg,png,gif,webp',
        ];
    }
}
