<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamService;

Auth::requireRole('admin');

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$status = in_array($status, ['active', 'inactive'], true) ? $status : '';

$allowedSorts = ['title', 'questions', 'pass_percentage', 'status', 'created_at'];
$sort = strtolower(trim($_GET['sort'] ?? 'created_at'));
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'created_at';
}

$dir = strtolower(trim($_GET['dir'] ?? ''));
if (!in_array($dir, ['asc', 'desc'], true)) {
    $dir = $sort === 'created_at' ? 'desc' : 'asc';
}

$exams = (new ExamService())->list($status ?: null, $search ?: null, $sort, $dir);

require __DIR__ . '/../../views/admin/exams/index.php';
