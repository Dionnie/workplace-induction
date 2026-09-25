<?php
/**
 * <head> contents shared by every page: Bootstrap, Bootstrap Icons, app.css
 * and the site's colour theme (docs/rules/design-system.html#page-shell).
 *
 * @var string $documentTitle Browser tab text.
 */
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($documentTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <?= theme_style_tag() ?>
