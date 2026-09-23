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

<div class="d-flex justify-content-between align-items-center mb-3" style="max-width: 720px;">
    <h1 class="fs-4 fw-semibold mb-0">Edit Induction</h1>
    <a href="/admin/inductions/editor.php?id=<?= (int) $induction['id'] ?>" class="btn btn-primary btn-sm">
        Edit Content Blocks
    </a>
</div>

<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body p-4">
        <?php require __DIR__ . '/_form.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
