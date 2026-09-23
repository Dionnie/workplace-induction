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

$data = [
    'max_file_size_mb' => trim($_POST['max_file_size_mb'] ?? ''),
    'allowed_types' => $_POST['allowed_types'] ?? [],
];

$result = (new MediaLibraryService())->updateSettings($data);

if ($result['success']) {
    flash('success', 'Media library settings updated.');
} else {
    set_errors($result['errors']);
    flash('error', 'Please fix the errors below.');
}

redirect('/admin/media-library/index.php');
