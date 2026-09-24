<?php

declare(strict_types=1);

/**
 * Top navbar with horizontal links, for layouts with only a few sections
 * (the inductee area). The admin area uses a sidebar instead; see
 * admin-header.php and docs/core/design-system.html#navigation.
 *
 * @var array<string, array{0: string, 1: string}> $navItems key => [label, url]
 * @var string $currentPage Key of the active item ('profile' for My Profile).
 * @var string $homeUrl
 * @var string $profileUrl
 * @var string $navbarExpand Breakpoint where the menu stops collapsing.
 */
$appName = site_settings()['company_name'];
$appLogo = site_settings()['logo_url'];
$accountNameClass = '';
?>
<nav class="navbar navbar-expand-<?= e($navbarExpand) ?> navbar-dark app-navbar d-print-none">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e($homeUrl) ?>">
            <?php if ($appLogo): ?><img src="<?= e($appLogo) ?>" alt="" class="brand-logo"><?php endif; ?>
            <span class="text-truncate"><?= e($appName) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#app-navbar-menu"
                aria-controls="app-navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="app-navbar-menu">
            <ul class="navbar-nav me-auto">
                <?php // Loop variables are prefixed: views share this scope. ?>
                <?php foreach ($navItems as $navKey => [$navLabel, $navUrl]): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === $navKey ? 'active' : '' ?>" href="<?= e($navUrl) ?>"
                            <?= $currentPage === $navKey ? 'aria-current="page"' : '' ?>><?= e($navLabel) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php require __DIR__ . '/account-menu.php'; ?>
        </div>
    </div>
</nav>
