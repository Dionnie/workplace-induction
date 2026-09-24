<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Notification\EmailSettingsService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$data = [
    'notify_inductee_on_completion' => isset($_POST['notify_inductee_on_completion']) ? '1' : '',
    'notify_inductee_on_expiry' => isset($_POST['notify_inductee_on_expiry']) ? '1' : '',
    'expiry_reminder_days' => trim($_POST['expiry_reminder_days'] ?? ''),
    'admin_notification_frequency' => trim($_POST['admin_notification_frequency'] ?? ''),
    'notify_admin_on_registration' => isset($_POST['notify_admin_on_registration']) ? '1' : '',
    'notify_admin_on_completion' => isset($_POST['notify_admin_on_completion']) ? '1' : '',
    'notify_admin_on_expired' => isset($_POST['notify_admin_on_expired']) ? '1' : '',
];

$result = (new EmailSettingsService())->updateNotifications($data);

if ($result['success']) {
    clear_old();
    flash('success', 'Notification settings updated.');
} else {
    set_old($data);
    set_errors($result['errors']);
    flash('error', 'Please fix the highlighted fields.');
}

redirect('/admin/settings/index.php?tab=notifications');
