<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Inductee\InducteeProfileService;

Auth::requireRole('admin');

$search = trim($_GET['search'] ?? '');
$employmentType = trim($_GET['employment_type'] ?? '');
$status = trim($_GET['status'] ?? '');
$profileStatus = trim($_GET['profile_completed'] ?? '');

if ($employmentType !== '' && !in_array($employmentType, InducteeProfileService::EMPLOYMENT_TYPES, true)) {
    $employmentType = '';
}
if ($status !== '' && !in_array($status, ['active', 'inactive', 'suspended'], true)) {
    $status = '';
}
if ($profileStatus !== '' && !in_array($profileStatus, ['complete', 'incomplete'], true)) {
    $profileStatus = '';
}

$sort = strtolower(trim($_GET['sort'] ?? 'created_at'));
$dir = strtolower(trim($_GET['dir'] ?? 'desc'));

$allowedSorts = [
    'name',
    'email',
    'contact_number',
    'job_position',
    'company',
    'employment_type',
    'status',
    'profile_completed',
    'created_at',
];

if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'created_at';
}
if (!in_array($dir, ['asc', 'desc'], true)) {
    $dir = $sort === 'created_at' ? 'desc' : 'asc';
}

$service = new InducteeProfileService();
$inductees = $service->listAll(
    $search !== '' ? $search : null,
    $employmentType !== '' ? $employmentType : null,
    $status !== '' ? $status : null,
    $profileStatus !== '' ? $profileStatus : null,
    $sort,
    $dir
);

require __DIR__ . '/../../views/admin/inductees/index.php';
