<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Admin\Services\UserManagementService;
use App\Core\Auth;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$result = (new UserManagementService())->sendSetupEmail($id);

if ($result['success']) {
    flash('success', 'Setup email sent.');
} else {
    flash('error', $result['errors']['form'] ?? 'Unable to send the setup email.');
}

redirect('/admin/users/edit.php?id=' . $id);
