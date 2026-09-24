<?php
/**
 * Messages set with flash('success'|'error', ...) before a redirect
 * (docs/core/design-system.html#alerts).
 */
foreach (['success' => 'success', 'error' => 'danger'] as $flashKey => $flashVariant): ?>
    <?php if ($flashMessage = flash($flashKey)): ?>
        <div class="alert alert-<?= $flashVariant ?> alert-dismissible fade show" role="alert">
            <?= e($flashMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
