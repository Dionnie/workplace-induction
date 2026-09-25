<?php

declare(strict_types=1);

namespace App\Notification;

use App\Compliance\ComplianceRepository;
use App\Core\Auth\UserRepository;
use App\Core\Database;
use App\Core\Mailer;
use DateTimeImmutable;

/**
 * Inductee emails are always sent immediately. Administrator emails are
 * sent either per event ('instant') or as one periodic report, so a busy
 * induction period doesn't flood the admin inbox.
 */
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
     * Instant mode only: tells administrators a new inductee registered.
     * Otherwise the registration is included in the next admin report.
     *
     * @param array<string, mixed> $inductee email (a new registration has no profile details yet)
     */
    public function notifyAdminsOfRegistration(array $inductee): void
    {
        $settings = $this->settings->get();
        if ($settings['admin_notification_frequency'] !== 'instant' || !$settings['notify_admin_on_registration']) {
            return;
        }

        $this->sendToAdmins(Mailer::renderTemplate('inductee-registered', ['inductee' => $inductee]), $settings);
    }

    /**
     * Instant mode only: notifies administrators and any other concerned
     * people (configured cc/bcc) that an inductee has completed an induction.
     * Otherwise the completion is included in the next admin report.
     *
     * @param array<string, mixed> $inductee
     * @param array<string, mixed> $induction
     * @param array<string, mixed> $complianceRecord
     */
    public function notifyInductionCompleted(array $inductee, array $induction, array $complianceRecord): void
    {
        $settings = $this->settings->get();
        if ($settings['admin_notification_frequency'] !== 'instant' || !$settings['notify_admin_on_completion']) {
            return;
        }

        $this->sendToAdmins(Mailer::renderTemplate('induction-completed', [
            'inductee' => $inductee,
            'induction' => $induction,
            'complianceRecord' => $complianceRecord,
        ]), $settings);
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

        $this->sendTo($inductee['email'], $rendered, $settings);
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

            if ($this->sendTo($record['email'], $rendered, $settings)) {
                $this->compliance->markReminderSent((int) $record['id']);
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Sends the admin report when a new period has started since the last
     * one. Safe to run repeatedly (e.g. daily from cron). A period with no
     * activity is marked as reported without sending an email.
     *
     * @return string 'sent', 'empty' (nothing to report), 'failed' or 'not_due'
     */
    public function sendAdminReportIfDue(?DateTimeImmutable $now = null): string
    {
        $settings = $this->settings->get();
        $periodStart = $this->currentPeriodStart($settings['admin_notification_frequency'], $now ?? Database::now());
        $lastSent = $settings['admin_report_last_sent_at'] ? new DateTimeImmutable($settings['admin_report_last_sent_at']) : null;

        if ($lastSent !== null && $lastSent >= $periodStart) {
            return 'not_due';
        }

        // Report whole periods only (e.g. last Monday to this Monday). The
        // first report covers just one period so historical data isn't swept in.
        $from = $lastSent ?? $this->previousPeriodStart($settings['admin_notification_frequency'], $periodStart);
        $report = $this->buildAdminReport($from, $periodStart, $settings);

        $hasActivity = array_filter($report['sections'], fn (?array $rows): bool => !empty($rows)) !== [];
        if ($hasActivity && !$this->sendToAdmins(Mailer::renderTemplate('admin-report', ['report' => $report]), $settings)) {
            // Leave the period unreported so the next run retries it.
            return 'failed';
        }

        $this->settings->markAdminReportSent($periodStart->format('Y-m-d H:i:s'));

        return $hasActivity ? 'sent' : 'empty';
    }

    /**
     * The day the next admin report goes out, assuming the daily cron runs.
     * In instant mode this is the next expired-compliance list.
     */
    public function nextReportDue(): DateTimeImmutable
    {
        $settings = $this->settings->get();
        $frequency = $settings['admin_notification_frequency'];
        $periodStart = $this->currentPeriodStart($frequency, Database::now());
        $lastSent = $settings['admin_report_last_sent_at'] ? new DateTimeImmutable($settings['admin_report_last_sent_at']) : null;

        if ($lastSent === null || $lastSent < $periodStart) {
            return $periodStart; // due now; goes out on the next run
        }

        return match ($frequency) {
            'weekly' => $periodStart->modify('+1 week'),
            'monthly' => $periodStart->modify('+1 month'),
            default => $periodStart->modify('+1 day'),
        };
    }

    /**
     * What the next admin report would contain if the period ended now.
     *
     * @return array{from: DateTimeImmutable, to: DateTimeImmutable, sections: array<string, ?array<int, array<string, mixed>>>}
     */
    public function previewAdminReport(): array
    {
        $settings = $this->settings->get();
        $now = Database::now();
        $from = $settings['admin_report_last_sent_at']
            ? new DateTimeImmutable($settings['admin_report_last_sent_at'])
            : $this->previousPeriodStart(
                $settings['admin_notification_frequency'],
                $this->currentPeriodStart($settings['admin_notification_frequency'], $now)
            );

        return $this->buildAdminReport($from, $now, $settings);
    }

    /**
     * Sections are null when switched off. In instant mode registrations and
     * completions were already emailed as they happened, so only expired
     * compliance (which has no single moment to notify on) is reported.
     *
     * @return array{from: DateTimeImmutable, to: DateTimeImmutable, sections: array<string, ?array<int, array<string, mixed>>>}
     */
    private function buildAdminReport(DateTimeImmutable $from, DateTimeImmutable $to, array $settings): array
    {
        $instant = $settings['admin_notification_frequency'] === 'instant';
        $fromAt = $from->format('Y-m-d H:i:s');
        $toAt = $to->format('Y-m-d H:i:s');

        $this->compliance->expireLapsed();

        return [
            'from' => $from,
            'to' => $to,
            'sections' => [
                'registrations' => !$instant && $settings['notify_admin_on_registration']
                    ? (new UserRepository())->inducteesRegisteredBetween($fromAt, $toAt)
                    : null,
                'completions' => !$instant && $settings['notify_admin_on_completion']
                    ? $this->compliance->completedBetween($fromAt, $toAt)
                    : null,
                'expired' => $settings['notify_admin_on_expired']
                    ? $this->compliance->expiredBetween($from->format('Y-m-d'), $to->format('Y-m-d'))
                    : null,
            ],
        ];
    }

    private function currentPeriodStart(string $frequency, DateTimeImmutable $now): DateTimeImmutable
    {
        return match ($frequency) {
            'weekly' => $now->modify('monday this week')->setTime(0, 0),
            'monthly' => $now->modify('first day of this month')->setTime(0, 0),
            default => $now->setTime(0, 0), // daily, and instant's daily expired list
        };
    }

    private function previousPeriodStart(string $frequency, DateTimeImmutable $periodStart): DateTimeImmutable
    {
        return match ($frequency) {
            'weekly' => $periodStart->modify('-1 week'),
            'monthly' => $periodStart->modify('-1 month'),
            default => $periodStart->modify('-1 day'),
        };
    }

    /**
     * Sends to an inductee using the inductee sender, CC and BCC.
     *
     * @param array{subject: string, body: string, html: string} $rendered
     */
    private function sendTo(string $to, array $rendered, array $settings): bool
    {
        return Mailer::send($to, $rendered['subject'], $rendered['body'],
            ['html' => $rendered['html']] + EmailSettingsService::senderOptions($settings, 'inductee'));
    }

    /**
     * Sends to the administrator To list (EmailSettingsService::adminTo())
     * using the administrator sender, CC and BCC.
     *
     * @param array{subject: string, body: string, html: string} $rendered
     */
    private function sendToAdmins(array $rendered, array $settings): bool
    {
        return Mailer::send(EmailSettingsService::adminTo($settings), $rendered['subject'], $rendered['body'],
            ['html' => $rendered['html']] + EmailSettingsService::senderOptions($settings, 'admin'));
    }
}
