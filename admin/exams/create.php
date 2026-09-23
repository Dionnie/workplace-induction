<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'pass_percentage' => trim($_POST['pass_percentage'] ?? ''),
        'status' => $_POST['status'] ?? '',
        'exam_blocks' => $_POST['exam_blocks'] ?? '[]',
    ];

    $result = (new ExamService())->create($data);

    if ($result['success']) {
        clear_old();
        flash('success', 'Exam created.');
        redirect('/admin/exams/index.php');
    }

    set_old($data);
    set_errors($result['errors']);
    redirect('/admin/exams/create.php');
}

$errors = get_errors();
require __DIR__ . '/../../views/admin/exams/create.php';
