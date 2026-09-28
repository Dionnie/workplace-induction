<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Compliance\ComplianceService;
use App\Core\Auth;
use App\Induction\InductionRepository;

Auth::requireRole('admin');

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$status = in_array($status, ['active', 'expired', 'superseded', 'revoked'], true) ? $status : '';
$inductionId = (int) ($_GET['induction_id'] ?? 0);

$sort = strtolower(trim($_GET['sort'] ?? 'created_at'));
$dir = strtolower(trim($_GET['dir'] ?? 'desc'));

$allowedSorts = ['inductee', 'induction', 'certificate_number', 'issue_date', 'expiry_date', 'status', 'created_at'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'created_at';
}
if (!in_array($dir, ['asc', 'desc'], true)) {
    $dir = in_array($sort, ['issue_date', 'expiry_date', 'created_at'], true) ? 'desc' : 'asc';
}

$records = (new ComplianceService())->listAll($status ?: null, $inductionId ?: null, $search ?: null, $sort, $dir);
$inductions = (new InductionRepository())->all();

require __DIR__ . '/../../views/admin/compliance/index.php';
