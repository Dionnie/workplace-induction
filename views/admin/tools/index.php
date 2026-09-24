<?php

declare(strict_types=1);

$pageTitle = 'Tools';
$currentPage = 'tools';
require __DIR__ . '/../../partials/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tools</h1>
        <p class="page-subtitle">Maintenance utilities for administrators. Use with care: these can change existing data.</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-lg-4">
        <a href="/admin/tools/search-replace.php" class="card shadow-sm h-100 link-card">
            <div class="card-body">
                <h2 class="fs-6 mb-1"><i class="bi bi-search text-primary me-2" aria-hidden="true"></i>Search &amp; Replace</h2>
                <p class="text-muted small mb-0">Find and replace text directly in the database.</p>
            </div>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
