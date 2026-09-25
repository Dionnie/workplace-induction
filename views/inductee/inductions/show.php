<?php

declare(strict_types=1);

use App\ContentBlocks\ContentBlockRenderer;

/**
 * The induction's content as slides, one at a time
 * (docs/application/content_blocks_editor.md #5): the induction's title,
 * About button and outline in a sidebar (a drawer below lg), the current
 * slide, and a Previous / Next bar fixed to the bottom of the screen. Every
 * slide is in the page; slide-viewer.js shows one and hides the rest.
 *
 * @var array<string, mixed> $induction
 * @var array<int, array<string, mixed>> $sections Section slides, each with its "lectures".
 */
$pageTitle = $induction['title'];
$currentPage = 'dashboard';
$wideContainer = true;
require __DIR__ . '/../../partials/inductee-header.php';

$state = $induction['compliance_state'];
$compliance = $induction['latest_compliance'];
$renderer = new ContentBlockRenderer();

// Every slide in reading order: a section slide, then its lecture slides.
// The eyebrow above each title says where the slide sits.
$slides = [];
foreach ($sections as $sectionIndex => $section) {
    $slides[] = ['slide' => $section, 'eyebrow' => 'Section ' . ($sectionIndex + 1)];
    foreach ($section['lectures'] ?? [] as $lecture) {
        $slides[] = ['slide' => $lecture, 'eyebrow' => (string) ($section['title'] ?? '')];
    }
}

// What finishing the induction means for this inductee: nothing once they
// are compliant, otherwise the exam or marking it complete. Shown on the
// last slide in place of Next.
ob_start();
if ($state !== 'compliant'):
    if ($induction['exam_id'] === null): ?>
        <form method="post" action="/inductee/inductions/complete.php">
            <?= csrf_field() ?>
            <input type="hidden" name="induction_id" value="<?= (int) $induction['id'] ?>">
            <button type="submit" class="btn btn-primary">Mark as Complete</button>
        </form>
    <?php else: ?>
        <a href="/inductee/exams/take.php?induction_id=<?= (int) $induction['id'] ?>" class="btn btn-primary">
            <?= $state === 'failed' ? 'Retry Exam' : 'Start Exam' ?>
        </a>
    <?php endif;
endif;
$finishAction = trim((string) ob_get_clean());
?>

<div class="<?= $slides ? 'cb-slides-layout' : 'content-canvas' ?>">
    <?php if ($slides): ?>
        <aside class="cb-slides-sidebar">
            <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="slide-outline" aria-labelledby="slide-outline-title">
                <div class="offcanvas-header">
                    <h2 class="offcanvas-title fs-6" id="slide-outline-title">Outline</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#slide-outline" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="cb-slides-induction">
                        <h1 class="cb-slides-induction-title"><?= e($induction['title']) ?></h1>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-about-open>
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>About
                        </button>
                    </div>
                    <nav class="cb-slide-outline" aria-label="Outline">
                        <div class="small text-uppercase text-muted fw-semibold mb-2 d-none d-lg-block">Outline</div>
                        <ol class="cb-outline-list list-unstyled mb-0">
                            <?php foreach ($sections as $section): ?>
                                <li class="cb-outline-section">
                                    <a href="#slide-<?= e($section['id']) ?>" class="cb-outline-link cb-outline-link-section" data-slide-link="<?= e($section['id']) ?>">
                                        <?= e($section['title']) ?>
                                    </a>
                                    <?php if (!empty($section['lectures'])): ?>
                                        <ul class="cb-outline-items list-unstyled">
                                            <?php foreach ($section['lectures'] as $lecture): ?>
                                                <li>
                                                    <a href="#slide-<?= e($lecture['id']) ?>" class="cb-outline-link" data-slide-link="<?= e($lecture['id']) ?>">
                                                        <?= e($lecture['title']) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                </div>
            </div>
        </aside>
    <?php endif; ?>

    <div class="cb-slides-stage">
        <?php // With slides, the title and description are in the sidebar and the About modal, so the slide gets the screen. ?>
        <?php if (!$slides): ?>
            <div class="page-header">
                <div>
                    <nav aria-label="Breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/inductee/index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= e($induction['title']) ?></li>
                        </ol>
                    </nav>
                    <h1 class="page-title"><?= e($induction['title']) ?></h1>
                    <?php if (!empty($induction['description'])): ?>
                        <p class="page-subtitle"><?= e($induction['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($state === 'compliant' && $compliance): ?>
            <div class="alert alert-success">
                You are currently compliant for this induction. Expires <?= e($compliance['expiry_date']) ?>.
                <a href="/inductee/certificates/show.php?id=<?= (int) $compliance['id'] ?>" class="alert-link">View certificate</a>.
            </div>
        <?php elseif ($state === 'expired'): ?>
            <div class="alert alert-danger">Your compliance for this induction has expired. Go through the slides again to renew.</div>
        <?php elseif ($state === 'failed'): ?>
            <div class="alert alert-danger">You did not pass the exam on your last attempt. Review the slides and try again.</div>
        <?php endif; ?>

        <?php if (!$slides): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-0">This induction has no content yet.</p>
                </div>
            </div>
            <?php if ($finishAction !== ''): ?>
                <div class="mt-4"><?= $finishAction ?></div>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach ($slides as $index => $item): ?>
                <?php $slide = $item['slide']; ?>
                <?php // No id="slide-…": it would match the #slide-… URL hash, and the browser would scroll to it on load. ?>
                <article class="cb-slide card border-0 shadow-sm" data-slide="<?= e($slide['id']) ?>"
                         aria-labelledby="slide-title-<?= e($slide['id']) ?>"<?= $index > 0 ? ' hidden' : '' ?>>
                    <div class="cb-slide-band" aria-hidden="true"></div>
                    <div class="card-body">
                        <header class="cb-slide-header">
                            <p class="cb-slide-eyebrow"><?= e($item['eyebrow']) ?></p>
                            <h2 class="cb-slide-title cb-heading-2" id="slide-title-<?= e($slide['id']) ?>" tabindex="-1"><?= e($slide['title']) ?></h2>
                        </header>
                        <?= $renderer->renderAll($slide['blocks'] ?? []) ?>
                    </div>
                    <div class="cb-slide-band cb-slide-band-bottom" aria-hidden="true"></div>
                </article>
            <?php endforeach; ?>

            <nav class="cb-slide-nav" aria-label="Slides">
                <button type="button" class="btn btn-outline-secondary" data-slide-prev aria-label="Previous slide" disabled>
                    <i class="bi bi-arrow-left" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">Previous</span>
                </button>
                <div class="cb-slide-nav-status">
                    <button type="button" class="btn btn-sm btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas"
                            data-bs-target="#slide-outline" aria-controls="slide-outline" aria-label="Open outline" title="Outline">
                        <i class="bi bi-list-ul" aria-hidden="true"></i>
                    </button>
                    <span class="cb-slide-counter" data-slide-counter aria-live="polite"><span class="d-none d-sm-inline">Slide </span>1 of <?= count($slides) ?></span>
                </div>
                <button type="button" class="btn btn-primary" data-slide-next aria-label="Next slide">
                    <span class="d-none d-sm-inline me-1">Next</span><i class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>
                <?php if ($finishAction !== ''): ?>
                    <div data-slide-finish hidden><?= $finishAction ?></div>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php if ($slides): ?>
    <div class="modal fade" id="induction-about" tabindex="-1" aria-labelledby="induction-about-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="induction-about-title"><?= e($induction['title']) ?></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($induction['description'])): ?>
                        <p class="mb-0"><?= nl2br(e($induction['description'])) ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-0">This induction has no description.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/slide-viewer.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
