<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Inductee\InducteeProfileService;

Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$inductee = (new InducteeProfileService())->getWithUser($id);

if (!$inductee) {
    http_response_code(404);
    exit('Inductee not found.');
}

require __DIR__ . '/../../views/admin/inductees/show.php';
