<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Admin\Services\UserManagementService;
use App\Core\Auth;

Auth::requireRole('admin');

$search = trim($_GET['search'] ?? '');
$userType = $_GET['user_type'] ?? '';
$userType = in_array($userType, ['admin', 'inductee'], true) ? $userType : '';

$sort = strtolower(trim($_GET['sort'] ?? 'created_at'));
$dir = strtolower(trim($_GET['dir'] ?? 'desc'));

$allowedSorts = ['name', 'email', 'user_type', 'status', 'profile_completed', 'created_at'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'created_at';
}
if (!in_array($dir, ['asc', 'desc'], true)) {
    $dir = $sort === 'created_at' ? 'desc' : 'asc';
}

$users = (new UserManagementService())->list($userType ?: null, $search ?: null, $sort, $dir);

require __DIR__ . '/../../views/admin/users/index.php';
