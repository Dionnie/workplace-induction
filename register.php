<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\Auth;
use App\Core\Auth\AuthService;

if (!app_config()['registration_enabled']) {
    http_response_code(404);
    exit('Not found.');
}

if (Auth::check()) {
    redirect('/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

    $result = (new AuthService())->registerInductee($email, $password, $passwordConfirmation);

    if ($result['success']) {
        clear_old();
        flash('success', 'Account created. Check your email to verify your address, then log in to complete your profile.');
        redirect('/login.php');
    }

    set_old(['email' => $email]);
    set_errors($result['errors']);
    redirect('/register.php');
}

$errors = get_errors();
require __DIR__ . '/views/auth/register.php';
