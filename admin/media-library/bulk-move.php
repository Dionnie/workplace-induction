<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\MediaLibrary\MediaLibraryService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$ids = $_POST['ids'] ?? [];
$targetCategoryId = isset($_POST['target_category_id']) && $_POST['target_category_id'] !== ''
    ? (int) $_POST['target_category_id']
    : null;

$result = (new MediaLibraryService())->moveItems(is_array($ids) ? $ids : [], $targetCategoryId);

if ($result['success']) {
    flash('success', sprintf('%d file(s) moved.', $result['moved']));
} else {
    flash('error', $result['errors']['form'] ?? $result['errors']['category_id'] ?? 'Unable to move files.');
}

$query = [];
if (!empty($_POST['category_id'])) {
    $query['category_id'] = $_POST['category_id'];
}
if (!empty($_POST['search'])) {
    $query['search'] = $_POST['search'];
}
if (($_POST['view'] ?? '') === 'list') {
    $query['view'] = 'list';
}

$redirect = '/admin/media-library/index.php';
if ($query) {
    $redirect .= '?' . http_build_query($query);
}

redirect($redirect);
