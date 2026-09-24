<?php

declare(strict_types=1);

$pageTitle = 'Tools';
$currentPage = 'tools';
require __DIR__ . '/../../partials/admin-header.php';
?>

<h1 class="fs-4 fw-semibold mb-3">Tools</h1>
<p class="text-muted mb-4">Maintenance utilities for admins. Use with care -- these can affect existing data.</p>

<div class="row g-3">
    <div class="col-md-6 col-lg-4">
        <a href="/admin/tools/search-replace.php" class="card shadow-sm h-100 text-decoration-none text-body">
            <div class="card-body">
                <h2 class="fs-6 fw-semibold mb-2"><i class="bi bi-search me-2" aria-hidden="true"></i>Search &amp; Replace</h2>
                <p class="text-muted small mb-0">
                    Find and replace text directly in the database.
                </p>
            </div>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
