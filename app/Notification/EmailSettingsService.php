<?php

declare(strict_types=1);

namespace App\Notification;

class EmailSettingsService
{
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
    public function update(array $data): array
    {
        $errors = $this->validate($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->settings->update([
            'sender_name' => trim($data['sender_name']),
            'sender_email' => trim($data['sender_email']),
            'cc' => $this->normalizeList($data['cc']),
            'bcc' => $this->normalizeList($data['bcc']),
            'notify_admin_on_completion' => $data['notify_admin_on_completion'],
            'notify_inductee_on_completion' => $data['notify_inductee_on_completion'],
            'notify_inductee_on_expiry' => $data['notify_inductee_on_expiry'],
            'expiry_reminder_days' => (int) $data['expiry_reminder_days'],
        ]);

        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];

        if (trim($data['sender_name'] ?? '') === '') {
            $errors['sender_name'] = 'Sender name is required.';
        }

        $senderEmail = trim($data['sender_email'] ?? '');
        if ($senderEmail !== '' && !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['sender_email'] = 'Enter a valid sender email address.';
        }

        foreach (['cc' => 'CC', 'bcc' => 'BCC'] as $field => $label) {
            foreach ($this->splitList($data[$field] ?? '') as $address) {
                if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Enter valid, comma-separated email addresses for {$label}.";
                    break;
                }
            }
        }

        $days = $data['expiry_reminder_days'] ?? '';
        if (!ctype_digit((string) $days) || (int) $days < 1) {
            $errors['expiry_reminder_days'] = 'Enter a valid number of days (1 or more).';
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
