<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Compliance\ComplianceService;
use App\Core\Auth;

Auth::requireRole('inductee');

$authUser = Auth::user();
$records = (new ComplianceService())->listForUser((int) $authUser['id']);

require __DIR__ . '/../../views/inductee/compliance/index.php';
