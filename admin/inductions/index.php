<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Induction\InductionService;

Auth::requireRole('admin');

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$status = in_array($status, ['active', 'inactive'], true) ? $status : '';

$inductions = (new InductionService())->list($status ?: null, $search ?: null);

require __DIR__ . '/../../views/admin/inductions/index.php';
