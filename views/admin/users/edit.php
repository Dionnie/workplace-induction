<?php

declare(strict_types=1);

use App\Core\Auth\AuthService;

/**
 * @var array<string, mixed> $user
 * @var array<string, string> $errors
 */
$pageTitle = 'Edit User';
$currentPage = 'users';
require __DIR__ . '/../../partials/admin-header.php';

$isOwnAccount = (int) $user['id'] === (int) ($authUser['id'] ?? 0);
$isInductee = $user['user_type'] === 'inductee';
$userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: (string) $user['email'];
$profileCompleted = old('profile_completed', $user['profile_completed'] ? '1' : '') === '1';
$emailVerified = $user['email_verified_at'] !== null;
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
            <p class="page-subtitle"><?= e($userName) ?></p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="post" action="/admin/users/edit.php?id=<?= (int) $user['id'] ?>" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?= e((string) $user['email']) ?>" disabled>
                    <div class="form-text">An account's email can't be changed.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm">
                        <label for="user_type" class="form-label">User Type</label>
                        <input type="text" class="form-control" id="user_type"
                               value="<?= $user['user_type'] === 'admin' ? 'Administrator' : 'Inductee' ?>" disabled>
                    </div>
                    <div class="col-sm">
                        <label for="status" class="form-label required">Status</label>
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

                <?php if ($isInductee): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input <?= error_for($errors, 'profile_completed') ? 'is-invalid' : '' ?>" type="checkbox"
                               id="profile_completed" name="profile_completed" value="1" <?= $profileCompleted ? 'checked' : '' ?>>
                        <label class="form-check-label" for="profile_completed">Profile completed</label>
                        <?php if ($error = error_for($errors, 'profile_completed')): ?>
                            <div class="invalid-feedback"><?= e($error) ?></div>
                        <?php else: ?>
                            <div class="form-text mt-0">
                                Inductees must complete their profile before starting an induction. Untick to have them review it
                                at their next visit.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="form-check mb-3">
                    <?php if ($emailVerified): ?>
                        <input class="form-check-input" type="checkbox" id="email_verified" checked disabled>
                        <label class="form-check-label" for="email_verified">Email verified</label>
                        <div class="form-text mt-0">Verified <?= e(date('Y-m-d', strtotime((string) $user['email_verified_at']))) ?>.</div>
                    <?php else: ?>
                        <input class="form-check-input" type="checkbox" id="email_verified" name="email_verified" value="1"
                            <?= old('email_verified') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="email_verified">Email verified</label>
                        <div class="form-text mt-0">
                            Not verified yet<?= $isInductee ? ', so they can\'t log in' : '' ?>. Tick if you have confirmed the address yourself,
                            for example when the verification email didn't arrive.
                        </div>
                    <?php endif; ?>
                </div>

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
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="/admin/users/index.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="fs-6 mb-1">Password Setup Email</h2>
            <p class="text-muted small mb-3">
                Emails <?= e((string) $user['email']) ?> a link to choose their own password, valid for
                <?= AuthService::SETUP_LINK_DAYS ?> days. Their current password keeps working until they use it.
                <?= $user['status'] !== 'active' ? 'Only active accounts can be sent one.' : '' ?>
            </p>
            <form method="post" action="/admin/users/send-setup-email.php" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                <button type="submit" class="btn btn-outline-secondary btn-sm" <?= $user['status'] !== 'active' ? 'disabled' : '' ?>>Send Setup Email</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="fs-6 mb-1">Switch Account</h2>
            <p class="text-muted small mb-3">
                <?php if (!$isInductee): ?>
                    Only inductee accounts can be switched to.
                <?php elseif ($user['status'] !== 'active'): ?>
                    Only active accounts can be switched to. Set Status to Active first.
                <?php else: ?>
                    Use the site as <?= e($userName) ?>, without their password. Everything you do counts as theirs.
                    Switch Back, at the top of every page, returns you here.
                <?php endif; ?>
            </p>
            <form method="post" action="/admin/users/switch.php" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                <button type="submit" class="btn btn-outline-secondary btn-sm" <?= !$isInductee || $user['status'] !== 'active' ? 'disabled' : '' ?>>Switch Account</button>
            </form>
        </div>
    </div>

    <div class="card border-danger-subtle shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="fs-6 text-danger mb-1">Danger Zone</h2>
            <p class="text-muted small mb-2">These actions can't be undone.</p>
            <div class="list-group list-group-flush">
                <div class="list-group-item px-0 py-3 d-flex flex-wrap flex-sm-nowrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">Delete this user</div>
                        <div class="text-muted small">
                            <?php if ($isOwnAccount): ?>
                                You can't delete your own account while you are logged in to it.
                            <?php else: ?>
                                Permanently removes the account. If the user has exam attempts or compliance records,
                                those must be deleted too (cascade delete).
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm text-nowrap flex-shrink-0" data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                        <?= $isOwnAccount ? 'disabled' : '' ?>>
                        Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$isOwnAccount): ?>
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="/admin/users/delete.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="deleteUserModalTitle">Delete User</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong><?= e($userName) ?></strong>? This cannot be undone.</p>
                        <div class="alert alert-warning small mb-3">
                            If this user has exam attempts or compliance records, deletion is blocked unless cascade
                            delete is enabled below.
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="cascade" value="1" id="deleteUserCascade">
                            <label class="form-check-label" for="deleteUserCascade">
                                Also permanently delete this user's exam attempts and compliance records (cascade delete)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
