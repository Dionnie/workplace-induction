<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * Account dropdown at the right of the top bar, shared by the admin and
 * inductee layouts (docs/core/design-system.html#navigation).
 *
 * @var string $profileUrl
 * @var string $currentPage
 * @var string $accountNameClass Classes for the name beside the icon, e.g.
 *     'd-none d-md-inline' to show only the icon on phones.
 */
$authUser = Auth::user();
$accountName = trim(($authUser['first_name'] ?? '') . ' ' . ($authUser['last_name'] ?? '')) ?: (string) ($authUser['email'] ?? '');
?>
<ul class="navbar-nav ms-auto">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle <?= $currentPage === 'profile' ? 'active' : '' ?>" href="#" role="button"
           data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu for <?= e($accountName) ?>">
            <i class="bi bi-person-circle" aria-hidden="true"></i>
            <span class="<?= e($accountNameClass ?? '') ?> ms-1"><?= e($accountName) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text small text-muted text-truncate"><?= e((string) ($authUser['email'] ?? '')) ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item <?= $currentPage === 'profile' ? 'active' : '' ?>" href="<?= e($profileUrl) ?>"
                    <?= $currentPage === 'profile' ? 'aria-current="page"' : '' ?>><i class="bi bi-person me-2" aria-hidden="true"></i>My Profile</a>
            </li>
            <li><a class="dropdown-item" href="/logout.php"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Log Out</a></li>
        </ul>
    </li>
</ul>
