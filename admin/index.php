<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Admin\Services\DashboardService;
use App\Core\Auth;

Auth::requireRole('admin');

$metrics = (new DashboardService())->metrics();

require __DIR__ . '/../views/admin/dashboard.php';
