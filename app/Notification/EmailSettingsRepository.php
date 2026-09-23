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

    public function update(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO email_settings
                (id, sender_name, sender_email, cc, bcc, notify_admin_on_completion, notify_inductee_on_completion,
                 notify_inductee_on_expiry, expiry_reminder_days)
             VALUES
                (1, :sender_name, :sender_email, :cc, :bcc, :notify_admin_on_completion, :notify_inductee_on_completion,
                 :notify_inductee_on_expiry, :expiry_reminder_days)
             ON DUPLICATE KEY UPDATE
                sender_name = VALUES(sender_name),
                sender_email = VALUES(sender_email),
                cc = VALUES(cc),
                bcc = VALUES(bcc),
                notify_admin_on_completion = VALUES(notify_admin_on_completion),
                notify_inductee_on_completion = VALUES(notify_inductee_on_completion),
                notify_inductee_on_expiry = VALUES(notify_inductee_on_expiry),
                expiry_reminder_days = VALUES(expiry_reminder_days)'
        );

        $stmt->execute([
            'sender_name' => $data['sender_name'],
            'sender_email' => $data['sender_email'] !== '' ? $data['sender_email'] : null,
            'cc' => $data['cc'] !== '' ? $data['cc'] : null,
            'bcc' => $data['bcc'] !== '' ? $data['bcc'] : null,
            'notify_admin_on_completion' => $data['notify_admin_on_completion'] ? 1 : 0,
            'notify_inductee_on_completion' => $data['notify_inductee_on_completion'] ? 1 : 0,
            'notify_inductee_on_expiry' => $data['notify_inductee_on_expiry'] ? 1 : 0,
            'expiry_reminder_days' => $data['expiry_reminder_days'],
        ]);
    }

    private function defaults(): array
    {
        return [
            'id' => 1,
            'sender_name' => app_config()['name'],
            'sender_email' => app_config()['contact_email'],
            'cc' => null,
            'bcc' => null,
            'notify_admin_on_completion' => 1,
            'notify_inductee_on_completion' => 1,
            'notify_inductee_on_expiry' => 1,
            'expiry_reminder_days' => 30,
        ];
    }
}
