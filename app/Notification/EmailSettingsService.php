<?php

declare(strict_types=1);

namespace App\Notification;

class EmailSettingsService
{
    /** Who an email is sent to; each has its own sender, CC and BCC. */
    public const AUDIENCES = [
        'inductee' => 'Inductee',
        'admin' => 'Administrator',
    ];

    /**
     * The saved fields of each audience. Inductee emails go to the inductee
     * themselves, so only administrator emails have a To list.
     */
    public const FIELDS = [
        'inductee' => ['sender_name', 'sender_email', 'cc', 'bcc'],
        'admin' => ['sender_name', 'sender_email', 'to', 'cc', 'bcc'],
    ];

    public const ADMIN_FREQUENCIES = [
        'instant' => 'Instant',
        'daily' => 'Daily report',
        'weekly' => 'Weekly report',
        'monthly' => 'Monthly report',
    ];

    private EmailSettingsRepository $settings;

    public function __construct()
    {
        $this->settings = new EmailSettingsRepository();
    }

    public function get(): array
    {
        return $this->settings->get();
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function updateSenders(array $data): array
    {
        $errors = $this->validateSenders($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $values = [];
        foreach (array_keys(self::AUDIENCES) as $audience) {
            $values["{$audience}_sender_name"] = trim($data["{$audience}_sender_name"] ?? '');
            $values["{$audience}_sender_email"] = trim($data["{$audience}_sender_email"] ?? '');
            $values["{$audience}_cc"] = $this->normalizeList($data["{$audience}_cc"] ?? '');
            $values["{$audience}_bcc"] = $this->normalizeList($data["{$audience}_bcc"] ?? '');
        }
        $values['admin_to'] = $this->normalizeList($data['admin_to'] ?? '');

        $this->settings->updateSenders($values);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Mailer options for an email to the given audience. Blank sender
     * fields are left to Mailer, which falls back to the company name and
     * primary email.
     *
     * @param array<string, mixed> $settings
     * @return array{from_name: ?string, from_email: ?string, cc: ?string, bcc: ?string}
     */
    public static function senderOptions(array $settings, string $audience): array
    {
        return [
            'from_name' => $settings["{$audience}_sender_name"] ?? null,
            'from_email' => $settings["{$audience}_sender_email"] ?? null,
            'cc' => $settings["{$audience}_cc"] ?? null,
            'bcc' => $settings["{$audience}_bcc"] ?? null,
        ];
    }

    /**
     * Who administrator emails go to: the saved To list, or when it is
     * blank, default_email(). Never derived from user accounts, so only the
     * people chosen in Settings > Email get them (docs/core/settings.md §3).
     *
     * @param array<string, mixed> $settings
     */
    public static function adminTo(array $settings): string
    {
        return ($settings['admin_to'] ?? '') ?: default_email();
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function updateNotifications(array $data): array
    {
        $errors = $this->validateNotifications($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->settings->updateNotifications([
            'notify_inductee_on_completion' => $data['notify_inductee_on_completion'],
            'notify_inductee_on_expiry' => $data['notify_inductee_on_expiry'],
            'expiry_reminder_days' => (int) $data['expiry_reminder_days'],
            'admin_notification_frequency' => $data['admin_notification_frequency'],
            'notify_admin_on_registration' => $data['notify_admin_on_registration'],
            'notify_admin_on_completion' => $data['notify_admin_on_completion'],
            'notify_admin_on_expired' => $data['notify_admin_on_expired'],
        ]);

        return ['success' => true, 'errors' => []];
    }

    public function markAdminReportSent(string $sentAt): void
    {
        $this->settings->markAdminReportSent($sentAt);
    }

    /**
     * @return array<string, string>
     */
    private function validateSenders(array $data): array
    {
        $errors = [];

        foreach (array_keys(self::AUDIENCES) as $audience) {
            if (mb_strlen(trim($data["{$audience}_sender_name"] ?? '')) > 150) {
                $errors["{$audience}_sender_name"] = 'From Name must be 150 characters or fewer.';
            }

            $senderEmail = trim($data["{$audience}_sender_email"] ?? '');
            if ($senderEmail !== '' && !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
                $errors["{$audience}_sender_email"] = 'Enter a valid From Email address.';
            }

            foreach (['to' => 'To', 'cc' => 'CC', 'bcc' => 'BCC'] as $field => $label) {
                if (!in_array($field, self::FIELDS[$audience], true)) {
                    continue;
                }
                $list = $data["{$audience}_{$field}"] ?? '';
                foreach ($this->splitList($list) as $address) {
                    if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                        $errors["{$audience}_{$field}"] = "Enter valid, comma-separated email addresses for {$label}.";
                        break;
                    }
                }
                if (!isset($errors["{$audience}_{$field}"]) && strlen($this->normalizeList($list)) > 500) {
                    $errors["{$audience}_{$field}"] = "{$label} is too long (500 characters maximum).";
                }
            }
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    private function validateNotifications(array $data): array
    {
        $errors = [];

        $days = $data['expiry_reminder_days'] ?? '';
        if (!ctype_digit((string) $days) || (int) $days < 1 || (int) $days > 365) {
            $errors['expiry_reminder_days'] = 'Enter a number of days between 1 and 365.';
        }

        if (!array_key_exists($data['admin_notification_frequency'] ?? '', self::ADMIN_FREQUENCIES)) {
            $errors['admin_notification_frequency'] = 'Choose how often administrators are notified.';
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    private function splitList(string $value): array
    {
        $addresses = array_map('trim', explode(',', $value));
        return array_values(array_filter($addresses, fn (string $address): bool => $address !== ''));
    }

    private function normalizeList(string $value): string
    {
        return implode(', ', $this->splitList($value));
    }
}
