<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamService;
use App\Induction\InductionService;

Auth::requireRole('inductee');

// Inductions need a completed profile (docs/core/auth.md #12).
Auth::requireCompletedProfile('/inductee/profile/index.php', 'Complete your profile before starting an induction.');

$inductionId = (int) ($_GET['induction_id'] ?? $_POST['induction_id'] ?? 0);
$authUser = Auth::user();
$inductionService = new InductionService();
$induction = $inductionService->findActiveForInductee((int) $authUser['id'], $inductionId);

if (!$induction || $induction['exam_id'] === null) {
    http_response_code(404);
    exit('Exam not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $answers = $_POST['answers'] ?? [];
    $answers = is_array($answers) ? $answers : [];

    $result = $inductionService->submitExam((int) $authUser['id'], $inductionId, $answers);

    if (!$result['success']) {
        flash('error', $result['errors']['form'] ?? 'Unable to submit this exam.');
        redirect('/inductee/inductions/show.php?id=' . $inductionId);
    }

    $result = $result['result'];
    require __DIR__ . '/../../views/inductee/exams/result.php';
    exit;
}

$exam = (new ExamService())->find((int) $induction['exam_id']);
if (!$exam || $exam['status'] !== 'active') {
    http_response_code(404);
    exit('Exam not found.');
}

$questions = json_decode((string) $exam['exam_blocks'], true) ?: [];
foreach ($questions as &$question) {
    foreach ($question['options'] as &$option) {
        unset($option['correct']);
    }
    unset($option);
}
unset($question);

require __DIR__ . '/../../views/inductee/exams/take.php';
