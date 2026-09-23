<?php
/** @var string $pageTitle */
$appName = app_config()['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-surface-subtle">

<header class="border-bottom bg-white">
    <div class="container py-3">
        <a href="/index.php" class="fs-5 fw-semibold text-brand text-decoration-none"><?= e($appName) ?></a>
    </div>
</header>

<main class="py-5">
    <div class="container" style="max-width: 420px;">
