<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\Auth\AuthService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $token = (string) ($_POST['token'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

    $result = (new AuthService())->resetPassword($token, $password, $passwordConfirmation);

    if ($result['success']) {
        flash('success', 'Your password has been reset. You can now log in.');
        redirect('/login.php');
    }

    set_errors($result['errors']);
    redirect('/reset-password.php?token=' . urlencode($token));
}

$token = $_GET['token'] ?? '';
$errors = get_errors();

if ($token === '') {
    $errors = ['form' => 'This password reset link is invalid or has expired.'];
}

require __DIR__ . '/views/auth/reset-password.php';
