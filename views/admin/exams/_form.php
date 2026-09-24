<?php

declare(strict_types=1);

/**
 * @var string $formAction
 * @var string $submitLabel
 * @var array<string, string> $errors
 * @var array<string, mixed> $values
 * @var string $examBlocksJson
 */
?>
<form method="post" action="<?= e($formAction) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control <?= error_for($errors, 'title') ? 'is-invalid' : '' ?>"
               id="title" name="title" value="<?= e((string) ($values['title'] ?? '')) ?>" required autofocus>
        <?php if ($error = error_for($errors, 'title')): ?>
            <div class="invalid-feedback"><?= e($error) ?></div>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm">
            <label for="pass_percentage" class="form-label">Pass Percentage</label>
            <input type="number" min="1" max="100" class="form-control <?= error_for($errors, 'pass_percentage') ? 'is-invalid' : '' ?>"
                   id="pass_percentage" name="pass_percentage" value="<?= e((string) ($values['pass_percentage'] ?? '')) ?>" required>
            <?php if ($error = error_for($errors, 'pass_percentage')): ?>
                <div class="invalid-feedback"><?= e($error) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-sm">
            <label for="status" class="form-label">Status</label>
            <select class="form-select <?= error_for($errors, 'status') ? 'is-invalid' : '' ?>" id="status" name="status" required>
                <?php foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= ($values['status'] ?? 'active') === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($error = error_for($errors, 'status')): ?>
                <div class="invalid-feedback"><?= e($error) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description <span class="text-muted small">(optional)</span></label>
        <textarea class="form-control" id="description" name="description" rows="2"><?= e((string) ($values['description'] ?? '')) ?></textarea>
    </div>

    <fieldset class="mb-3">
        <legend class="form-label fs-6 mb-2">Questions</legend>

        <div id="exam-questions" data-initial="<?= e($examBlocksJson) ?>"></div>

        <?php if ($examBlocksJson === '[]'): ?>
            <p class="text-muted small mb-2" id="no-questions-hint">No questions yet.</p>
        <?php endif; ?>

        <button type="button" class="btn btn-sm btn-outline-primary" data-add-question>
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Question
        </button>

        <?php if ($error = error_for($errors, 'exam_blocks')): ?>
            <div class="invalid-feedback d-block"><?= e($error) ?></div>
        <?php endif; ?>

        <input type="hidden" id="exam_blocks_input" name="exam_blocks" value="">
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
        <a href="/admin/exams/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<script src="/assets/js/exam-blocks.js"></script>
