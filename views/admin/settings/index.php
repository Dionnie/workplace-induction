<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $settings
 * @var array<string, string> $errors
 */
$pageTitle = 'Settings';
$currentPage = 'settings';
require __DIR__ . '/../../partials/admin-header.php';
?>

<h1 class="fs-4 fw-semibold mb-3">Email &amp; Notification Settings</h1>

<div class="card shadow-sm mb-4" style="max-width: 640px;">
    <div class="card-body p-4">
        <form method="post" action="/admin/settings/index.php" novalidate>
            <?= csrf_field() ?>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label for="sender_name" class="form-label">Sender Name</label>
                    <input type="text" class="form-control <?= error_for($errors, 'sender_name') ? 'is-invalid' : '' ?>"
                           id="sender_name" name="sender_name" value="<?= old('sender_name', (string) $settings['sender_name']) ?>" required autofocus>
                    <?php if ($error = error_for($errors, 'sender_name')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <label for="sender_email" class="form-label">Sender Email</label>
                    <input type="email" class="form-control <?= error_for($errors, 'sender_email') ? 'is-invalid' : '' ?>"
                           id="sender_email" name="sender_email" value="<?= old('sender_email', (string) ($settings['sender_email'] ?? '')) ?>">
                    <?php if ($error = error_for($errors, 'sender_email')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php else: ?>
                        <div class="form-text">Leave blank to use the system default.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="cc" class="form-label">CC</label>
                <input type="text" class="form-control <?= error_for($errors, 'cc') ? 'is-invalid' : '' ?>"
                       id="cc" name="cc" value="<?= old('cc', (string) ($settings['cc'] ?? '')) ?>" placeholder="e.g. hse@example.com, manager@example.com">
                <?php if ($error = error_for($errors, 'cc')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php else: ?>
                    <div class="form-text">Other concerned people to copy on notification emails. Comma-separated.</div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="bcc" class="form-label">BCC</label>
                <input type="text" class="form-control <?= error_for($errors, 'bcc') ? 'is-invalid' : '' ?>"
                       id="bcc" name="bcc" value="<?= old('bcc', (string) ($settings['bcc'] ?? '')) ?>">
                <?php if ($error = error_for($errors, 'bcc')): ?>
                    <div class="invalid-feedback"><?= e($error) ?></div>
                <?php endif; ?>
            </div>

            <hr class="my-4">
            <h2 class="fs-6 fw-semibold mb-3">Notifications</h2>

            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" id="notify_admin_on_completion" name="notify_admin_on_completion" value="1"
                       <?= old('notify_admin_on_completion', $settings['notify_admin_on_completion'] ? '1' : '') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="notify_admin_on_completion">
                    Notify administrators when an inductee completes an induction
                </label>
            </div>

            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" id="notify_inductee_on_completion" name="notify_inductee_on_completion" value="1"
                       <?= old('notify_inductee_on_completion', $settings['notify_inductee_on_completion'] ? '1' : '') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="notify_inductee_on_completion">
                    Notify the inductee when they complete an induction
                </label>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="notify_inductee_on_expiry" name="notify_inductee_on_expiry" value="1"
                       <?= old('notify_inductee_on_expiry', $settings['notify_inductee_on_expiry'] ? '1' : '') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="notify_inductee_on_expiry">
                    Notify inductees when their compliance is about to expire
                </label>
            </div>

            <div class="mb-4" style="max-width: 220px;">
                <label for="expiry_reminder_days" class="form-label">Remind Before Expiry</label>
                <div class="input-group">
                    <input type="number" min="1" class="form-control <?= error_for($errors, 'expiry_reminder_days') ? 'is-invalid' : '' ?>"
                           id="expiry_reminder_days" name="expiry_reminder_days"
                           value="<?= old('expiry_reminder_days', (string) $settings['expiry_reminder_days']) ?>">
                    <span class="input-group-text">days</span>
                    <?php if ($error = error_for($errors, 'expiry_reminder_days')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

<div class="card shadow-sm" style="max-width: 640px;">
    <div class="card-body p-4">
        <h2 class="fs-6 fw-semibold">Expiry Reminders</h2>
        <p class="text-muted small mb-3">
            Sends the expiry reminder email to every inductee whose compliance is within the configured window and
            has not already been reminded. Schedule <code>cron/send-expiry-reminders.php</code> to run daily, or
            trigger it manually below.
        </p>
        <form method="post" action="/admin/settings/send-reminders.php" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm">Send Reminders Now</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
