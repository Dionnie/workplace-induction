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

$result = (new MediaLibraryService())->deleteItems(is_array($ids) ? $ids : []);

if ($result['success']) {
    flash('success', sprintf('%d file(s) deleted.', $result['deleted']));
} else {
    flash('error', $result['errors']['form'] ?? 'Unable to delete files.');
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
