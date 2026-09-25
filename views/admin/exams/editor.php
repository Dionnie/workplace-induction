<?php

declare(strict_types=1);

/**
 * Exam Blocks Studio: the Content Blocks Studio's top bar, over the exam
 * page's centred column of question cards (views/inductee/exams/take.php).
 * See docs/application/exams.md.
 *
 * @var array<string, mixed> $exam
 * @var string $initialBlocksJson
 */
$documentTitle = 'Edit Exam Blocks · ' . $exam['title'] . ' · ' . site_settings()['company_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/../../partials/head.php'; ?>
</head>
<body>

<header class="cb-studio-header border-bottom bg-white sticky-top">
    <div class="cb-studio-header-inner container-fluid px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="cb-studio-header-info d-flex align-items-center gap-3 overflow-hidden">
            <a href="/admin/exams/edit.php?id=<?= (int) $exam['id'] ?>" class="cb-back-link btn btn-outline-secondary flex-shrink-0">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Exam Details
            </a>
            <span class="fw-semibold text-truncate"><?= e($exam['title']) ?></span>
            <?php if ($exam['status'] !== 'active'): ?>
                <?= status_badge((string) $exam['status']) ?>
            <?php endif; ?>
            <span id="cb-save-status" class="cb-save-status badge text-bg-success">Saved</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="cb-outline-toggle btn btn-outline-secondary" data-bs-toggle="offcanvas" data-bs-target="#cbOutlineOffcanvas" aria-controls="cbOutlineOffcanvas">
                <i class="bi bi-list-ol me-1" aria-hidden="true"></i>Exam Outline
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
        <h2 class="offcanvas-title fs-6" id="cbOutlineOffcanvasLabel">Exam Outline</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ol class="cb-outline-list list-unstyled mb-0" id="cb-outline-list-offcanvas"></ol>
        <p class="cb-outline-empty mb-0" id="cb-outline-empty-offcanvas">Add a question to start building the exam.</p>
    </div>
</div>

<main class="cb-studio-main py-5">
    <div class="cb-studio-container container-fluid px-4">
        <div class="page-narrow mx-auto">
            <?php require __DIR__ . '/../../partials/flash.php'; ?>
        </div>

        <div class="cb-content-area">
            <!-- Floating outline rail beside the column on wide screens; see .cb-outline-sidebar-rail in app.css. -->
            <aside class="cb-outline-sidebar-rail">
                <nav class="cb-outline-sidebar card border-0 shadow-sm p-3" aria-label="Exam outline">
                    <div class="cb-outline-heading small text-uppercase text-muted fw-semibold mb-2">Exam Outline</div>
                    <ol class="cb-outline-list list-unstyled mb-0" id="cb-outline-list"></ol>
                    <p class="cb-outline-empty mb-0" id="cb-outline-empty">Add a question to start building the exam.</p>
                </nav>
            </aside>

            <div class="page-narrow mx-auto">
                <div id="cb-blocks" class="cb-blocks-container" data-initial="<?= e($initialBlocksJson) ?>"></div>
                <p class="cb-empty-hint text-muted small text-center mb-0" id="cb-empty-hint">No questions yet. Add one with the buttons above.</p>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/media-picker.js"></script>
<script src="/assets/js/exam-editor.js"></script>
<script>
    ExamEditor.init({
        blocksContainer: '#cb-blocks',
        emptyHint: '#cb-empty-hint',
        outlineList: '#cb-outline-list',
        outlineEmpty: '#cb-outline-empty',
        outlineListOffcanvas: '#cb-outline-list-offcanvas',
        outlineEmptyOffcanvas: '#cb-outline-empty-offcanvas',
        offcanvas: '#cbOutlineOffcanvas',
        saveButton: '#cb-save-btn',
        saveStatus: '#cb-save-status',
        saveUrl: <?= json_encode('/admin/exams/editor.php?id=' . (int) $exam['id']) ?>,
        csrfToken: <?= json_encode(csrf_token()) ?>
    });
</script>
</body>
</html>
