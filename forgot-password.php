<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\Auth\AuthService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        (new AuthService())->requestPasswordReset($email);
    }

    clear_old();
    flash('success', 'If an account exists for that email address, a password reset link has been sent.');
    redirect('/forgot-password.php');
}

require __DIR__ . '/views/auth/forgot-password.php';
