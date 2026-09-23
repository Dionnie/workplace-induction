<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\MediaLibrary\MediaLibraryService;

Auth::requireRole('admin');

$service = new MediaLibraryService();

$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
$search = trim($_GET['search'] ?? '');

// The view toggle/delete/bulk-move all pass ?view= explicitly, which is also
// remembered in a cookie so a plain visit (e.g. the nav link, with no query
// string at all) still opens in the last view the admin chose.
if (isset($_GET['view']) && in_array($_GET['view'], ['grid', 'list'], true)) {
    $view = $_GET['view'];
    setcookie('media_library_view', $view, [
        'expires' => time() + 60 * 60 * 24 * 365,
        'path' => '/admin/media-library/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
} else {
    $view = ($_COOKIE['media_library_view'] ?? '') === 'list' ? 'list' : 'grid';
}

$categories = $service->listCategories();
$items = $service->listItems($categoryId, $search ?: null);
$settings = $service->settings();
$availableTypes = $service->availableTypes()['extensions'];
$errors = get_errors();

require __DIR__ . '/../../views/admin/media-library/index.php';
