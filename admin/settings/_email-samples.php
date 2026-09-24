<?php

declare(strict_types=1);

use App\Notification\NotificationService;

/**
 * Sample data for each email template, shared by the preview and the test
 * email. The admin report uses real activity since the last report.
 *
 * @return array<string, callable(): array<string, mixed>>
 */
return (function (): array {
    $inductee = [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane.smith@example.com',
        'company' => 'Example Contracting',
    ];
    $induction = ['title' => 'Site Safety Induction'];
    $complianceRecord = [
        'certificate_number' => 'CERT-000123',
        'issue_date' => date('Y-m-d'),
        'expiry_date' => date('Y-m-d', strtotime('+1 year')),
    ];

    return [
        'induction-completed-inductee' => fn (): array => [
            'inductee' => $inductee,
            'induction' => $induction,
            'complianceRecord' => $complianceRecord,
            'certificateUrl' => public_url('/certificate.php?token=sample'),
        ],
        'compliance-expiring' => fn (): array => [
            'inductee' => $inductee,
            'induction' => $induction,
            'complianceRecord' => ['expiry_date' => date('Y-m-d', strtotime('+30 days'))] + $complianceRecord,
        ],
        'inductee-registered' => fn (): array => ['inductee' => $inductee],
        'induction-completed' => fn (): array => [
            'inductee' => $inductee,
            'induction' => $induction,
            'complianceRecord' => $complianceRecord,
        ],
        'admin-report' => fn (): array => ['report' => (new NotificationService())->previewAdminReport()],
        'verify-email' => fn (): array => ['verificationUrl' => public_url('/verify-email.php?token=sample')],
        'password-reset' => fn (): array => ['resetUrl' => public_url('/reset-password.php?token=sample')],
    ];
})();
