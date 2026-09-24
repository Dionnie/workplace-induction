<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>> $categories
 * @var array<int, array<string, mixed>> $items
 * @var array<string, mixed> $settings
 * @var array<int, string> $availableTypes
 * @var int|null $categoryId
 * @var string $search
 * @var string $view 'grid' or 'list'
 * @var array<string, string> $errors
 */
$pageTitle = 'Media Library';
$currentPage = 'media-library';
require __DIR__ . '/../../partials/admin-header.php';

$selectedTypes = array_filter(array_map('trim', explode(',', (string) $settings['allowed_types'])));
$acceptAttr = implode(',', array_map(fn (string $type): string => '.' . $type, $selectedTypes));

$categoryNames = array_column($categories, 'name', 'id');
$currentCategoryName = $categoryId === null ? 'None' : (string) ($categoryNames[$categoryId] ?? 'None');

// Hidden inputs that keep the current filter after a POST redirect.
$filterInputs = '<input type="hidden" name="category_id" value="' . ($categoryId !== null ? (int) $categoryId : '') . '">'
    . '<input type="hidden" name="search" value="' . e($search) . '">'
    . '<input type="hidden" name="view" value="' . e($view) . '">';
?>

<div class="page-header">
    <h1 class="page-title">Media Library</h1>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="get" action="/admin/media-library/index.php" id="media-filter-form" class="row g-2 align-items-end mb-3">
                    <div class="col-auto">
                        <label for="media-category-select" class="form-label small mb-1">Category</label>
                        <select name="category_id" id="media-category-select" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="" <?= $categoryId === null ? 'selected' : '' ?>>None</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                                    <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="media-search" class="form-label small mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="search" name="search" id="media-search" class="form-control"
                                   placeholder="Search by filename" value="<?= e($search) ?>">
                            <button type="submit" class="btn btn-outline-secondary" title="Search" aria-label="Search">
                                <i class="bi bi-search" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-auto ms-auto">
                        <span class="form-label small mb-1 d-block">View</span>
                        <div class="btn-group btn-group-sm" role="group" aria-label="View mode">
                            <button type="submit" name="view" value="grid"
                                    class="btn btn-outline-secondary <?= $view === 'grid' ? 'active' : '' ?>"
                                    title="Grid view (larger previews)" aria-label="Grid view" <?= $view === 'grid' ? 'aria-pressed="true"' : '' ?>>
                                <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>
                            </button>
                            <button type="submit" name="view" value="list"
                                    class="btn btn-outline-secondary <?= $view === 'list' ? 'active' : '' ?>"
                                    title="List view (full filenames)" aria-label="List view" <?= $view === 'list' ? 'aria-pressed="true"' : '' ?>>
                                <i class="bi bi-list-ul" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <div id="media-dropzone" class="border border-2 border-dashed rounded p-4 text-center bg-surface-subtle" role="button" tabindex="0">
                    <i class="bi bi-cloud-arrow-up fs-2 text-muted d-block mb-1" aria-hidden="true"></i>
                    <p class="mb-1 fw-semibold">Drag &amp; drop images here, or click to browse</p>
                    <p class="text-muted small mb-0">
                        New uploads are grouped into: <strong><?= e($currentCategoryName) ?></strong>
                        &middot; Max <?= (int) $settings['max_file_size_mb'] ?> MB per file
                        &middot; Allowed: <?= e(strtoupper(implode(', ', $selectedTypes))) ?>
                    </p>
                    <input type="file" id="media-file-input" class="d-none" multiple accept="<?= e($acceptAttr) ?>">
                </div>
                <div id="media-upload-alerts" class="mt-2"></div>
            </div>
        </div>

        <?php if (!empty($items)): ?>
            <form id="bulk-move-form" method="post" action="/admin/media-library/bulk-move.php"
                  class="card shadow-sm mb-3 d-flex flex-row flex-wrap align-items-center gap-2 p-2">
                <?= csrf_field() ?>
                <?= $filterInputs ?>

                <div class="form-check mb-0 ms-1">
                    <input class="form-check-input" type="checkbox" id="media-select-all">
                    <label class="form-check-label small" for="media-select-all">Select all</label>
                </div>
                <span id="media-selected-count" class="small text-muted">0 selected</span>
                <select name="target_category_id" class="form-select form-select-sm w-auto ms-auto" aria-label="Move to category">
                    <option value="">None</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" id="media-bulk-move-btn" class="btn btn-outline-secondary btn-sm" disabled>
                    <i class="bi bi-folder-symlink me-1" aria-hidden="true"></i>Move to Category
                </button>
                <button type="submit" id="media-bulk-delete-btn" class="btn btn-outline-danger btn-sm" disabled
                        formaction="/admin/media-library/bulk-delete.php"
                        onclick="return confirm('Delete the selected files? This cannot be undone.');">
                    <i class="bi bi-trash me-1" aria-hidden="true"></i>Delete Selected
                </button>
            </form>
        <?php endif; ?>

        <?php if ($view === 'list'): ?>
            <div class="card shadow-sm card-table">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th><span class="visually-hidden">Select</span></th>
                                <th><span class="visually-hidden">Preview</span></th>
                                <th>Filename</th>
                                <th>Category</th>
                                <th>Size</th>
                                <th><span class="visually-hidden">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No files found.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $itemCategoryName = $categoryNames[(int) $item['category_id']] ?? null;
                                $sizeKb = (int) $item['size'] / 1024;
                                $sizeLabel = $sizeKb >= 1024 ? round($sizeKb / 1024, 1) . ' MB' : round($sizeKb, 1) . ' KB';
                                $fileUrl = '/assets/uploads/media-library/' . $item['filename'];
                                ?>
                                <tr>
                                    <td>
                                        <input class="form-check-input media-select" type="checkbox" name="ids[]" value="<?= (int) $item['id'] ?>"
                                               form="bulk-move-form" aria-label="Select <?= e($item['original_filename']) ?>">
                                    </td>
                                    <td>
                                        <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener noreferrer" title="View full image (opens in new tab)">
                                            <img src="<?= e($fileUrl) ?>" class="media-thumb rounded" alt="<?= e($item['original_filename']) ?>" loading="lazy">
                                        </a>
                                    </td>
                                    <td class="text-break">
                                        <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener noreferrer" title="View full image (opens in new tab)">
                                            <?= e($item['original_filename']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($itemCategoryName !== null): ?>
                                            <span class="badge text-bg-light border"><?= e($itemCategoryName) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small text-nowrap"><?= e($sizeLabel) ?></td>
                                    <td class="text-end">
                                        <form method="post" action="/admin/media-library/delete.php" onsubmit="return confirm('Delete this file? This cannot be undone.');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                            <?= $filterInputs ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete <?= e($item['original_filename']) ?>">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div id="media-grid" class="row row-cols-3 row-cols-sm-4 row-cols-md-5 row-cols-xl-6 g-2">
                <?php if (empty($items)): ?>
                    <div class="col-12">
                        <p class="text-muted text-center py-5 mb-0">No files found.</p>
                    </div>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                    <?php
                    $itemCategoryName = $categoryNames[(int) $item['category_id']] ?? null;
                    $sizeKb = (int) $item['size'] / 1024;
                    $sizeLabel = $sizeKb >= 1024 ? round($sizeKb / 1024, 1) . ' MB' : round($sizeKb, 1) . ' KB';
                    $fileUrl = '/assets/uploads/media-library/' . $item['filename'];
                    ?>
                    <div class="col">
                        <div class="card h-100 media-tile">
                            <div class="position-relative">
                                <div class="media-tile-select position-absolute top-0 start-0 m-1 bg-white bg-opacity-75 rounded d-flex align-items-center justify-content-center">
                                    <input class="form-check-input media-select m-0" type="checkbox" name="ids[]" value="<?= (int) $item['id'] ?>"
                                           form="bulk-move-form" aria-label="Select <?= e($item['original_filename']) ?>">
                                </div>
                                <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener noreferrer" title="View full image (opens in new tab)">
                                    <img src="<?= e($fileUrl) ?>" class="card-img-top" alt="<?= e($item['original_filename']) ?>" loading="lazy">
                                    <span class="media-tile-zoom position-absolute bottom-0 end-0 m-1 badge bg-dark bg-opacity-75">
                                        <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                                    </span>
                                </a>
                            </div>
                            <div class="card-body p-2">
                                <p class="text-truncate mb-1" title="<?= e($item['original_filename']) ?>"><?= e($item['original_filename']) ?></p>
                                <?php if ($itemCategoryName !== null): ?>
                                    <span class="badge text-bg-light border mb-1"><?= e($itemCategoryName) ?></span>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-muted"><?= e($sizeLabel) ?></span>
                                    <form method="post" action="/admin/media-library/delete.php" onsubmit="return confirm('Delete this file? This cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <?= $filterInputs ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Delete" aria-label="Delete <?= e($item['original_filename']) ?>">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-xl-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="fs-6 mb-3">Categories</h2>

                <form method="post" action="/admin/media-library/categories/store.php" class="d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="text" name="name" class="form-control form-control-sm <?= error_for($errors, 'name') ? 'is-invalid' : '' ?>"
                           placeholder="New category name" aria-label="New category name" required>
                    <button type="submit" class="btn btn-primary btn-sm text-nowrap" title="Add category" aria-label="Add category">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    </button>
                </form>
                <?php if ($error = error_for($errors, 'name')): ?>
                    <div class="invalid-feedback d-block"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if (empty($categories)): ?>
                    <p class="text-muted small mb-0 mt-3">No categories yet.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush mt-3">
                        <?php foreach ($categories as $category): ?>
                            <li class="list-group-item px-0">
                                <div class="category-view d-flex justify-content-between align-items-center">
                                    <span class="text-truncate me-2"><?= e($category['name']) ?></span>

                                    <span class="text-nowrap category-actions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 category-rename-btn"
                                                title="Rename" aria-label="Rename <?= e($category['name']) ?>">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 category-delete-btn"
                                                title="Delete" aria-label="Delete <?= e($category['name']) ?>">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </span>

                                    <form method="post" action="/admin/media-library/categories/delete.php"
                                          class="category-delete-confirm d-none text-nowrap gap-1 m-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger py-0 px-1"
                                                title="Confirm delete" aria-label="Confirm delete <?= e($category['name']) ?>">
                                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 category-delete-cancel-btn"
                                                title="Cancel" aria-label="Cancel">
                                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                                <form method="post" action="/admin/media-library/categories/update.php"
                                      class="category-edit-form d-none align-items-center gap-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                    <input type="text" name="name" class="form-control form-control-sm" value="<?= e($category['name']) ?>"
                                           maxlength="100" aria-label="Category name" required>
                                    <button type="submit" class="btn btn-sm btn-primary py-0 px-1" title="Save" aria-label="Save">
                                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 category-cancel-btn"
                                            title="Cancel" aria-label="Cancel">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="fs-6 mb-3">Upload Settings</h2>
                <form method="post" action="/admin/media-library/settings.php">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="max_file_size_mb" class="form-label">Max File Size</label>
                        <div class="input-group input-group-sm">
                            <input type="number" min="1" max="50" class="form-control <?= error_for($errors, 'max_file_size_mb') ? 'is-invalid' : '' ?>"
                                   id="max_file_size_mb" name="max_file_size_mb" value="<?= (int) $settings['max_file_size_mb'] ?>">
                            <span class="input-group-text">MB</span>
                        </div>
                        <?php if ($error = error_for($errors, 'max_file_size_mb')): ?>
                            <div class="invalid-feedback d-block"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>

                    <fieldset class="mb-3">
                        <legend class="form-label fs-6 mb-2">Allowed File Types</legend>
                        <?php foreach ($availableTypes as $type): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="allowed_types[]" value="<?= e($type) ?>"
                                       id="type_<?= e($type) ?>" <?= in_array($type, $selectedTypes, true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_<?= e($type) ?>"><?= e(strtoupper($type)) ?></label>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($error = error_for($errors, 'allowed_types')): ?>
                            <div class="invalid-feedback d-block"><?= e($error) ?></div>
                        <?php else: ?>
                            <div class="form-text">Images only for now.</div>
                        <?php endif; ?>
                    </fieldset>

                    <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.MEDIA_LIBRARY_UPLOAD_URL = '/admin/media-library/upload.php';
    window.MEDIA_LIBRARY_CSRF_TOKEN = '<?= csrf_token() ?>';
</script>
<script src="/assets/js/media-library.js"></script>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
