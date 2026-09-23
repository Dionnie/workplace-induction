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

$id = (int) ($_POST['id'] ?? 0);
$result = (new MediaLibraryService())->deleteCategory($id);

if ($result['success']) {
    flash('success', 'Category deleted. Its files were kept as uncategorized.');
} else {
    flash('error', $result['errors']['form'] ?? 'Unable to delete category.');
}

redirect('/admin/media-library/index.php');
