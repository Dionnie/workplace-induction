<?php

declare(strict_types=1);

/**
 * @var string $formAction
 * @var string $submitLabel
 * @var array<string, string> $errors
 * @var array<string, mixed> $values
 * @var string $contentBlocksJson
 * @var array<int, array<string, mixed>> $exams
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
            <label for="code" class="form-label">Code</label>
            <input type="text" class="form-control <?= error_for($errors, 'code') ? 'is-invalid' : '' ?>"
                   id="code" name="code" value="<?= e((string) ($values['code'] ?? '')) ?>" required
                   placeholder="e.g. SITE-SAFETY-01">
            <?php if ($error = error_for($errors, 'code')): ?>
                <div class="invalid-feedback"><?= e($error) ?></div>
            <?php else: ?>
                <div class="form-text">Letters, numbers, and dashes only.</div>
            <?php endif; ?>
        </div>
        <div class="col-sm">
            <label for="validity_months" class="form-label">Validity (months)</label>
            <input type="number" min="1" class="form-control <?= error_for($errors, 'validity_months') ? 'is-invalid' : '' ?>"
                   id="validity_months" name="validity_months" value="<?= e((string) ($values['validity_months'] ?? '')) ?>" required>
            <?php if ($error = error_for($errors, 'validity_months')): ?>
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

    <div class="mb-3">
        <label for="exam_id" class="form-label">Exam <span class="text-muted small">(optional)</span></label>
        <select class="form-select <?= error_for($errors, 'exam_id') ? 'is-invalid' : '' ?>" id="exam_id" name="exam_id">
            <option value="">No exam &mdash; content only</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?= (int) $exam['id'] ?>" <?= (string) ($values['exam_id'] ?? '') === (string) $exam['id'] ? 'selected' : '' ?>>
                    <?= e($exam['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($error = error_for($errors, 'exam_id')): ?>
            <div class="invalid-feedback"><?= e($error) ?></div>
        <?php else: ?>
            <div class="form-text">Only active exams are listed. Leave blank if this induction has no assessment.</div>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
        <a href="/admin/inductions/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
