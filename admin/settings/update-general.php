<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Core\SiteSettingsService;

Auth::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$data = [
    'company_name' => trim($_POST['company_name'] ?? ''),
    'logo_url' => trim($_POST['logo_url'] ?? ''),
    'primary_email' => trim($_POST['primary_email'] ?? ''),
];

$result = (new SiteSettingsService())->updateGeneral($data);

if ($result['success']) {
    clear_old();
    flash('success', 'General settings updated.');
} else {
    set_old($data);
    set_errors($result['errors']);
    flash('error', 'Please fix the highlighted fields.');
}

redirect('/admin/settings/index.php?tab=general');
