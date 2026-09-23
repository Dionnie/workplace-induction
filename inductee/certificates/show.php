<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Compliance\ComplianceService;
use App\Core\Auth;

Auth::requireRole('inductee');

$id = (int) ($_GET['id'] ?? 0);
$authUser = Auth::user();
$record = (new ComplianceService())->findOwnedByUser($id, (int) $authUser['id']);

if (!$record) {
    http_response_code(404);
    exit('Certificate not found.');
}

require __DIR__ . '/../../views/inductee/certificates/show.php';
