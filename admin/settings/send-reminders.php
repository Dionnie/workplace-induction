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

$sent = (new NotificationService())->sendExpiryReminders();

flash('success', $sent === 1 ? 'Sent 1 expiry reminder.' : "Sent {$sent} expiry reminders.");
redirect('/admin/settings/index.php');
