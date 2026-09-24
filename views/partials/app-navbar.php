<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * Top navbar shared by the admin and inductee layouts
 * (docs/core/design-system.html#navbar).
 *
 * @var array<string, array{0: string, 1: string}> $navItems key => [label, url]
 * @var string $currentPage Key of the active item ('profile' for My Profile).
 * @var string $homeUrl
 * @var string $profileUrl
 * @var string $navbarExpand Breakpoint where the menu stops collapsing.
 */
$appName = site_settings()['company_name'];
$appLogo = site_settings()['logo_url'];
$authUser = Auth::user();
$accountName = trim(($authUser['first_name'] ?? '') . ' ' . ($authUser['last_name'] ?? '')) ?: (string) ($authUser['email'] ?? '');
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
                <?php foreach ($navItems as $key => [$label, $url]): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === $key ? 'active' : '' ?>" href="<?= e($url) ?>"
                            <?= $currentPage === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $currentPage === 'profile' ? 'active' : '' ?>" href="#" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account">
                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                        <span class="d-<?= e($navbarExpand) ?>-none ms-1"><?= e($accountName) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted text-truncate"><?= e($accountName) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?= $currentPage === 'profile' ? 'active' : '' ?>" href="<?= e($profileUrl) ?>"
                                <?= $currentPage === 'profile' ? 'aria-current="page"' : '' ?>>My Profile</a>
                        </li>
                        <li><a class="dropdown-item" href="/logout.php">Log Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
