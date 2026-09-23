<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Admin\Services\UserManagementService;
use App\Core\Auth;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'user_type' => $_POST['user_type'] ?? '',
        'status' => $_POST['status'] ?? '',
        'password' => (string) ($_POST['password'] ?? ''),
        'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
    ];

    $result = (new UserManagementService())->create($data);

    if ($result['success']) {
        clear_old();
        flash('success', 'User created.');
        redirect('/admin/users/index.php');
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/admin/users/create.php');
}

$errors = get_errors();
require __DIR__ . '/../../views/admin/users/create.php';
