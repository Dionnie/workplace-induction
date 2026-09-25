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
    // The same answer whether or not the email exists, or a link was just sent (docs/core/users.md §7).
    flash('success', 'If an account exists for that email address, a password reset link has been sent. We send at most one a minute, so if you asked moments ago, use that email.');
    redirect('/forgot-password.php');
}

require __DIR__ . '/views/auth/forgot-password.php';
