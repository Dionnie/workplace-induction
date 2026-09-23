<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Admin\Services\UserManagementService;
use App\Core\Auth;

Auth::requireRole('admin');

$userId = (int) Auth::id();
$user = (new UserManagementService())->find($userId);

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}

$errors = get_errors();
require __DIR__ . '/../../views/admin/profile/index.php';
