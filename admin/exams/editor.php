<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Exam\ExamService;

Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$service = new ExamService();
$exam = $service->find($id);

if (!$exam) {
    http_response_code(404);
    exit('Exam not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $result = $service->updateExamBlocks($id, $_POST['exam_blocks'] ?? '[]');

    header('Content-Type: application/json');
    if (!$result['success']) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => $result['errors']['exam_blocks'] ?? $result['errors']['form'] ?? 'Unable to save.',
            'block_id' => $result['block_id'] ?? null,
        ]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Exam blocks saved.']);
    exit;
}

$initialBlocksJson = json_encode(json_decode((string) $exam['exam_blocks'], true) ?: []);

require __DIR__ . '/../../views/admin/exams/editor.php';
