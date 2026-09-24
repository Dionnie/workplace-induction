<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Admin\Services\UserManagementService;
use App\Core\Auth;

Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$service = new UserManagementService();
$user = $service->find($id);

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'email' => trim($_POST['email'] ?? ''),
        'status' => $_POST['status'] ?? '',
        'profile_completed' => !empty($_POST['profile_completed']) ? '1' : '',
        'email_verified' => !empty($_POST['email_verified']) ? '1' : '',
        'password' => (string) ($_POST['password'] ?? ''),
        'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
    ];

    if ($id === Auth::id() && $data['status'] !== 'active') {
        set_old($data);
        set_errors(['status' => 'You cannot deactivate or suspend your own account.']);
        redirect('/admin/users/edit.php?id=' . $id);
    }

    $result = $service->update($id, $data);

    if ($result['success']) {
        clear_old();
        flash('success', 'User updated.');
        redirect('/admin/users/index.php');
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/admin/users/edit.php?id=' . $id);
}

$errors = get_errors();
require __DIR__ . '/../../views/admin/users/edit.php';
