<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\Auth;
use App\Core\Auth\AuthService;

if (Auth::check()) {
    $user = Auth::user();
    redirect($user['user_type'] === 'admin' ? '/admin/index.php' : '/inductee/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $result = (new AuthService())->attemptLogin($email, $password);

    if ($result['success']) {
        Auth::login((int) $result['user']['id']);
        clear_old();
        redirect($result['user']['user_type'] === 'admin' ? '/admin/index.php' : '/inductee/index.php');
    }

    set_old(['email' => $email]);
    set_errors($result['errors']);
    redirect('/login.php');
}

$errors = get_errors();
require __DIR__ . '/views/auth/login.php';
