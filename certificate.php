<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Compliance\ComplianceService;

$token = $_GET['token'] ?? '';
$record = $token !== '' ? (new ComplianceService())->findByVerificationToken($token) : null;

require __DIR__ . '/views/public/certificate.php';
