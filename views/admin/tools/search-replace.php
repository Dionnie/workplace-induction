<?php

declare(strict_types=1);

/**
 * @var array<int, string> $allTables every table name in this database
 * @var string $find
 * @var string $replace
 * @var array<int, string> $tables selected table names
 * @var array{
 *     tables: array<string, array{
 *         columns: array<string, array{rows: array<int, array{pk:string, value:string, count:int, snippets: array<int, array{before:string,match:string,after:string}>}>, occurrences:int}>,
 *         occurrences: int
 *     }>,
 *     totalOccurrences: int,
 *     totalRows: int
 * }|null $results
 */
$pageTitle = 'Search & Replace';
$currentPage = 'tools';
require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
    <div>
        <nav aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/tools/index.php">Tools</a></li>
                <li class="breadcrumb-item active" aria-current="page">Search &amp; Replace</li>
            </ol>
        </nav>
        <h1 class="page-title">Search &amp; Replace</h1>
        <p class="page-subtitle">Run a dry run first to see every match, then replace.</p>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body p-4">
        <form method="get" action="/admin/tools/search-replace.php">
            <fieldset class="mb-3">
                <legend class="form-label fs-6 mb-2">Tables</legend>
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" id="sr-select-all">
                    <label class="form-check-label small text-muted" for="sr-select-all">Select all</label>
                </div>
                <div id="sr-table-list">
                    <?php foreach ($allTables as $table): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input sr-table-checkbox" type="checkbox" name="tables[]" value="<?= e($table) ?>"
                                   id="table_<?= e($table) ?>" <?= in_array($table, $tables, true) ? 'checked' : '' ?>>
                            <label class="form-check-label font-monospace" for="table_<?= e($table) ?>"><?= e($table) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="find" class="form-label">Find</label>
                    <input type="text" class="form-control" id="find" name="find" value="<?= e($find) ?>"
                           placeholder="e.g. https://old-domain.com" required>
                </div>
                <div class="col-md-6">
                    <label for="replace" class="form-label">Replace With <span class="text-muted small">(optional)</span></label>
                    <input type="text" class="form-control" id="replace" name="replace" value="<?= e($replace) ?>"
                           placeholder="e.g. https://new-domain.com">
                    <div class="form-text">Leave blank to remove the found text entirely.</div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Dry Run</button>
            </div>
        </form>
    </div>
</div>

<?php if ($results !== null): ?>
    <?php if ($results['totalRows'] === 0): ?>
        <div class="alert alert-info">No matches found.</div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="fs-6 mb-3">
                    Found <?= (int) $results['totalOccurrences'] ?> occurrence(s) across <?= (int) $results['totalRows'] ?> row(s)
                </h2>

                <?php foreach ($results['tables'] as $tableName => $tableResult): ?>
                    <?php foreach ($tableResult['columns'] as $columnName => $columnResult): ?>
                        <h3 class="fs-6 text-muted font-monospace mt-4 mb-2"><?= e($tableName) ?>.<?= e($columnName) ?></h3>
                        <?php foreach ($columnResult['rows'] as $row): ?>
                            <div class="border rounded p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                    <strong class="font-monospace small"><?= e($row['pk']) ?> = <?= e($row['value']) ?></strong>
                                    <span class="badge text-bg-light border"><?= (int) $row['count'] ?> occurrence(s)</span>
                                </div>
                                <?php foreach ($row['snippets'] as $snippet): ?>
                                    <div class="font-monospace small text-muted mb-1 text-break">
                                        <?= e($snippet['before']) ?><mark><?= e($snippet['match']) ?></mark><?= e($snippet['after']) ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($row['count'] > count($row['snippets'])): ?>
                                    <div class="small text-muted fst-italic">&hellip; and more occurrences in this row.</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <form method="post" action="/admin/tools/search-replace.php" class="form-actions mt-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="find" value="<?= e($find) ?>">
                    <input type="hidden" name="replace" value="<?= e($replace) ?>">
                    <?php foreach ($tables as $table): ?>
                        <input type="hidden" name="tables[]" value="<?= e($table) ?>">
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Replace</button>
                    <a href="/admin/tools/search-replace.php" class="btn btn-outline-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
document.getElementById('sr-select-all').addEventListener('change', function () {
    var checked = this.checked;
    document.querySelectorAll('.sr-table-checkbox').forEach(function (box) { box.checked = checked; });
});
</script>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
