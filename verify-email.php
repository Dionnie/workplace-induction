<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Core\Auth\AuthService;

$token = $_GET['token'] ?? '';
$verified = $token !== '' && (new AuthService())->verifyEmail($token);

require __DIR__ . '/views/auth/verify-email.php';
