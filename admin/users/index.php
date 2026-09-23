<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Admin\Services\UserManagementService;
use App\Core\Auth;

Auth::requireRole('admin');

$search = trim($_GET['search'] ?? '');
$userType = $_GET['user_type'] ?? '';
$userType = in_array($userType, ['admin', 'inductee'], true) ? $userType : '';

$users = (new UserManagementService())->list($userType ?: null, $search ?: null);

require __DIR__ . '/../../views/admin/users/index.php';
