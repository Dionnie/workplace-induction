<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $induction
 * @var array<string, string> $errors
 * @var array<int, array<string, mixed>> $exams
 */
$pageTitle = 'Edit Induction';
$currentPage = 'inductions';
require __DIR__ . '/../../partials/admin-header.php';

$values = [
    'title' => old_raw('title', (string) $induction['title']),
    'code' => old_raw('code', (string) $induction['code']),
    'description' => old_raw('description', (string) $induction['description']),
    'exam_id' => old_raw('exam_id', (string) ($induction['exam_id'] ?? '')),
    'validity_months' => old_raw('validity_months', (string) $induction['validity_months']),
    'status' => old_raw('status', (string) $induction['status']),
];
$formAction = '/admin/inductions/edit.php?id=' . (int) $induction['id'];
$submitLabel = 'Save Changes';
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/inductions/index.php">Inductions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Induction</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Induction</h1>
        </div>
        <a href="/admin/inductions/editor.php?id=<?= (int) $induction['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit Content Blocks
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <?php require __DIR__ . '/_form.php'; ?>
        </div>
    </div>

    <div class="card border-danger-subtle shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="fs-6 text-danger mb-1">Danger Zone</h2>
            <p class="text-muted small mb-2">These actions can't be undone.</p>
            <div class="list-group list-group-flush">
                <div class="list-group-item px-0 py-3 d-flex flex-wrap flex-sm-nowrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">Delete this induction</div>
                        <div class="text-muted small">
                            Permanently removes the induction and its content blocks. If inductees have exam attempts or
                            compliance records for it, those must be deleted too (cascade delete).
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm text-nowrap flex-shrink-0" data-bs-toggle="modal" data-bs-target="#deleteInductionModal">
                        Delete Induction
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteInductionModal" tabindex="-1" aria-labelledby="deleteInductionModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/admin/inductions/delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $induction['id'] ?>">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="deleteInductionModalTitle">Delete Induction</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong><?= e($induction['title']) ?></strong>? This cannot be undone.</p>
                    <div class="alert alert-warning small mb-3">
                        If this induction has exam attempts or compliance records, deletion is blocked unless cascade
                        delete is enabled below.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cascade" value="1" id="deleteInductionCascade">
                        <label class="form-check-label" for="deleteInductionCascade">
                            Also permanently delete this induction's exam attempts and compliance records (cascade delete)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Induction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
