<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamService;
use App\Induction\InductionService;

Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$service = new InductionService();
$induction = $service->find($id);

if (!$induction) {
    http_response_code(404);
    exit('Induction not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'code' => trim($_POST['code'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'exam_id' => trim($_POST['exam_id'] ?? ''),
        'validity_months' => trim($_POST['validity_months'] ?? ''),
        'status' => $_POST['status'] ?? '',
    ];

    $result = $service->update($id, $data);

    if ($result['success']) {
        clear_old();
        flash('success', 'Induction updated.');
        redirect('/admin/inductions/index.php');
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/admin/inductions/edit.php?id=' . $id);
}

$errors = get_errors();

$examService = new ExamService();
$exams = $examService->list('active');

if ($induction['exam_id'] !== null && !in_array((int) $induction['exam_id'], array_column($exams, 'id'), true)) {
    $linkedExam = $examService->find((int) $induction['exam_id']);
    if ($linkedExam) {
        $linkedExam['title'] .= ' (inactive)';
        $exams[] = $linkedExam;
    }
}

require __DIR__ . '/../../views/admin/inductions/edit.php';
