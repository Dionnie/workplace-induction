<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\Auth;
use App\Core\Auth\AuthService;

// The page a guest was stopped at, to return to after logging in (docs/core/users.md §6).
$redirectTo = safe_redirect_path($_POST['redirect_to'] ?? $_GET['redirect_to'] ?? null);

if (Auth::check()) {
    redirect(Auth::intendedUrl($redirectTo));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $result = (new AuthService())->attemptLogin($email, $password);

    if ($result['success']) {
        Auth::login((int) $result['user']['id']);
        clear_old();
        redirect(Auth::intendedUrl($redirectTo));
    }

    set_old(['email' => $email]);
    set_errors($result['errors']);
    redirect('/login.php' . redirect_to_query($redirectTo));
}

$errors = get_errors();
require __DIR__ . '/views/auth/login.php';
