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
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="fs-4 fw-semibold mb-0">Media Library</h1>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
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
                        <input type="text" name="search" id="media-search" class="form-control form-control-sm"
                               placeholder="Search by filename" value="<?= e($search) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Search" aria-label="Search">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="col-auto ms-auto">
                        <label class="form-label small mb-1 d-block">View</label>
                        <div class="btn-group btn-group-sm" role="group" aria-label="View mode">
                            <button type="submit" name="view" value="grid"
                                    class="btn btn-outline-secondary <?= $view === 'grid' ? 'active' : '' ?>"
                                    title="Grid view (larger previews)" aria-label="Grid view">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </button>
                            <button type="submit" name="view" value="list"
                                    class="btn btn-outline-secondary <?= $view === 'list' ? 'active' : '' ?>"
                                    title="List view (full filenames)" aria-label="List view">
                                <i class="bi bi-list-ul"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <div id="media-dropzone" class="border border-2 border-dashed rounded p-4 text-center bg-surface-subtle" role="button" tabindex="0">
                    <i class="bi bi-cloud-arrow-up fs-2 text-muted d-block mb-1"></i>
                    <p class="mb-1 fw-semibold">Drag &amp; drop images here, or click to browse</p>
                    <p class="text-muted small mb-0">
                        New uploads are grouped into:
                        <strong><?= $categoryId === null ? 'None' : e((string) array_reduce(
                            $categories,
                            fn (?string $carry, array $c) => (int) $c['id'] === $categoryId ? $c['name'] : $carry,
                            null
                        )) ?></strong>
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
                  class="card shadow-sm mb-2 d-flex flex-row flex-wrap align-items-center gap-2 p-2">
                <?= csrf_field() ?>
                <input type="hidden" name="category_id" value="<?= $categoryId !== null ? (int) $categoryId : '' ?>">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="view" value="<?= e($view) ?>">

                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="media-select-all">
                    <label class="form-check-label small" for="media-select-all">Select all</label>
                </div>
                <span id="media-selected-count" class="small text-muted">0 selected</span>
                <select name="target_category_id" class="form-select form-select-sm ms-auto" style="width: auto;" aria-label="Move to category">
                    <option value="">None</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" id="media-bulk-move-btn" class="btn btn-outline-primary btn-sm" disabled>
                    <i class="bi bi-folder-symlink"></i> Move to Category
                </button>
                <button type="submit" id="media-bulk-delete-btn" class="btn btn-outline-danger btn-sm" disabled
                        formaction="/admin/media-library/bulk-delete.php"
                        onclick="return confirm('Delete the selected file(s)? This cannot be undone.');">
                    <i class="bi bi-trash"></i> Delete Selected
                </button>
            </form>
        <?php endif; ?>

        <?php if ($view === 'list'): ?>
            <div class="table-responsive bg-white rounded shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 2rem;"></th>
                            <th style="width: 4rem;"></th>
                            <th>Filename</th>
                            <th>Category</th>
                            <th>Size</th>
                            <th></th>
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
                                $itemCategory = null;
                                foreach ($categories as $category) {
                                    if ((int) $category['id'] === (int) $item['category_id']) {
                                        $itemCategory = $category;
                                        break;
                                    }
                                }
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
                                        <img src="<?= e($fileUrl) ?>" class="rounded" style="width: 3rem; height: 3rem; object-fit: cover;"
                                             alt="<?= e($item['original_filename']) ?>" loading="lazy">
                                    </a>
                                </td>
                                <td class="text-break">
                                    <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener noreferrer" title="View full image (opens in new tab)">
                                        <?= e($item['original_filename']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($itemCategory): ?>
                                        <span class="badge text-bg-secondary"><?= e($itemCategory['name']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small text-nowrap"><?= e($sizeLabel) ?></td>
                                <td class="text-end">
                                    <form method="post" action="/admin/media-library/delete.php" onsubmit="return confirm('Delete this file? This cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="category_id" value="<?= $categoryId !== null ? (int) $categoryId : '' ?>">
                                        <input type="hidden" name="search" value="<?= e($search) ?>">
                                        <input type="hidden" name="view" value="<?= e($view) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Delete" aria-label="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                        $itemCategory = null;
                        foreach ($categories as $category) {
                            if ((int) $category['id'] === (int) $item['category_id']) {
                                $itemCategory = $category;
                                break;
                            }
                        }
                        $sizeKb = (int) $item['size'] / 1024;
                        $sizeLabel = $sizeKb >= 1024 ? round($sizeKb / 1024, 1) . ' MB' : round($sizeKb, 1) . ' KB';
                        $fileUrl = '/assets/uploads/media-library/' . $item['filename'];
                    ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="position-relative">
                                <div class="position-absolute top-0 start-0 m-1 bg-white bg-opacity-75 rounded d-flex align-items-center justify-content-center"
                                     style="z-index: 2; width: 1.5rem; height: 1.5rem;">
                                    <input class="form-check-input media-select m-0" type="checkbox" name="ids[]" value="<?= (int) $item['id'] ?>"
                                           form="bulk-move-form" aria-label="Select <?= e($item['original_filename']) ?>">
                                </div>
                                <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener noreferrer" title="View full image (opens in new tab)">
                                    <img src="<?= e($fileUrl) ?>" class="card-img-top"
                                         style="aspect-ratio: 1 / 1; object-fit: cover;" alt="<?= e($item['original_filename']) ?>" loading="lazy">
                                    <span class="position-absolute bottom-0 end-0 m-1 badge bg-dark bg-opacity-75" style="z-index: 2;">
                                        <i class="bi bi-arrows-fullscreen"></i>
                                    </span>
                                </a>
                            </div>
                            <div class="card-body p-2">
                                <p class="small text-truncate mb-1" style="font-size: 0.75rem;" title="<?= e($item['original_filename']) ?>">
                                    <?= e($item['original_filename']) ?>
                                </p>
                                <?php if ($itemCategory): ?>
                                    <span class="badge text-bg-secondary mb-1" style="font-size: 0.65rem;"><?= e($itemCategory['name']) ?></span>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-muted" style="font-size: 0.7rem;"><?= e($sizeLabel) ?></span>
                                    <form method="post" action="/admin/media-library/delete.php" onsubmit="return confirm('Delete this file? This cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="category_id" value="<?= $categoryId !== null ? (int) $categoryId : '' ?>">
                                        <input type="hidden" name="search" value="<?= e($search) ?>">
                                        <input type="hidden" name="view" value="<?= e($view) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Delete" aria-label="Delete">
                                            <i class="bi bi-trash"></i>
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

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold mb-3">Categories</h2>

                <form method="post" action="/admin/media-library/categories/store.php" class="d-flex gap-2 mb-1">
                    <?= csrf_field() ?>
                    <input type="text" name="name" class="form-control form-control-sm <?= error_for($errors, 'name') ? 'is-invalid' : '' ?>"
                           placeholder="New category name" required>
                    <button type="submit" class="btn btn-primary btn-sm text-nowrap" title="Add category" aria-label="Add category">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </form>
                <?php if ($error = error_for($errors, 'name')): ?>
                    <div class="text-danger small mb-2"><?= e($error) ?></div>
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
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 category-delete-btn"
                                                title="Delete" aria-label="Delete <?= e($category['name']) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </span>

                                    <form method="post" action="/admin/media-library/categories/delete.php"
                                          class="category-delete-confirm d-none text-nowrap gap-1 m-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1"
                                                title="Confirm delete" aria-label="Confirm delete <?= e($category['name']) ?>">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 category-delete-cancel-btn"
                                                title="Cancel" aria-label="Cancel">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                                <form method="post" action="/admin/media-library/categories/update.php"
                                      class="category-edit-form d-none align-items-center gap-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                    <input type="text" name="name" class="form-control form-control-sm" value="<?= e($category['name']) ?>"
                                           maxlength="100" required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-1" title="Save" aria-label="Save">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 category-cancel-btn"
                                            title="Cancel" aria-label="Cancel">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold mb-3">Upload Settings</h2>
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
                            <div class="text-danger small mt-1"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Allowed File Types</label>
                        <?php foreach ($availableTypes as $type): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="allowed_types[]" value="<?= e($type) ?>"
                                       id="type_<?= e($type) ?>" <?= in_array($type, $selectedTypes, true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_<?= e($type) ?>"><?= e(strtoupper($type)) ?></label>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-text">Images only for now.</div>
                        <?php if ($error = error_for($errors, 'allowed_types')): ?>
                            <div class="text-danger small mt-1"><?= e($error) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-circle"></i> Save Settings</button>
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
