<?php

declare(strict_types=1);

use App\ContentBlocks\ContentBlockRenderer;

/**
 * @var array<string, mixed> $induction
 * @var array<int, array<string, mixed>> $blocks
 * @var array<int, array{id: string, title: string, items: array<int, array{id: string, type: string, label: string}>}> $outline
 */
$pageTitle = $induction['title'];
$currentPage = 'dashboard';
$wideContainer = true;
require __DIR__ . '/../../partials/inductee-header.php';

$state = $induction['compliance_state'];
$compliance = $induction['latest_compliance'];

// Rendered once, then reused in both the floating sidebar rail (wide
// viewports) and the offcanvas drawer (everywhere else) — see
// .cb-outline-sidebar-rail in app.css for why both exist.
$outlineItemsHtml = '';
if (!empty($outline)) {
    ob_start();
    foreach ($outline as $section): ?>
        <li class="cb-outline-section">
            <a href="#block-<?= e($section['id']) ?>" class="cb-outline-link cb-outline-link-section" data-teleport="block-<?= e($section['id']) ?>">
                <?= e($section['title']) ?>
            </a>
            <?php if (!empty($section['items'])): ?>
                <ul class="cb-outline-items list-unstyled">
                    <?php foreach ($section['items'] as $item): ?>
                        <li>
                            <a href="#block-<?= e($item['id']) ?>" class="cb-outline-link" data-teleport="block-<?= e($item['id']) ?>">
                                <?= e($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
    <?php endforeach;
    $outlineItemsHtml = ob_get_clean();
}
?>

<!-- Header and state alert share the canvas width, so they line up with the content card below. -->
<div class="content-canvas">
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
        <?php if (!empty($outline)): ?>
            <button type="button" class="cb-outline-toggle btn btn-outline-secondary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#cbOutlineOffcanvas" aria-controls="cbOutlineOffcanvas">
                <i class="bi bi-list-ul me-1" aria-hidden="true"></i>Course Outline
            </button>
        <?php endif; ?>
    </div>

    <?php if ($state === 'compliant' && $compliance): ?>
        <div class="alert alert-success">
            You are currently compliant for this induction. Expires <?= e($compliance['expiry_date']) ?>.
            <a href="/inductee/certificates/show.php?id=<?= (int) $compliance['id'] ?>" class="alert-link">View certificate</a>.
        </div>
    <?php elseif ($state === 'expired'): ?>
        <div class="alert alert-danger">Your compliance for this induction has expired. Complete it again below to renew.</div>
    <?php elseif ($state === 'failed'): ?>
        <div class="alert alert-danger">You did not pass the exam on your last attempt. Review the content below and try again.</div>
    <?php endif; ?>
</div>

<?php if (!empty($outline)): ?>
    <div class="offcanvas offcanvas-start" tabindex="-1" id="cbOutlineOffcanvas" aria-labelledby="cbOutlineOffcanvasLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title fs-6" id="cbOutlineOffcanvasLabel">Course Outline</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="cb-outline-list list-unstyled mb-0"><?= $outlineItemsHtml ?></ul>
        </div>
    </div>
<?php endif; ?>

<!--
  .cb-content-area is placed here, after the title/description/toggle/state
  alert — all variable-height content that differs per induction — so the
  sidebar rail inside it (position:absolute, top:0 relative to this
  wrapper) always starts level with the canvas below, never overlapping
  whatever happens to precede it on a given page.
-->
<div class="cb-content-area">
    <?php if (!empty($outline)): ?>
        <aside class="cb-outline-sidebar-rail">
            <nav class="cb-outline-sidebar card border-0 shadow-sm p-3" aria-label="Course outline">
                <div class="cb-outline-heading small text-uppercase text-muted fw-semibold mb-2">Course Outline</div>
                <ul class="cb-outline-list list-unstyled mb-0"><?= $outlineItemsHtml ?></ul>
            </nav>
        </aside>
    <?php endif; ?>

    <div class="content-canvas">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (empty($blocks)): ?>
                    <p class="text-muted">This induction has no content yet.</p>
                <?php else: ?>
                    <?= (new ContentBlockRenderer())->renderAll($blocks) ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($state !== 'compliant'): ?>
            <div class="content-block-inner mt-4">
                <?php if ($induction['exam_id'] === null): ?>
                    <form method="post" action="/inductee/inductions/complete.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="induction_id" value="<?= (int) $induction['id'] ?>">
                        <button type="submit" class="btn btn-primary">Mark as Complete</button>
                    </form>
                <?php else: ?>
                    <a href="/inductee/exams/take.php?induction_id=<?= (int) $induction['id'] ?>" class="btn btn-primary">
                        <?= $state === 'failed' ? 'Retry Exam' : 'Start Exam' ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($outline)): ?>
    <script src="/assets/js/course-outline.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/inductee-footer.php'; ?>
