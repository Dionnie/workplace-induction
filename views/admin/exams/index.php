<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $exams
 * @var string $search
 * @var string $status
 */
$pageTitle = 'Exams';
$currentPage = 'exams';
require __DIR__ . '/../../partials/admin-header.php';

$statusBadge = [
    'active' => 'success',
    'inactive' => 'secondary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="fs-4 fw-semibold mb-0">Exams</h1>
    <a href="/admin/exams/create.php" class="btn btn-primary btn-sm">Add Exam</a>
</div>

<form method="get" action="/admin/exams/index.php" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title"
               value="<?= e($search) ?>">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Statuses</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white">
        <thead>
            <tr>
                <th>Title</th>
                <th>Questions</th>
                <th>Pass %</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($exams)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No exams found.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($exams as $exam): ?>
                <?php $questionCount = count(json_decode((string) $exam['exam_blocks'], true) ?: []); ?>
                <tr>
                    <td><?= e($exam['title']) ?></td>
                    <td><?= $questionCount ?></td>
                    <td><?= (int) $exam['pass_percentage'] ?>%</td>
                    <td>
                        <span class="badge text-bg-<?= $statusBadge[$exam['status']] ?? 'secondary' ?>">
                            <?= e(ucfirst($exam['status'])) ?>
                        </span>
                    </td>
                    <td><?= e(date('Y-m-d', strtotime((string) $exam['created_at']))) ?></td>
                    <td class="text-end">
                        <a href="/admin/exams/edit.php?id=<?= (int) $exam['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteExamModal"
                                data-id="<?= (int) $exam['id'] ?>"
                                data-name="<?= e($exam['title']) ?>">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="deleteExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/admin/exams/delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteExamId" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteExamName"></strong>? This cannot be undone.</p>
                    <div class="alert alert-warning small mb-3">
                        As an administrator, you can permanently delete this exam even if it is used by inductions
                        or has exam attempts. If related data exists, deletion is blocked unless cascade delete is
                        enabled below.
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('deleteExamModal').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    document.getElementById('deleteExamId').value = button.getAttribute('data-id');
    document.getElementById('deleteExamName').textContent = button.getAttribute('data-name');
});
</script>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
