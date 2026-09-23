<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\MediaLibrary\MediaLibraryService;

Auth::requireRole('admin');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'errors' => ['file' => 'Method not allowed.']]);
    exit;
}

verify_csrf();

$categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;

$result = (new MediaLibraryService())->upload($_FILES['file'] ?? [], $categoryId);

http_response_code($result['success'] ? 200 : 422);
echo json_encode($result);
