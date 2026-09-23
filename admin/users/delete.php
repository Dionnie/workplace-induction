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
$cascade = !empty($_POST['cascade']);

$result = (new UserManagementService())->delete($id, $cascade);

if ($result['success']) {
    flash('success', 'User deleted.');
} else {
    flash('error', $result['errors']['form'] ?? 'Unable to delete user.');
}

redirect('/admin/users/index.php');
