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

$records = (new ComplianceService())->listAll($status ?: null, $inductionId ?: null, $search ?: null);
$inductions = (new InductionRepository())->all();

require __DIR__ . '/../../views/admin/compliance/index.php';
