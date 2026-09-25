<?php

declare(strict_types=1);

use App\Core\Auth\AuthService;

/** @var array<string, string> $errors */
$pageTitle = 'Add User';
$currentPage = 'users';
require __DIR__ . '/../../partials/admin-header.php';

$sendSetupEmail = old('send_setup_email', '1') === '1';
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/users/index.php">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add User</li>
                </ol>
            </nav>
            <h1 class="page-title">Add User</h1>
            <p class="page-subtitle">Inductees add their name and workplace details themselves, when they first log in.</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="post" action="/admin/users/create.php" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" class="form-control <?= error_for($errors, 'email') ? 'is-invalid' : '' ?>"
                           id="email" name="email" value="<?= old('email') ?>" autocomplete="off" required autofocus>
                    <?php if ($error = error_for($errors, 'email')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php else: ?>
                        <div class="form-text">Treated as verified, so they aren't asked to confirm it.</div>
                    <?php endif; ?>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="user_type" class="form-label required">User Type</label>
                        <select class="form-select <?= error_for($errors, 'user_type') ? 'is-invalid' : '' ?>" id="user_type" name="user_type" required>
                            <option value="inductee" <?= old('user_type') === 'inductee' ? 'selected' : '' ?>>Inductee</option>
                            <option value="admin" <?= old('user_type') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                        <?php if ($error = error_for($errors, 'user_type')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="status" class="form-label required">Status</label>
                        <select class="form-select <?= error_for($errors, 'status') ? 'is-invalid' : '' ?>" id="status" name="status" required>
                            <?php foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= old('status', 'active') === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($error = error_for($errors, 'status')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input <?= error_for($errors, 'send_setup_email') ? 'is-invalid' : '' ?>" type="checkbox"
                           id="send_setup_email" name="send_setup_email" value="1" <?= $sendSetupEmail ? 'checked' : '' ?>>
                    <label class="form-check-label" for="send_setup_email">Email the user a link to set their password</label>
                    <?php if ($error = error_for($errors, 'send_setup_email')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php else: ?>
                        <div class="form-text mt-0">
                            They choose their own password, so no one else knows it. The link lasts <?= AuthService::SETUP_LINK_DAYS ?> days.
                            Untick to set a password yourself; nothing is emailed.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row g-3 mb-3" id="password-fields" <?= $sendSetupEmail ? 'hidden' : '' ?>>
                    <div class="col-sm">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" class="form-control <?= error_for($errors, 'password') ? 'is-invalid' : '' ?>"
                               id="password" name="password" autocomplete="new-password" required>
                        <?php if ($error = error_for($errors, 'password')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php else: ?>
                            <div class="form-text">At least 8 characters.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="password_confirmation" class="form-label required">Confirm Password</label>
                        <input type="password" class="form-control <?= error_for($errors, 'password_confirmation') ? 'is-invalid' : '' ?>"
                               id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                        <?php if ($error = error_for($errors, 'password_confirmation')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="/admin/users/index.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        // The password is only set here when no setup email is sent.
        var sendSetupEmail = document.getElementById('send_setup_email');
        var passwordFields = document.getElementById('password-fields');

        sendSetupEmail.addEventListener('change', function () {
            passwordFields.hidden = sendSetupEmail.checked;
        });
    })();
</script>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
