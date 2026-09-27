<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Tools\SearchReplaceService;

Auth::requireRole('admin');

$service = new SearchReplaceService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $find = (string) ($_POST['find'] ?? '');
    $replace = (string) ($_POST['replace'] ?? '');
    $tables = array_values(array_filter((array) ($_POST['tables'] ?? []), 'is_string'));
    $caseSensitive = isset($_POST['case_sensitive']);

    $result = $service->replace($tables, $find, $replace, $caseSensitive);

    if ($result['success']) {
        $tablesTouched = count(array_filter($result['updated']));
        flash('success', sprintf(
            'Replaced text in %d row(s) across %d table(s).',
            $result['totalUpdated'],
            $tablesTouched
        ));
    } else {
        flash('error', $result['errors']['form'] ?? $result['errors']['find'] ?? $result['errors']['tables'] ?? 'Replacement failed.');
    }

    // Redirect (not a direct re-render) so the browser's back/refresh never
    // resubmits the replace action, and so the page that loads next re-runs
    // search() fresh -- a successful replace should now show zero matches,
    // which is itself confirmation the change was applied.
    redirect('/admin/tools/search-replace.php?' . http_build_query([
        'find' => $find,
        'replace' => $replace,
        'tables' => $tables,
        'case_sensitive' => $caseSensitive ? '1' : null,
    ]));
}

$find = trim((string) ($_GET['find'] ?? ''));
$replace = (string) ($_GET['replace'] ?? '');
$tables = array_values(array_filter((array) ($_GET['tables'] ?? []), 'is_string'));
// Ticked on first load; after a dry run, whatever was submitted.
$caseSensitive = !isset($_GET['find']) || isset($_GET['case_sensitive']);

$allTables = $service->tables();
$results = ($find !== '' && !empty($tables)) ? $service->search($tables, $find, $caseSensitive) : null;

require __DIR__ . '/../../views/admin/tools/search-replace.php';
