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

$attempts = (new ExamAttemptService())->list($inductionId ?: null, $examId ?: null, $result ?: null, $search ?: null);
$inductions = (new InductionRepository())->all();
$exams = (new ExamRepository())->all();

require __DIR__ . '/../../views/admin/exam-attempts/index.php';
