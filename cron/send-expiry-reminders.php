<?php

declare(strict_types=1);

// Intended to be run daily via the server's task scheduler, e.g.:
//   php cron/send-expiry-reminders.php
// Restricted to the CLI since it is not authenticated like the admin
// "Send Reminders Now" button.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require __DIR__ . '/../bootstrap.php';

use App\Notification\NotificationService;

$sent = (new NotificationService())->sendExpiryReminders();

echo "Sent {$sent} expiry reminder(s).\n";
