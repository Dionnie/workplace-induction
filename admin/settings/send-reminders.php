<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Notification\NotificationService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$notifications = new NotificationService();
$sent = $notifications->sendExpiryReminders();
$report = $notifications->sendAdminReportIfDue();

$message = $sent === 1 ? 'Sent 1 expiry reminder.' : "Sent {$sent} expiry reminders.";
$message .= match ($report) {
    'sent' => ' Admin report sent.',
    'empty' => ' Admin report skipped: no activity this period.',
    'failed' => ' Admin report could not be sent; it will be retried next time.',
    default => ' Admin report is not due yet.',
};

flash('success', $message);
redirect('/admin/settings/index.php?tab=notifications');
