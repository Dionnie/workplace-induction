<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Compliance\ComplianceService;
use App\Core\Auth;

Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$record = (new ComplianceService())->findForAdmin($id);

if (!$record) {
    http_response_code(404);
    exit('Compliance record not found.');
}

require __DIR__ . '/../../views/admin/compliance/show.php';
