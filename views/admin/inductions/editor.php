<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $induction
 * @var string $initialBlocksJson
 */
$appName = app_config()['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Content &middot; <?= e($induction['title']) ?> &middot; <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-surface-subtle">

<header class="cb-studio-header border-bottom bg-white sticky-top">
    <div class="cb-studio-header-inner container-fluid px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="cb-studio-header-info d-flex align-items-center gap-3 overflow-hidden">
            <a href="/admin/inductions/edit.php?id=<?= (int) $induction['id'] ?>" class="cb-back-link btn btn-outline-secondary flex-shrink-0">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Induction Details
            </a>
            <span class="cb-induction-title fw-semibold text-truncate"><?= e($induction['title']) ?></span>
            <span id="cb-save-status" class="cb-save-status badge text-bg-secondary">Saved</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="cb-outline-toggle btn btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#cbOutlineOffcanvas" aria-controls="cbOutlineOffcanvas">
                <i class="bi bi-list-ul me-1" aria-hidden="true"></i>Course Outline
            </button>
            <button type="button" id="cb-save-btn" class="cb-save-button btn btn-primary">
                <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Save Changes
            </button>
        </div>
    </div>
</header>

<!-- Same outline content, for narrower viewports via the toggle button above. -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="cbOutlineOffcanvas" aria-labelledby="cbOutlineOffcanvasLabel">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title fs-6 fw-semibold" id="cbOutlineOffcanvasLabel">Course Outline</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="cb-outline-list list-unstyled mb-0" id="cb-outline-list-offcanvas"></ul>
        <p class="cb-outline-empty mb-0" id="cb-outline-empty-offcanvas">Add a Section to start building your outline.</p>
    </div>
</div>

<main class="cb-studio-main py-5">
    <div class="cb-studio-container container-fluid px-4">
        <div class="cb-content-area">
            <!-- Floating sidebar rail: starts level with the canvas below (not the viewport top), so it never overlaps whatever precedes it, and shown only when there's enough gutter beside the centered canvas not to overlap it (see .cb-outline-sidebar-rail in app.css). -->
            <aside class="cb-outline-sidebar-rail">
                <nav class="cb-outline-sidebar card border-0 shadow-sm p-3" aria-label="Course outline">
                    <div class="cb-outline-heading small text-uppercase text-muted fw-semibold mb-2">Course Outline</div>
                    <ul class="cb-outline-list list-unstyled mb-0" id="cb-outline-list"></ul>
                    <p class="cb-outline-empty mb-0" id="cb-outline-empty">Add a Section to start building your outline.</p>
                </nav>
            </aside>

            <div class="content-canvas">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div id="cb-blocks" class="cb-blocks-container" data-initial="<?= e($initialBlocksJson) ?>"></div>
                        <p class="cb-empty-hint text-muted small mb-0" id="cb-empty-hint">No content blocks yet. Use the buttons above to add some.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/media-picker.js"></script>
<script src="/assets/js/course-editor.js"></script>
<script>
    CourseEditor.init({
        blocksContainer: '#cb-blocks',
        emptyHint: '#cb-empty-hint',
        outlineList: '#cb-outline-list',
        outlineEmpty: '#cb-outline-empty',
        outlineListOffcanvas: '#cb-outline-list-offcanvas',
        outlineEmptyOffcanvas: '#cb-outline-empty-offcanvas',
        offcanvas: '#cbOutlineOffcanvas',
        saveButton: '#cb-save-btn',
        saveStatus: '#cb-save-status',
        saveUrl: <?= json_encode('/admin/inductions/editor.php?id=' . (int) $induction['id']) ?>,
        csrfToken: <?= json_encode(csrf_token()) ?>
    });
</script>
</body>
</html>
