<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $user
 * @var array<string, string> $errors
 */
$pageTitle = 'Edit User';
$currentPage = 'users';
require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-narrow">
    <div class="page-header">
        <div>
            <nav aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/users/index.php">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                </ol>
            </nav>
            <h1 class="page-title">Edit User</h1>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="post" action="/admin/users/edit.php?id=<?= (int) $user['id'] ?>" novalidate>
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

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?= error_for($errors, 'email') ? 'is-invalid' : '' ?>"
                           id="email" name="email" value="<?= old('email', (string) $user['email']) ?>" required>
                    <?php if ($error = error_for($errors, 'email')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php endif; ?>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="user_type" class="form-label">User Type</label>
                        <input type="text" class="form-control" id="user_type"
                               value="<?= $user['user_type'] === 'admin' ? 'Administrator' : 'Inductee' ?>" disabled>
                    </div>
                    <div class="col-sm">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select <?= error_for($errors, 'status') ? 'is-invalid' : '' ?>" id="status" name="status" required>
                            <?php foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= old('status', (string) $user['status']) === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($error = error_for($errors, 'status')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="password" class="form-label">New Password <span class="text-muted small">(optional)</span></label>
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
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="/admin/users/index.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
