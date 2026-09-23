<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamService;

Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$service = new ExamService();
$exam = $service->find($id);

if (!$exam) {
    http_response_code(404);
    exit('Exam not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'pass_percentage' => trim($_POST['pass_percentage'] ?? ''),
        'status' => $_POST['status'] ?? '',
        'exam_blocks' => $_POST['exam_blocks'] ?? '[]',
    ];

    $result = $service->update($id, $data);

    if ($result['success']) {
        clear_old();
        flash('success', 'Exam updated.');
        redirect('/admin/exams/index.php');
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/admin/exams/edit.php?id=' . $id);
}

$errors = get_errors();
require __DIR__ . '/../../views/admin/exams/edit.php';
