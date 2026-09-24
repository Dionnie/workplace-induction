<?php

declare(strict_types=1);

/** @var array<string, string> $errors */
$pageTitle = 'Add User';
$currentPage = 'users';
require __DIR__ . '/../../partials/admin-header.php';
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
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="post" action="/admin/users/create.php" novalidate>
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control <?= error_for($errors, 'first_name') ? 'is-invalid' : '' ?>"
                               id="first_name" name="first_name" value="<?= old('first_name') ?>" required autofocus>
                        <?php if ($error = error_for($errors, 'first_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control <?= error_for($errors, 'last_name') ? 'is-invalid' : '' ?>"
                               id="last_name" name="last_name" value="<?= old('last_name') ?>" required>
                        <?php if ($error = error_for($errors, 'last_name')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?= error_for($errors, 'email') ? 'is-invalid' : '' ?>"
                           id="email" name="email" value="<?= old('email') ?>" required>
                    <?php if ($error = error_for($errors, 'email')): ?>
                        <div class="invalid-feedback"><?= e($error) ?></div>
                    <?php endif; ?>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="user_type" class="form-label">User Type</label>
                        <select class="form-select <?= error_for($errors, 'user_type') ? 'is-invalid' : '' ?>" id="user_type" name="user_type" required>
                            <option value="inductee" <?= old('user_type') === 'inductee' ? 'selected' : '' ?>>Inductee</option>
                            <option value="admin" <?= old('user_type') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                        </select>
                        <?php if ($error = error_for($errors, 'user_type')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="status" class="form-label">Status</label>
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

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control <?= error_for($errors, 'password') ? 'is-invalid' : '' ?>"
                               id="password" name="password" autocomplete="new-password" required>
                        <?php if ($error = error_for($errors, 'password')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php else: ?>
                            <div class="form-text">At least 8 characters.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
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

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
