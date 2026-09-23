<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Compliance\ComplianceService;
use App\Core\Auth;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$result = (new ComplianceService())->revoke($id);

if ($result['success']) {
    flash('success', 'Compliance record revoked.');
} else {
    flash('error', $result['error']);
}

redirect('/admin/compliance/index.php');
