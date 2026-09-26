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
 *     Bootstrap's default .container width (e.g. the induction slide
 *     view, whose 1024px slide is wider than the standard container at lg).
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
        <?php if (Auth::switchedFrom() !== null): ?>
            <!-- An administrator is using this account (Switch Account, docs/core/users.md §10). -->
            <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <i class="bi bi-person-badge me-1" aria-hidden="true"></i>
                    You are using <strong><?= e(trim(($authUser['first_name'] ?? '') . ' ' . ($authUser['last_name'] ?? '')) ?: (string) $authUser['email']) ?></strong>'s account as an administrator.
                </div>
                <form method="post" action="/admin/users/switch-back.php">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm">Switch Back</button>
                </form>
            </div>
        <?php endif; ?>
        <?php require __DIR__ . '/flash.php'; ?>
