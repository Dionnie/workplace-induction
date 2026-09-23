<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Notification\EmailSettingsService;

Auth::requireRole('admin');

$service = new EmailSettingsService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'sender_name' => trim($_POST['sender_name'] ?? ''),
        'sender_email' => trim($_POST['sender_email'] ?? ''),
        'cc' => trim($_POST['cc'] ?? ''),
        'bcc' => trim($_POST['bcc'] ?? ''),
        'notify_admin_on_completion' => isset($_POST['notify_admin_on_completion']) ? '1' : '',
        'notify_inductee_on_completion' => isset($_POST['notify_inductee_on_completion']) ? '1' : '',
        'notify_inductee_on_expiry' => isset($_POST['notify_inductee_on_expiry']) ? '1' : '',
        'expiry_reminder_days' => trim($_POST['expiry_reminder_days'] ?? ''),
    ];

    $result = $service->update($data);

    if ($result['success']) {
        clear_old();
        flash('success', 'Settings updated.');
        redirect('/admin/settings/index.php');
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/admin/settings/index.php');
}

$settings = $service->get();
$errors = get_errors();

require __DIR__ . '/../../views/admin/settings/index.php';
