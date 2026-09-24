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

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="fs-4 fw-semibold mb-0">Search &amp; Replace</h1>
    <a href="/admin/tools/index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Tools</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="/admin/tools/search-replace.php">
            <div class="mb-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <label class="form-label mb-0">Tables</label>
                    <div class="form-check form-check-inline mb-0 ms-2">
                        <input class="form-check-input" type="checkbox" id="sr-select-all">
                        <label class="form-check-label small text-muted" for="sr-select-all">Select all</label>
                    </div>
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
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="find" class="form-label">Find</label>
                    <input type="text" class="form-control" id="find" name="find" value="<?= e($find) ?>"
                           placeholder="e.g. https://old-domain.com" required>
                </div>
                <div class="col-md-6">
                    <label for="replace" class="form-label">Replace With</label>
                    <input type="text" class="form-control" id="replace" name="replace" value="<?= e($replace) ?>"
                           placeholder="e.g. https://new-domain.com">
                    <div class="form-text">Leave blank to remove the found text entirely.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>Dry Run</button>
        </form>
    </div>
</div>

<?php if ($results !== null): ?>
    <?php if ($results['totalRows'] === 0): ?>
        <div class="alert alert-secondary">No matches found.</div>
    <?php else: ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold mb-3">
                    Found <?= (int) $results['totalOccurrences'] ?> occurrence(s) across <?= (int) $results['totalRows'] ?> row(s)
                </h2>

                <?php foreach ($results['tables'] as $tableName => $tableResult): ?>
                    <?php foreach ($tableResult['columns'] as $columnName => $columnResult): ?>
                        <h3 class="fs-6 fw-semibold text-muted font-monospace mt-4 mb-2"><?= e($tableName) ?>.<?= e($columnName) ?></h3>
                        <?php foreach ($columnResult['rows'] as $row): ?>
                            <div class="border rounded p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="font-monospace small"><?= e($row['pk']) ?> = <?= e($row['value']) ?></strong>
                                    <span class="badge text-bg-secondary"><?= (int) $row['count'] ?> occurrence(s)</span>
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

                <div class="d-flex gap-2 mt-3">
                    <form method="post" action="/admin/tools/search-replace.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="find" value="<?= e($find) ?>">
                        <input type="hidden" name="replace" value="<?= e($replace) ?>">
                        <?php foreach ($tables as $table): ?>
                            <input type="hidden" name="tables[]" value="<?= e($table) ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Replace</button>
                    </form>
                    <a href="/admin/tools/search-replace.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
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
