<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamService;
use App\Induction\InductionService;

Auth::requireRole('admin');

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

    $result = (new InductionService())->create($data);

    if ($result['success']) {
        clear_old();
        flash('success', 'Induction created. Now add its content blocks.');
        redirect('/admin/inductions/editor.php?id=' . $result['id']);
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/admin/inductions/create.php');
}

$errors = get_errors();
$exams = (new ExamService())->list('active');
require __DIR__ . '/../../views/admin/inductions/create.php';
