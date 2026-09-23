<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamAttemptService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$result = (new ExamAttemptService())->delete($id);

if ($result['success']) {
    flash('success', 'Exam attempt deleted.');
} else {
    flash('error', $result['errors']['form'] ?? 'Unable to delete exam attempt.');
}

redirect('/admin/exam-attempts/index.php');
