<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Induction\InductionService;

Auth::requireRole('inductee');

$authUser = Auth::user();
$inductions = (new InductionService())->listActiveForInductee((int) $authUser['id']);

require __DIR__ . '/../views/inductee/dashboard.php';
