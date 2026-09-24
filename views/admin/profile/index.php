<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $user
 * @var array<string, string> $errors
 */
$pageTitle = 'My Profile';
$currentPage = 'profile';
require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-narrow">
    <div class="page-header">
        <h1 class="page-title">My Profile</h1>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body p-4">
            <h2 class="fs-6 mb-1">Profile</h2>
            <p class="text-muted small mb-3">Your name as shown throughout the application.</p>

            <form method="post" action="/admin/profile/update-profile.php" novalidate>
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control <?= error_for($errors, 'first_name') ? 'is-invalid' : '' ?>"
                               id="first_name" name="first_name" value="<?= old('first_name', (string) $user['first_name']) ?>" required autofocus>
                        <?php if ($error = error_for($errors, 'first_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control <?= error_for($errors, 'last_name') ? 'is-invalid' : '' ?>"
                               id="last_name" name="last_name" value="<?= old('last_name', (string) $user['last_name']) ?>" required>
                        <?php if ($error = error_for($errors, 'last_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="fs-6 mb-1">Account</h2>
            <p class="text-muted small mb-3">Your login email and password.</p>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="<?= e($user['email']) ?>" disabled>
            </div>

            <form method="post" action="/admin/profile/update-password.php" novalidate>
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control <?= error_for($errors, 'password') ? 'is-invalid' : '' ?>"
                               id="password" name="password" autocomplete="new-password">
                        <?php if ($error = error_for($errors, 'password')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php else: ?>
                            <div class="form-text">Leave blank to keep the current password.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control <?= error_for($errors, 'password_confirmation') ? 'is-invalid' : '' ?>"
                               id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        <?php if ($error = error_for($errors, 'password_confirmation')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
