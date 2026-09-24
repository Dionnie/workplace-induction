<?php
/**
 * Guest layout, part 1 of 2 (close with guest-footer.php): log in, register,
 * password reset, certificate verification. Pages render their own flash
 * messages inside their card. See docs/core/design-system.html#page-shell.
 *
 * @var string $pageTitle
 * @var string $guestContainerClass Optional width class; default .container-guest.
 */
$appName = site_settings()['company_name'];
$appLogo = site_settings()['logo_url'];
$documentTitle = $pageTitle . ' · ' . $appName;
$guestContainerClass = $guestContainerClass ?? 'container-guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/head.php'; ?>
</head>
<body>

<header class="border-bottom bg-white">
    <div class="container py-3">
        <a href="/index.php" class="d-inline-flex align-items-center gap-2 fs-5 fw-semibold text-primary text-decoration-none">
            <?php if ($appLogo): ?><img src="<?= e($appLogo) ?>" alt="" class="brand-logo"><?php endif; ?>
            <?= e($appName) ?>
        </a>
    </div>
</header>

<main class="py-5">
    <div class="container <?= e($guestContainerClass) ?>">
