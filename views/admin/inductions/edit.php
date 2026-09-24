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
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
