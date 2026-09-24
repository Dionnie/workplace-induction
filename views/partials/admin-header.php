<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * Admin layout, part 1 of 2 (close with admin-footer.php).
 * See docs/core/design-system.html#page-shell.
 *
 * @var string $pageTitle
 * @var string $currentPage Key of the active navbar item.
 */
$authUser = Auth::user();
$currentPage = $currentPage ?? '';
$documentTitle = $pageTitle . ' · ' . site_settings()['company_name'];

$navItems = [
    'dashboard' => ['Dashboard', '/admin/index.php'],
    'inductions' => ['Inductions', '/admin/inductions/index.php'],
    'exams' => ['Exams', '/admin/exams/index.php'],
    'exam-attempts' => ['Exam Attempts', '/admin/exam-attempts/index.php'],
    'compliance' => ['Compliance', '/admin/compliance/index.php'],
    'users' => ['Users', '/admin/users/index.php'],
    'media-library' => ['Media Library', '/admin/media-library/index.php'],
    'tools' => ['Tools', '/admin/tools/index.php'],
    'settings' => ['Settings', '/admin/settings/index.php'],
];
$homeUrl = '/admin/index.php';
$profileUrl = '/admin/profile/index.php';
$navbarExpand = 'xl';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/head.php'; ?>
</head>
<body>

<?php require __DIR__ . '/app-navbar.php'; ?>

<main class="py-4">
    <div class="container">
        <?php require __DIR__ . '/flash.php'; ?>
