<?php

declare(strict_types=1);

namespace App\Notification;

use App\Core\Database;
use PDO;

class EmailSettingsRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function get(): array
    {
        $stmt = $this->db->query('SELECT * FROM email_settings WHERE id = 1');
        $settings = $stmt->fetch();
        return $settings ?: $this->defaults();
    }

    /**
     * @param array<string, string> $data Sender fields for both audiences (see EmailSettingsService::AUDIENCES).
     */
    public function updateSenders(array $data): void
    {
        $values = [];
        foreach (EmailSettingsService::AUDIENCES as $audience => $label) {
            foreach (['sender_name', 'sender_email', 'cc', 'bcc'] as $field) {
                $column = "{$audience}_{$field}";
                $values[$column] = $data[$column] !== '' ? $data[$column] : null;
            }
        }

        $this->upsert($values);
    }

    public function updateNotifications(array $data): void
    {
        $this->upsert([
            'notify_inductee_on_completion' => $data['notify_inductee_on_completion'] ? 1 : 0,
            'notify_inductee_on_expiry' => $data['notify_inductee_on_expiry'] ? 1 : 0,
            'expiry_reminder_days' => $data['expiry_reminder_days'],
            'admin_notification_frequency' => $data['admin_notification_frequency'],
            'notify_admin_on_registration' => $data['notify_admin_on_registration'] ? 1 : 0,
            'notify_admin_on_completion' => $data['notify_admin_on_completion'] ? 1 : 0,
            'notify_admin_on_expired' => $data['notify_admin_on_expired'] ? 1 : 0,
        ]);
    }

    public function markAdminReportSent(string $sentAt): void
    {
        $this->upsert(['admin_report_last_sent_at' => $sentAt]);
    }

    /**
     * Saves only the given columns. On first save the row is created from
     * defaults so the other columns get sensible values. Column names come
     * from this class only, never from input.
     *
     * @param array<string, mixed> $values
     */
    private function upsert(array $values): void
    {
        $row = array_merge($this->defaults(), $values);
        $columns = array_keys($row);

        $sql = sprintf(
            'INSERT INTO email_settings (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
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
            'inductee_sender_name' => null,
            'inductee_sender_email' => null,
            'inductee_cc' => null,
            'inductee_bcc' => null,
            'admin_sender_name' => null,
            'admin_sender_email' => null,
            'admin_cc' => null,
            'admin_bcc' => null,
            'admin_notification_frequency' => 'weekly',
            'notify_admin_on_registration' => 1,
            'notify_admin_on_completion' => 1,
            'notify_inductee_on_completion' => 1,
            'notify_inductee_on_expiry' => 1,
            'notify_admin_on_expired' => 1,
            'expiry_reminder_days' => 30,
            'admin_report_last_sent_at' => null,
        ];
    }
}
