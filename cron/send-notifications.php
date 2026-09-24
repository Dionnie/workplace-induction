<?php

declare(strict_types=1);

// Intended to be run daily via the server's task scheduler, e.g.:
//   php cron/send-notifications.php
// Sends inductee expiry reminders, and the admin report when a new period
// (day/week/month, per Settings > Notifications) has started.
// Restricted to the CLI since it is not authenticated like the admin
// "Run Now" button. Set 'url' in config/app.php so email links are absolute.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require __DIR__ . '/../bootstrap.php';

use App\Notification\NotificationService;

$notifications = new NotificationService();

$sent = $notifications->sendExpiryReminders();
echo "Sent {$sent} expiry reminder(s).\n";

$report = $notifications->sendAdminReportIfDue();
echo match ($report) {
    'sent' => "Admin report sent.\n",
    'empty' => "Admin report skipped: no activity this period.\n",
    'failed' => "Admin report could not be sent; it will be retried next run.\n",
    default => "Admin report not due yet.\n",
};
