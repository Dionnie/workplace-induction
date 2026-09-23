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

$userId = (int) Auth::id();

$data = [
    'password' => (string) ($_POST['password'] ?? ''),
    'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
];

$result = (new UserManagementService())->updatePassword($userId, $data);

if ($result['success']) {
    clear_old();
    flash('success', 'Account updated.');
    redirect('/admin/profile/index.php');
}

set_old($data);
set_errors($result['errors']);
redirect('/admin/profile/index.php');
