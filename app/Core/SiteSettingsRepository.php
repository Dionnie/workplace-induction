<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class SiteSettingsRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function get(): array
    {
        $stmt = $this->db->query('SELECT * FROM site_settings WHERE id = 1');
        $settings = $stmt->fetch();
        return $settings ?: $this->defaults();
    }

    public function updateGeneral(array $data): void
    {
        $this->upsert([
            'company_name' => $data['company_name'],
            'logo_url' => $data['logo_url'] !== '' ? $data['logo_url'] : null,
            'primary_email' => $data['primary_email'] !== '' ? $data['primary_email'] : null,
        ]);
    }

    public function updateAppearance(array $data): void
    {
        $this->upsert([
            'theme_preset' => $data['theme_preset'],
            'theme_primary' => $data['theme_primary'] !== '' ? $data['theme_primary'] : null,
            'theme_accent' => $data['theme_accent'] !== '' ? $data['theme_accent'] : null,
        ]);
    }

    /**
     * Saves only the given columns; the row is created from defaults on
     * first save. Column names come from this class only, never from input.
     *
     * @param array<string, mixed> $values
     */
    private function upsert(array $values): void
    {
        $row = array_merge($this->defaults(), $values);
        $columns = array_keys($row);

        $sql = sprintf(
            'INSERT INTO site_settings (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            implode(', ', $columns),
            implode(', ', array_map(fn (string $column): string => ':' . $column, $columns)),
            implode(', ', array_map(fn (string $column): string => "{$column} = VALUES({$column})", array_keys($values)))
        );

        $this->db->prepare($sql)->execute($row);
    }

    private function defaults(): array
    {
        return [
            'id' => 1,
            'company_name' => app_config()['name'],
            'logo_url' => null,
            'primary_email' => app_config()['contact_email'],
            'theme_preset' => Theme::DEFAULT_PRESET,
            'theme_primary' => null,
            'theme_accent' => null,
        ];
    }
}
