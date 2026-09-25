<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * Inductee layout, part 1 of 2 (close with inductee-footer.php).
 * See docs/rules/design-system.html#page-shell.
 *
 * @var string $pageTitle
 * @var string $currentPage Key of the active navbar item.
 * @var bool $wideContainer Set true for pages that need more than
 *     Bootstrap's default .container width (e.g. a page with its own
 *     left sidebar rail, where a 1024px reading canvas plus a 260px
 *     sidebar needs more room than the standard container gives).
 */
$authUser = Auth::user();
$currentPage = $currentPage ?? '';
$wideContainer = $wideContainer ?? false;
$documentTitle = $pageTitle . ' · ' . site_settings()['company_name'];

$navItems = [
    'dashboard' => ['Dashboard', '/inductee/index.php'],
    'compliance' => ['Compliance', '/inductee/compliance/index.php'],
];
$homeUrl = '/inductee/index.php';
$profileUrl = '/inductee/profile/index.php';
$navbarExpand = 'md';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/head.php'; ?>
</head>
<body>

<?php require __DIR__ . '/app-navbar.php'; ?>

<main class="py-4">
    <div class="<?= $wideContainer ? 'container-fluid px-4' : 'container' ?>">
        <?php require __DIR__ . '/flash.php'; ?>
