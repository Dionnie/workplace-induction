<?php

declare(strict_types=1);

/**
 * Saves the colour theme chosen in Settings > Appearance: a preset from
 * App\Core\Theme::PRESETS, or custom Primary and Accent colours.
 *
 * The saved colours become the brand tokens (--color-primary-*,
 * --color-accent-*) that theme_style_tag() writes into every page head. The
 * Bootstrap bridge in assets/css/app.css maps Bootstrap's primary classes
 * to those tokens, so the whole UI follows the theme; status colours never
 * change. See docs/core/design-system.html#colour and
 * docs/core/ui-guidelines.md section 3.
 */

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
    'theme_preset' => trim($_POST['theme_preset'] ?? ''),
    'theme_primary' => trim($_POST['theme_primary'] ?? ''),
    'theme_accent' => trim($_POST['theme_accent'] ?? ''),
];

$result = (new SiteSettingsService())->updateAppearance($data);

if ($result['success']) {
    clear_old();
    flash('success', 'Appearance updated.');
} else {
    set_old($data);
    set_errors($result['errors']);
    flash('error', 'Please fix the highlighted fields.');
}

redirect('/admin/settings/index.php?tab=appearance');
