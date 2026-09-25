<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Admin\Services\UserManagementService;
use App\Core\Auth;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'email' => trim($_POST['email'] ?? ''),
        'user_type' => $_POST['user_type'] ?? '',
        'status' => $_POST['status'] ?? '',
        'send_setup_email' => !empty($_POST['send_setup_email']) ? '1' : '',
        'password' => (string) ($_POST['password'] ?? ''),
        'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
    ];

    $result = (new UserManagementService())->create($data);

    if ($result['success']) {
        clear_old();

        // The account exists either way; send them to where the email can be sent again.
        if (isset($result['email_error'])) {
            flash('error', "User created, but the setup email couldn't be sent. Check the email settings (Settings › Email), then use Send Setup Email below.");
            redirect('/admin/users/edit.php?id=' . $result['id']);
        }

        flash('success', $data['send_setup_email'] ? 'User created and emailed a link to set their password.' : 'User created.');
        redirect('/admin/users/index.php');
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/admin/users/create.php');
}

$errors = get_errors();
require __DIR__ . '/../../views/admin/users/create.php';
