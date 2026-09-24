<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

use App\Core\Auth;
use App\Core\SiteSettingsService;
use App\Notification\EmailSettingsService;
use App\Notification\NotificationService;

Auth::requireRole('admin');

$tabs = ['general', 'appearance', 'email', 'notifications'];
$tab = in_array($_GET['tab'] ?? '', $tabs, true) ? $_GET['tab'] : 'general';

$site = (new SiteSettingsService())->get();
$settings = (new EmailSettingsService())->get();
$nextReport = (new NotificationService())->nextReportDue();
$errors = get_errors();

require __DIR__ . '/../../views/admin/settings/index.php';

// Old input only applies to the render right after a failed save.
clear_old();
