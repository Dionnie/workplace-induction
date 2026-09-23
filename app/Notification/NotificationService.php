<?php

declare(strict_types=1);

namespace App\Notification;

use App\Admin\Services\UserManagementService;
use App\Compliance\ComplianceRepository;
use App\Core\Mailer;

class NotificationService
{
    private EmailSettingsService $settings;
    private ComplianceRepository $compliance;

    public function __construct()
    {
        $this->settings = new EmailSettingsService();
        $this->compliance = new ComplianceRepository();
    }

    /**
     * Notifies administrators and any other concerned people (configured
     * cc/bcc) that an inductee has completed an induction.
     *
     * @param array<string, mixed> $inductee
     * @param array<string, mixed> $induction
     * @param array<string, mixed> $complianceRecord
     */
    public function notifyInductionCompleted(array $inductee, array $induction, array $complianceRecord): void
    {
        $settings = $this->settings->get();
        if (!$settings['notify_admin_on_completion']) {
            return;
        }

        $adminEmails = array_column((new UserManagementService())->list('admin'), 'email');
        $to = implode(', ', $adminEmails);

        if ($to === '' && (string) $settings['cc'] === '' && (string) $settings['bcc'] === '') {
            return;
        }

        $rendered = Mailer::renderTemplate('induction-completed', [
            'inductee' => $inductee,
            'induction' => $induction,
            'complianceRecord' => $complianceRecord,
        ]);

        // mail() requires a "to" address; if there are no admin accounts,
        // fall back to the configured cc list so the notification still goes out.
        Mailer::send(
            $to !== '' ? $to : $this->firstAddress((string) $settings['cc']),
            $rendered['subject'],
            $rendered['body'],
            [
                'from_name' => $settings['sender_name'],
                'from_email' => $settings['sender_email'],
                'cc' => $to !== '' ? $settings['cc'] : null,
                'bcc' => $settings['bcc'],
            ]
        );
    }

    /**
     * Notifies the inductee themselves that they've completed an induction,
     * with a link to their certificate.
     *
     * @param array<string, mixed> $inductee
     * @param array<string, mixed> $induction
     * @param array<string, mixed> $complianceRecord
     */
    public function notifyInducteeOfCompletion(array $inductee, array $induction, array $complianceRecord): void
    {
        $settings = $this->settings->get();
        if (!$settings['notify_inductee_on_completion']) {
            return;
        }

        $rendered = Mailer::renderTemplate('induction-completed-inductee', [
            'inductee' => $inductee,
            'induction' => $induction,
            'complianceRecord' => $complianceRecord,
            'certificateUrl' => public_url('/certificate.php?token=' . $complianceRecord['verification_token']),
        ]);

        Mailer::send($inductee['email'], $rendered['subject'], $rendered['body'], [
            'from_name' => $settings['sender_name'],
            'from_email' => $settings['sender_email'],
            'cc' => $settings['cc'],
            'bcc' => $settings['bcc'],
        ]);
    }

    /**
     * Sends expiry reminders to inductees whose active compliance is
     * expiring soon. Safe to run repeatedly -- each record is only
     * reminded once, tracked via expiry_reminder_sent_at.
     */
    public function sendExpiryReminders(): int
    {
        $settings = $this->settings->get();
        if (!$settings['notify_inductee_on_expiry']) {
            return 0;
        }

        $records = $this->compliance->expiringWithoutReminder((int) $settings['expiry_reminder_days']);
        $sent = 0;

        foreach ($records as $record) {
            $rendered = Mailer::renderTemplate('compliance-expiring', [
                'inductee' => $record,
                'induction' => ['title' => $record['induction_title']],
                'complianceRecord' => $record,
            ]);

            $delivered = Mailer::send($record['email'], $rendered['subject'], $rendered['body'], [
                'from_name' => $settings['sender_name'],
                'from_email' => $settings['sender_email'],
                'cc' => $settings['cc'],
                'bcc' => $settings['bcc'],
            ]);

            if ($delivered) {
                $this->compliance->markReminderSent((int) $record['id']);
                $sent++;
            }
        }

        return $sent;
    }

    private function firstAddress(string $list): string
    {
        foreach (explode(',', $list) as $address) {
            $address = trim($address);
            if ($address !== '') {
                return $address;
            }
        }

        return '';
    }
}
