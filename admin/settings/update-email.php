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

$data = [];
foreach (array_keys(EmailSettingsService::AUDIENCES) as $audience) {
    foreach (['sender_name', 'sender_email', 'cc', 'bcc'] as $field) {
        $data["{$audience}_{$field}"] = trim($_POST["{$audience}_{$field}"] ?? '');
    }
}

$result = (new EmailSettingsService())->updateSenders($data);

if ($result['success']) {
    clear_old();
    flash('success', 'Email settings updated.');
} else {
    set_old($data);
    set_errors($result['errors']);
    flash('error', 'Please fix the highlighted fields.');
}

redirect('/admin/settings/index.php?tab=email');
