<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamAttemptService;

Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$attempt = (new ExamAttemptService())->find($id);

if (!$attempt) {
    http_response_code(404);
    exit('Exam attempt not found.');
}

require __DIR__ . '/../../views/admin/exam-attempts/show.php';
