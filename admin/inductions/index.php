<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Induction\InductionService;

Auth::requireRole('admin');

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$status = in_array($status, ['active', 'inactive'], true) ? $status : '';

$sort = strtolower(trim($_GET['sort'] ?? 'created_at'));
$dir = strtolower(trim($_GET['dir'] ?? 'desc'));

$allowedSorts = ['title', 'code', 'validity', 'status', 'created_at'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'created_at';
}
if (!in_array($dir, ['asc', 'desc'], true)) {
    $dir = in_array($sort, ['title', 'code'], true) ? 'asc' : 'desc';
}

$inductions = (new InductionService())->list($status ?: null, $search ?: null, $sort, $dir);

require __DIR__ . '/../../views/admin/inductions/index.php';
