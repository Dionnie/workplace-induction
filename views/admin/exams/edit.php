<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $exam
 * @var array<string, string> $errors
 */
$pageTitle = 'Edit Exam';
$currentPage = 'exams';
require __DIR__ . '/../../partials/admin-header.php';

$values = [
    'title' => old_raw('title', (string) $exam['title']),
    'description' => old_raw('description', (string) $exam['description']),
    'pass_percentage' => old_raw('pass_percentage', (string) $exam['pass_percentage']),
    'status' => old_raw('status', (string) $exam['status']),
];
$formAction = '/admin/exams/edit.php?id=' . (int) $exam['id'];
$submitLabel = 'Save Changes';
$questionCount = count(json_decode((string) $exam['exam_blocks'], true) ?: []);
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/exams/index.php">Exams</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Exam</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit Exam</h1>
            <p class="page-subtitle"><?= $questionCount ?> <?= $questionCount === 1 ? 'question' : 'questions' ?></p>
        </div>
        <a href="/admin/exams/editor.php?id=<?= (int) $exam['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Edit Exam Blocks
        </a>
    </div>

    <?php if ($questionCount === 0): ?>
        <div class="alert alert-info">
            This exam has no questions yet, so it can't be made active.
            <a href="/admin/exams/editor.php?id=<?= (int) $exam['id'] ?>" class="alert-link">Add questions in the Exam Blocks editor</a>.
        </div>
    <?php endif; ?>

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
                        <div class="fw-semibold">Delete this exam</div>
                        <div class="text-muted small">
                            Permanently removes the exam and its questions. If an induction uses it or inductees have
                            attempted it, it must be detached and its exam attempts deleted too (cascade delete).
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm text-nowrap flex-shrink-0" data-bs-toggle="modal" data-bs-target="#deleteExamModal">
                        Delete Exam
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteExamModal" tabindex="-1" aria-labelledby="deleteExamModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/admin/exams/delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $exam['id'] ?>">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="deleteExamModalTitle">Delete Exam</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong><?= e($exam['title']) ?></strong>? This cannot be undone.</p>
                    <div class="alert alert-warning small mb-3">
                        If this exam is used by inductions or has exam attempts, deletion is blocked unless cascade
                        delete is enabled below.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cascade" value="1" id="deleteExamCascade">
                        <label class="form-check-label" for="deleteExamCascade">
                            Also detach this exam from any induction using it and permanently delete its exam attempts
                            (cascade delete)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
