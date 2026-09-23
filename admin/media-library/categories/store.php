<?php

declare(strict_types=1);

require __DIR__ . '/../../../bootstrap.php';

use App\Core\Auth;
use App\MediaLibrary\MediaLibraryService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$result = (new MediaLibraryService())->createCategory($_POST['name'] ?? '');

if ($result['success']) {
    flash('success', 'Category added.');
} else {
    set_errors($result['errors']);
    flash('error', $result['errors']['name'] ?? 'Unable to add category.');
}

redirect('/admin/media-library/index.php');
