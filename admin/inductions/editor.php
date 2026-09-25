<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Induction\InductionService;

Auth::requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$service = new InductionService();
$induction = $service->find($id);

if (!$induction) {
    http_response_code(404);
    exit('Induction not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $result = $service->updateContentBlocks($id, $_POST['content_blocks'] ?? '[]');

    header('Content-Type: application/json');
    if (!$result['success']) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => $result['errors']['content_blocks'] ?? $result['errors']['form'] ?? 'Unable to save.',
        ]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Content blocks saved.']);
    exit;
}

// Section slides, each with its lecture slides (docs/application/content-blocks.md #3).
$sections = json_decode((string) $induction['content_blocks'], true);
$initialSlidesJson = json_encode(is_array($sections) ? $sections : []);

require __DIR__ . '/../../views/admin/inductions/editor.php';
