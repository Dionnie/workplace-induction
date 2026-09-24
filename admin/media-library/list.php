<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\MediaLibrary\MediaLibraryService;

// Read-only JSON data source for the MediaPicker modal (assets/js/media-picker.js).
// A GET, read-only endpoint needs no CSRF check, matching every other GET
// route in this app. Separate from admin/media-library/index.php (which
// renders the full admin page) so any page can fetch the same data without
// a full-page navigation.
Auth::requireRole('admin');

header('Content-Type: application/json');

$service = new MediaLibraryService();

$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
$search = trim($_GET['search'] ?? '');

$categories = $service->listCategories();
$items = array_map(
    static fn (array $item): array => [
        'id' => (int) $item['id'],
        'category_id' => $item['category_id'] !== null ? (int) $item['category_id'] : null,
        'original_filename' => $item['original_filename'],
        'mime_type' => $item['mime_type'],
        'size' => (int) $item['size'],
        'url' => '/assets/uploads/media-library/' . $item['filename'],
        'created_at' => $item['created_at'],
    ],
    $service->listItems($categoryId, $search ?: null)
);

echo json_encode([
    'success' => true,
    'categories' => $categories,
    'items' => $items,
]);
