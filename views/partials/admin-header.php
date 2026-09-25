<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * Admin layout, part 1 of 2 (close with admin-footer.php): a slim top bar
 * (brand, account menu) and a sidebar listing every admin section, grouped
 * as in admin-menu.php. Below lg the sidebar becomes a drawer opened from
 * the top bar. See docs/rules/design-system.html#navigation.
 *
 * @var string $pageTitle
 * @var string $currentPage Key of the active admin-menu.php item.
 */
$authUser = Auth::user();
$currentPage = $currentPage ?? '';
$appName = site_settings()['company_name'];
$appLogo = site_settings()['logo_url'];
$documentTitle = $pageTitle . ' · ' . $appName;
$adminMenu = require __DIR__ . '/admin-menu.php';
$profileUrl = '/admin/profile/index.php';
$accountNameClass = 'd-none d-md-inline';

// Dashboard sits above the groups, without a heading.
$sidebarGroups = ['' => ['dashboard' => ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => '/admin/index.php']]] + $adminMenu;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/head.php'; ?>
</head>
<body>

<header class="navbar navbar-expand navbar-dark app-navbar sticky-top d-print-none">
    <div class="container-fluid flex-nowrap px-3 px-lg-4">
        <button class="navbar-toggler d-inline-block d-lg-none me-2" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Open menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand d-flex align-items-center gap-2" href="/admin/index.php">
            <?php if ($appLogo): ?><img src="<?= e($appLogo) ?>" alt="" class="brand-logo"><?php endif; ?>
            <span class="text-truncate"><?= e($appName) ?></span>
        </a>
        <?php require __DIR__ . '/account-menu.php'; ?>
    </div>
</header>

<div class="admin-shell">
    <aside class="admin-sidebar d-print-none">
        <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="admin-sidebar" aria-labelledby="admin-sidebar-title">
            <div class="offcanvas-header">
                <h2 class="offcanvas-title fs-6" id="admin-sidebar-title">Menu</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                <nav class="admin-nav" aria-label="Administration">
                    <?php // Loop variables are prefixed: views share this scope and may use $items etc. ?>
                    <?php foreach ($sidebarGroups as $sidebarGroup => $sidebarItems): ?>
                        <?php if ($sidebarGroup !== ''): ?>
                            <div class="admin-nav-group small text-uppercase text-muted fw-semibold"><?= e($sidebarGroup) ?></div>
                        <?php endif; ?>
                        <ul class="nav flex-column">
                            <?php foreach ($sidebarItems as $sidebarKey => $sidebarItem): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= $currentPage === $sidebarKey ? 'active' : '' ?>" href="<?= e($sidebarItem['url']) ?>"
                                        <?= $currentPage === $sidebarKey ? 'aria-current="page"' : '' ?>>
                                        <i class="bi <?= e($sidebarItem['icon']) ?>" aria-hidden="true"></i><?= e($sidebarItem['label']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    </aside>

    <main class="admin-main py-4">
        <div class="container-xxl mx-0 px-3 px-lg-4">
            <?php require __DIR__ . '/flash.php'; ?>
