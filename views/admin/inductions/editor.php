<?php

declare(strict_types=1);

/**
 * Content Blocks Studio: the induction's content as slides
 * (docs/application/content_blocks_editor.md #6). The outline sidebar lists
 * the Section and Lecture slides; the stage shows one slide at a time, the
 * same way the inductee page does. course-editor.js renders the outline and
 * the stage from $initialSlidesJson.
 *
 * @var array<string, mixed> $induction
 * @var string $initialSlidesJson
 */
$documentTitle = 'Edit Content · ' . $induction['title'] . ' · ' . site_settings()['company_name'];
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
            <a href="/admin/inductions/edit.php?id=<?= (int) $induction['id'] ?>" class="cb-back-link btn btn-outline-secondary flex-shrink-0">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Induction Details
            </a>
            <span class="cb-induction-title fw-semibold text-truncate"><?= e($induction['title']) ?></span>
            <span id="cb-save-status" class="cb-save-status badge text-bg-success">Saved</span>
        </div>
        <button type="button" id="cb-save-btn" class="cb-save-button btn btn-primary">
            <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Save Changes
        </button>
    </div>
</header>

<main class="cb-studio-main py-4">
    <div class="container-fluid px-3 px-lg-4">
        <div class="cb-slides-layout">
            <aside class="cb-slides-sidebar">
                <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="slide-outline" aria-labelledby="slide-outline-title">
                    <div class="offcanvas-header">
                        <h2 class="offcanvas-title fs-6" id="slide-outline-title">Outline</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#slide-outline" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <nav class="cb-slide-outline" aria-label="Outline">
                            <div class="small text-uppercase text-muted fw-semibold mb-2 d-none d-lg-block">Outline</div>
                            <div class="d-grid gap-2 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-add-slide="section">
                                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Section
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-add-slide="lecture" id="cb-add-lecture">
                                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Lecture
                                </button>
                            </div>
                            <ol class="cb-outline-list list-unstyled mb-0" id="cb-outline-list"></ol>
                            <p class="cb-outline-empty mb-0" id="cb-outline-empty">No slides yet.</p>
                        </nav>
                    </div>
                </div>
            </aside>

            <div class="cb-slides-stage">
                <?php require __DIR__ . '/../../partials/flash.php'; ?>

                <div id="cb-slide-stage" data-initial="<?= e($initialSlidesJson) ?>"></div>

                <nav class="cb-slide-nav" id="cb-slide-nav" aria-label="Slides" hidden>
                    <button type="button" class="btn btn-outline-secondary" data-slide-prev aria-label="Previous slide">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">Previous</span>
                    </button>
                    <div class="cb-slide-nav-status">
                        <button type="button" class="btn btn-sm btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas"
                                data-bs-target="#slide-outline" aria-controls="slide-outline" aria-label="Open outline" title="Outline">
                            <i class="bi bi-list-ul" aria-hidden="true"></i>
                        </button>
                        <span class="cb-slide-counter" data-slide-counter aria-live="polite"></span>
                    </div>
                    <button type="button" class="btn btn-primary" data-slide-next aria-label="Next slide">
                        <span class="d-none d-sm-inline me-1">Next</span><i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/media-picker.js"></script>
<script src="/assets/js/course-editor.js"></script>
<script>
    CourseEditor.init({
        stage: '#cb-slide-stage',
        outline: '#slide-outline',
        outlineList: '#cb-outline-list',
        outlineEmpty: '#cb-outline-empty',
        addLectureButton: '#cb-add-lecture',
        nav: '#cb-slide-nav',
        saveButton: '#cb-save-btn',
        saveStatus: '#cb-save-status',
        saveUrl: <?= json_encode('/admin/inductions/editor.php?id=' . (int) $induction['id']) ?>,
        csrfToken: <?= json_encode(csrf_token()) ?>
    });
</script>
</body>
</html>
