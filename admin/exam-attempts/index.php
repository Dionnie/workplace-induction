<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamAttemptService;
use App\Exam\ExamRepository;
use App\Induction\InductionRepository;

Auth::requireRole('admin');

$search = trim($_GET['search'] ?? '');
$result = $_GET['result'] ?? '';
$result = in_array($result, ['passed', 'failed'], true) ? $result : '';
$inductionId = (int) ($_GET['induction_id'] ?? 0);
$examId = (int) ($_GET['exam_id'] ?? 0);

$sort = strtolower(trim($_GET['sort'] ?? 'attempted'));
$dir = strtolower(trim($_GET['dir'] ?? 'desc'));

$allowedSorts = ['inductee', 'induction', 'exam', 'score', 'result', 'attempted'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'attempted';
}
if (!in_array($dir, ['asc', 'desc'], true)) {
    $dir = in_array($sort, ['score', 'attempted'], true) ? 'desc' : 'asc';
}

$attempts = (new ExamAttemptService())->list($inductionId ?: null, $examId ?: null, $result ?: null, $search ?: null, $sort, $dir);
$inductions = (new InductionRepository())->all();
$exams = (new ExamRepository())->all();

require __DIR__ . '/../../views/admin/exam-attempts/index.php';
