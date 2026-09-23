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
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');

    $result = (new AuthService())->registerInductee($email, $password, $firstName, $lastName);

    if ($result['success']) {
        clear_old();
        flash('success', 'Account created. Please check your email to verify your address before logging in.');
        redirect('/login.php');
    }

    set_old(['email' => $email, 'first_name' => $firstName, 'last_name' => $lastName]);
    set_errors($result['errors']);
    redirect('/register.php');
}

$errors = get_errors();
require __DIR__ . '/views/auth/register.php';
