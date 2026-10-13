<?php

declare(strict_types=1);

use App\Core\Theme;
use App\Notification\EmailSettingsService;

/**
 * @var string $tab
 * @var array<string, mixed> $site
 * @var array<string, mixed> $settings
 * @var array<string, string> $errors
 * @var DateTimeImmutable $nextReport
 */
$pageTitle = 'Settings';
$currentPage = 'settings';
require __DIR__ . '/../../partials/admin-header.php';

$navGroups = [
    'Core' => [
        'general' => ['General', 'bi-building'],
        'appearance' => ['Appearance', 'bi-palette'],
        'email' => ['Email', 'bi-envelope'],
    ],
    'Application' => [
        'notifications' => ['Notifications', 'bi-bell'],
    ],
];

$checked = fn (string $field): string => old($field, $settings[$field] ? '1' : '') === '1' ? 'checked' : '';

// Field help in a tooltip (docs/rules/design-system.html#forms): an info
// button after the label. The control points at it with aria-describedby.
$help = fn (string $id, string $text): string => '<button type="button" class="field-help" id="' . e($id) . '"'
    . ' data-bs-toggle="tooltip" data-bs-title="' . e($text) . '" aria-label="' . e($text) . '">'
    . '<i class="bi bi-info-circle" aria-hidden="true"></i></button>';
$logoUrl = old_raw('logo_url', (string) ($site['logo_url'] ?? ''));
$frequency = old_raw('admin_notification_frequency', (string) $settings['admin_notification_frequency']);
?>

<div class="page-header">
    <h1 class="page-title">Settings</h1>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <nav aria-label="Settings sections">
            <?php foreach ($navGroups as $groupLabel => $items): ?>
                <div class="small text-uppercase text-muted fw-semibold mb-2 <?= $groupLabel !== 'Core' ? 'mt-3' : '' ?>"><?= e($groupLabel) ?></div>
                <div class="list-group">
                    <?php foreach ($items as $key => [$label, $icon]): ?>
                        <a href="/admin/settings/index.php?tab=<?= e($key) ?>"
                           class="list-group-item list-group-item-action <?= $tab === $key ? 'active' : '' ?>"
                           <?= $tab === $key ? 'aria-current="page"' : '' ?>>
                            <i class="bi <?= e($icon) ?> me-2" aria-hidden="true"></i><?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="col-md-9 page-narrow">

        <?php if ($tab === 'general'): ?>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="fs-6 mb-1">General</h2>
                    <p class="text-muted small mb-3">Your organization's branding, shown in page headers and on every email.</p>

                    <form method="post" action="/admin/settings/update-general.php" novalidate>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="company_name" class="form-label required">Company Name</label>
                            <input type="text" class="form-control <?= error_for($errors, 'company_name') ? 'is-invalid' : '' ?>"
                                   id="company_name" name="company_name" maxlength="150" required
                                   value="<?= old('company_name', (string) $site['company_name']) ?>">
                            <?php if ($error = error_for($errors, 'company_name')): ?>
                                <div class="invalid-feedback"><?= e($error) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <div class="mb-2">
                                <label class="form-label mb-0" for="logo-choose">Company Logo</label>
                                <?= $help('logo-help', 'Pick an image from the Media Library. A wide logo on a transparent background works best.') ?>
                            </div>
                            <input type="hidden" id="logo_url" name="logo_url" value="<?= e($logoUrl) ?>">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="logo-preview-box rounded d-flex align-items-center justify-content-center">
                                    <img id="logo-preview" <?= $logoUrl !== '' ? 'src="' . e($logoUrl) . '"' : '' ?> alt="Company logo" <?= $logoUrl === '' ? 'hidden' : '' ?>>
                                    <span id="logo-empty" class="text-muted small" <?= $logoUrl !== '' ? 'hidden' : '' ?>>No logo</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="logo-choose" aria-describedby="logo-help">
                                        <i class="bi bi-images me-1" aria-hidden="true"></i>Choose Logo
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="logo-remove" <?= $logoUrl === '' ? 'hidden' : '' ?>>Remove</button>
                                </div>
                            </div>
                            <?php if ($error = error_for($errors, 'logo_url')): ?>
                                <div class="invalid-feedback d-block"><?= e($error) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="primary_email" class="form-label">Primary Email</label>
                            <?= $help('primary_email-help', "Your organization's contact address. Shown in email footers and used as the From Email unless one is set under Email. Blank uses admin@ this site's domain, the address shown.") ?>
                            <input type="email" class="form-control <?= error_for($errors, 'primary_email') ? 'is-invalid' : '' ?>"
                                   id="primary_email" name="primary_email" aria-describedby="primary_email-help"
                                   placeholder="<?= e(default_email()) ?>"
                                   value="<?= old('primary_email', (string) ($site['primary_email'] ?? '')) ?>">
                            <?php if ($error = error_for($errors, 'primary_email')): ?>
                                <div class="invalid-feedback"><?= e($error) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($tab === 'appearance'): ?>
            <?php
            $themePreset = old_raw('theme_preset', (string) ($site['theme_preset'] ?? Theme::DEFAULT_PRESET));
            $currentColors = Theme::colors($site);
            $themePrimary = old_raw('theme_primary', (string) ($site['theme_primary'] ?: $currentColors['primary_700']));
            $themeAccent = old_raw('theme_accent', (string) ($site['theme_accent'] ?: $currentColors['accent']));
            $customColors = Theme::fromCustom($themePrimary, $themeAccent);

            // One radio per theme; data-color-* feed the live preview.
            $themeRadio = function (string $key, array $colors) use ($themePreset): string {
                return sprintf(
                    '<input type="radio" class="btn-check" name="theme_preset" id="theme_%1$s" value="%1$s" required '
                    . 'data-color-dark="%2$s" data-color-hover="%3$s" data-color-main="%4$s" data-color-accent="%5$s"%6$s>',
                    e($key), e($colors['primary_900']), e($colors['primary_800']), e($colors['primary_700']), e($colors['accent']),
                    $themePreset === $key ? ' checked' : ''
                );
            };
            $swatchColors = function (array $colors): string {
                return '<span class="theme-swatch-colors" aria-hidden="true">'
                    . '<span data-swatch="dark" style="background:' . e($colors['primary_900']) . '"></span>'
                    . '<span data-swatch="main" style="background:' . e($colors['primary_700']) . '"></span>'
                    . '<span data-swatch="accent" style="background:' . e($colors['accent']) . '"></span>'
                    . '</span>';
            };
            ?>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="fs-6 mb-1">Appearance</h2>
                    <p class="text-muted small mb-3">
                        Brand colours for the navbar, buttons and links. Emails and certificates stay plain white, so they read well with any logo. Status colours (green, amber, red) never change.
                    </p>

                    <form method="post" action="/admin/settings/update-appearance.php" novalidate id="appearance-form">
                        <?= csrf_field() ?>

                        <fieldset class="mb-4">
                            <legend class="form-label fs-6 mb-2 required">Colour Theme</legend>
                            <div class="row row-cols-3 row-cols-md-6 g-2">
                                <?php foreach (Theme::PRESETS as $key => $preset): ?>
                                    <div class="col">
                                        <?= $themeRadio($key, $preset) ?>
                                        <label class="theme-swatch" for="theme_<?= e($key) ?>">
                                            <?= $swatchColors($preset) ?>
                                            <span class="small"><?= e($preset['label']) ?></span>
                                            <?php if ($key === Theme::DEFAULT_PRESET): ?>
                                                <span class="theme-swatch-note">Default</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-2">
                                <?= $themeRadio('custom', $customColors) ?>
                                <label class="theme-swatch theme-swatch-row" for="theme_custom">
                                    <?= $swatchColors($customColors) ?>
                                    <span class="small">Custom colours</span>
                                    <span class="theme-swatch-note d-none d-sm-inline">Pick your own Primary and Accent</span>
                                </label>
                            </div>
                            <?php if ($error = error_for($errors, 'theme_preset')): ?>
                                <div class="invalid-feedback d-block"><?= e($error) ?></div>
                            <?php endif; ?>

                            <div id="theme-custom" class="border rounded mt-2 p-3 bg-surface-subtle" <?= $themePreset !== 'custom' ? 'hidden' : '' ?>>
                                <div class="row g-3">
                                    <?php foreach ([
                                        'theme_primary' => ['Primary', $themePrimary, 'Header, buttons and links. Must be dark enough for white text.'],
                                        'theme_accent' => ['Accent', $themeAccent, 'Small highlights, used sparingly.'],
                                    ] as $name => [$label, $color, $colorHelp]): ?>
                                        <div class="col-sm-6">
                                            <label for="<?= e($name) ?>" class="form-label required"><?= e($label) ?></label>
                                            <?= $help($name . '-help', $colorHelp) ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" class="form-control form-control-color <?= error_for($errors, $name) ? 'is-invalid' : '' ?>"
                                                       id="<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e($color) ?>" required
                                                       aria-describedby="<?= e($name) ?>-help">
                                                <code class="small text-body" data-hex-for="<?= e($name) ?>"><?= e($color) ?></code>
                                                <span class="badge text-bg-danger" data-contrast-warning="<?= e($name) ?>" hidden>Too light</span>
                                            </div>
                                            <?php if ($error = error_for($errors, $name)): ?>
                                                <div class="invalid-feedback d-block"><?= e($error) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </fieldset>

                        <div class="d-flex justify-content-between align-items-baseline mb-2">
                            <div class="form-label fs-6 mb-0">Preview</div>
                            <div class="text-muted small">Applies after you click Save</div>
                        </div>
                        <!-- The script sets the theme's --color-* tokens on this box; .theme-scope makes Bootstrap's own classes follow them too. -->
                        <div id="theme-preview" class="theme-preview theme-scope border rounded overflow-hidden mb-3" aria-hidden="true">
                            <div class="theme-preview-header d-flex align-items-center justify-content-between gap-3 px-3 py-2">
                                <span class="d-flex align-items-center gap-2 text-white fw-semibold text-truncate">
                                    <?php if (!empty($site['logo_url'])): ?><img src="<?= e($site['logo_url']) ?>" alt="" class="brand-logo"><?php endif; ?>
                                    <?= e($site['company_name']) ?>
                                </span>
                                <span class="d-none d-sm-flex gap-3 small text-nowrap">
                                    <span class="text-white fw-semibold">Dashboard</span>
                                    <span class="text-white-50">Inductions</span>
                                    <span class="text-white-50">Compliance</span>
                                </span>
                            </div>
                            <div class="p-3 bg-white">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="fw-semibold">Site Safety Induction</span>
                                    <span class="cb-type-badge cb-type-badge-lecture mb-0">Lecture</span>
                                </div>
                                <p class="small text-muted mb-3">
                                    Complete all content blocks, then <a href="#" tabindex="-1" onclick="return false;">view your certificate</a>.
                                </p>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <span class="btn btn-primary btn-sm">Save</span>
                                    <span class="btn btn-outline-secondary btn-sm">Cancel</span>
                                    <span class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" checked tabindex="-1" onclick="return false;">
                                        <span class="form-check-label small">Notify me</span>
                                    </span>
                                    <?= status_badge('active') ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($tab === 'email'): ?>
            <?php
            $audienceHelp = [
                'inductee' => 'Sent to each inductee: completion confirmations, expiry reminders and account emails (verify email, password reset).',
                'admin' => 'New registration and completion alerts, and the periodic report. Sent to the To list, not to administrator accounts.',
            ];
            $primaryEmail = ($site['primary_email'] ?? '') ?: default_email();
            ?>

            <form method="post" action="/admin/settings/update-email.php" novalidate>
                <?= csrf_field() ?>

                <?php foreach (EmailSettingsService::AUDIENCES as $audience => $audienceLabel): ?>
                    <?php
                    $field = fn (string $name): string => "{$audience}_{$name}";
                    $value = fn (string $name): string => old($field($name), (string) ($settings[$field($name)] ?? ''));
                    $invalid = fn (string $name): string => error_for($errors, $field($name)) ? 'is-invalid' : '';
                    $notCopied = $audience === 'inductee' ? ' Account emails (verify email, password reset) are never copied.' : '';

                    // A blank field uses its fallback, so the placeholder shows that
                    // fallback, and nothing when there is none. Only administrator
                    // emails have a To list; inductee emails go to the inductee.
                    $fields = [
                        'sender_name' => ['From Name', 'text', $site['company_name'], 'Blank uses the Company Name.'],
                        'sender_email' => ['From Email', 'email', $primaryEmail, 'Blank uses the Primary Email, set under General.'],
                        'to' => ['To', 'text', default_email(),"Who receives these emails, separated by commas. Blank sends to admin@ this site's domain, the address shown."],
                        'cc' => ['CC', 'text', '', 'Other people to copy, separated by commas.' . $notCopied],
                        'bcc' => ['BCC', 'text', '', 'Hidden copies, e.g. for records, separated by commas.' . $notCopied],
                    ];
                    $fields = array_intersect_key($fields, array_flip(EmailSettingsService::FIELDS[$audience]));
                    $testTitle = $audience === 'admin'
                        ? 'Sends a sample to the saved To, CC and BCC'
                        : 'Sends a sample to ' . ($authUser['email'] ?? '') . ' with the saved sender, CC and BCC';
                    ?>
                    <div class="card shadow-sm mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                                <h2 class="fs-6 mb-0">
                                    <?= e($audienceLabel) ?> Emails
                                    <?= $help($field('help'), $audienceHelp[$audience]) ?>
                                </h2>
                                <button type="submit" form="test-email-<?= e($audience) ?>" class="btn btn-outline-secondary btn-sm text-nowrap"
                                        title="<?= e($testTitle) ?>">
                                    <i class="bi bi-send me-1" aria-hidden="true"></i>Send Test Email
                                </button>
                            </div>

                            <div class="row g-3">
                                <?php foreach ($fields as $name => [$label, $type, $placeholder, $helpText]): ?>
                                    <div class="<?= $name === 'to' ? 'col-12' : 'col-sm-6' ?>">
                                        <label for="<?= e($field($name)) ?>" class="form-label"><?= e($label) ?></label>
                                        <?= $help($field($name) . '-help', $helpText) ?>
                                        <input type="<?= $type ?>" class="form-control <?= $invalid($name) ?>"
                                               id="<?= e($field($name)) ?>" name="<?= e($field($name)) ?>"
                                               <?= $name === 'sender_name' ? 'maxlength="150"' : '' ?>
                                               <?= $placeholder !== '' ? 'placeholder="' . e($placeholder) . '"' : '' ?>
                                               aria-describedby="<?= e($field($name)) ?>-help" value="<?= $value($name) ?>">
                                        <?php if ($error = error_for($errors, $field($name))): ?>
                                            <div class="invalid-feedback"><?= e($error) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="form-actions mb-4">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

            <?php foreach (array_keys(EmailSettingsService::AUDIENCES) as $audience): ?>
                <form method="post" action="/admin/settings/send-test-email.php" id="test-email-<?= e($audience) ?>" hidden>
                    <?= csrf_field() ?>
                    <input type="hidden" name="audience" value="<?= e($audience) ?>">
                </form>
            <?php endforeach; ?>

        <?php else: ?>
            <form method="post" action="/admin/settings/update-notifications.php" novalidate>
                <?= csrf_field() ?>

                <div class="card shadow-sm mb-3">
                    <div class="card-body p-4">
                        <h2 class="fs-6 mb-1">Inductee Notifications</h2>
                        <p class="text-muted small mb-3">Sent to the inductee straight away, from the sender set under <a href="/admin/settings/index.php?tab=email">Email</a>.</p>

                        <div class="d-flex justify-content-between align-items-start gap-3 py-2 border-bottom">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="notify_inductee_on_completion" name="notify_inductee_on_completion" value="1" <?= $checked('notify_inductee_on_completion') ?>
                                       aria-describedby="notify_inductee_on_completion-help">
                                <label class="form-check-label" for="notify_inductee_on_completion">Induction completed</label>
                                <?= $help('notify_inductee_on_completion-help', 'Confirmation with a link to their Certificate.') ?>
                            </div>
                            <button type="button" class="btn btn-link btn-sm text-nowrap" data-email-preview="induction-completed-inductee">Preview</button>
                        </div>

                        <div class="d-flex justify-content-between align-items-start gap-3 py-2">
                            <div>
                                <div class="form-check mb-0">
                                    <input type="checkbox" class="form-check-input" id="notify_inductee_on_expiry" name="notify_inductee_on_expiry" value="1" <?= $checked('notify_inductee_on_expiry') ?>>
                                    <label class="form-check-label" for="notify_inductee_on_expiry">Compliance expiring soon</label>
                                </div>
                                <div class="reminder-days input-group input-group-sm mt-2 ms-4">
                                    <span class="input-group-text">Remind</span>
                                    <input type="number" min="1" max="365" class="form-control <?= error_for($errors, 'expiry_reminder_days') ? 'is-invalid' : '' ?>"
                                           id="expiry_reminder_days" name="expiry_reminder_days" aria-label="Days before expiry" required
                                           value="<?= old('expiry_reminder_days', (string) $settings['expiry_reminder_days']) ?>">
                                    <span class="input-group-text">days before</span>
                                </div>
                                <?php if ($error = error_for($errors, 'expiry_reminder_days')): ?>
                                    <div class="invalid-feedback d-block ms-4"><?= e($error) ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-link btn-sm text-nowrap" data-email-preview="compliance-expiring">Preview</button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-body p-4">
                        <h2 class="fs-6 mb-1">Administrator Notifications</h2>
                        <p class="text-muted small mb-3">Sent to the To list, CC and BCC set under <a href="/admin/settings/index.php?tab=email">Email</a>.</p>

                        <fieldset class="mb-3">
                            <?php // The help button is inside the legend, so the group's name carries the help too. ?>
                            <legend class="form-label fs-6 mb-2">
                                <span class="required">Delivery</span>
                                <?= $help('delivery-help', "Instant sends one email per event. A report sends one summary per period (weekly on Mondays, monthly on the 1st), so a busy week doesn't flood your inbox. Periods with no activity send nothing.") ?>
                            </legend>
                            <?php foreach (EmailSettingsService::ADMIN_FREQUENCIES as $value => $label): ?>
                                <div class="form-check form-check-inline">
                                    <input type="radio" class="form-check-input" id="frequency_<?= e($value) ?>" name="admin_notification_frequency" required
                                           value="<?= e($value) ?>" <?= $frequency === $value ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="frequency_<?= e($value) ?>"><?= e($label) ?></label>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($error = error_for($errors, 'admin_notification_frequency')): ?>
                                <div class="invalid-feedback d-block"><?= e($error) ?></div>
                            <?php endif; ?>
                        </fieldset>

                        <div class="form-label fs-6 mb-2">Include</div>
                        <div class="d-flex justify-content-between align-items-start gap-3 py-2 border-bottom">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="notify_admin_on_registration" name="notify_admin_on_registration" value="1" <?= $checked('notify_admin_on_registration') ?>>
                                <label class="form-check-label" for="notify_admin_on_registration">New registrations</label>
                            </div>
                            <button type="button" class="btn btn-link btn-sm text-nowrap" data-email-preview="inductee-registered">Preview instant email</button>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-3 py-2 border-bottom">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="notify_admin_on_completion" name="notify_admin_on_completion" value="1" <?= $checked('notify_admin_on_completion') ?>>
                                <label class="form-check-label" for="notify_admin_on_completion">Completed inductions</label>
                            </div>
                            <button type="button" class="btn btn-link btn-sm text-nowrap" data-email-preview="induction-completed">Preview instant email</button>
                        </div>
                        <div class="py-2 mb-2">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="notify_admin_on_expired" name="notify_admin_on_expired" value="1" <?= $checked('notify_admin_on_expired') ?>
                                       aria-describedby="notify_admin_on_expired-help">
                                <label class="form-check-label" for="notify_admin_on_expired">Expired compliance</label>
                                <?= $help('notify_admin_on_expired-help', 'Compliance Records that lapsed without renewal. With Instant delivery, sent as a daily list.') ?>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3 small">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-email-preview="admin-report">
                                <i class="bi bi-eye me-1" aria-hidden="true"></i>Preview Next Report
                            </button>
                            <span class="text-muted">
                                <?= $settings['admin_notification_frequency'] === 'instant' ? 'Next expired compliance list' : 'Next report' ?>:
                                <?= e($nextReport->format('D j M Y')) ?>
                                <?php if ($settings['notify_admin_on_expired'] || $settings['admin_notification_frequency'] !== 'instant'): ?>
                                    (only sent if there is activity)
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-actions mb-4">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

            <div class="card shadow-sm mb-3">
                <div class="card-body p-4">
                    <h2 class="fs-6 mb-1">Scheduled Sending</h2>
                    <p class="text-muted small mb-3">
                        Expiry reminders and administrator reports are sent by <code>cron/send-notifications.php</code>.
                        Schedule it to run once a day. It never sends the same reminder or report twice.
                    </p>
                    <form method="post" action="/admin/settings/send-reminders.php" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Run Now</button>
                    </form>
                </div>
            </div>

            <p class="text-muted small">
                <i class="bi bi-lock me-1" aria-hidden="true"></i>
                Account emails are always sent:
                <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-email-preview="verify-email">Verify email</button>
                &middot;
                <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-email-preview="password-reset">Password reset</button>
                &middot;
                <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-email-preview="account-setup">Account setup</button>
            </p>
        <?php endif; ?>

    </div>
</div>

<div class="modal fade" id="email-preview-modal" tabindex="-1" aria-labelledby="email-preview-subject" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="text-muted small">Subject</div>
                    <h2 class="modal-title fs-6" id="email-preview-subject">Loading…</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="email-preview-frame" title="Email preview" class="email-preview-frame w-100 border-0 d-block"></iframe>
            </div>
        </div>
    </div>
</div>


<script>
    (function () {
        'use strict';

        // Email preview: the preview page's <title> is the email subject.
        var modalEl = document.getElementById('email-preview-modal');
        var frame = document.getElementById('email-preview-frame');
        var subject = document.getElementById('email-preview-subject');

        frame.addEventListener('load', function () {
            if (frame.getAttribute('src')) {
                subject.textContent = frame.contentDocument ? frame.contentDocument.title : '';
            }
        });

        document.querySelectorAll('[data-email-preview]').forEach(function (button) {
            button.addEventListener('click', function () {
                subject.textContent = 'Loading…';
                frame.src = '/admin/settings/email-preview.php?template=' + encodeURIComponent(button.dataset.emailPreview);
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        });
    })();
</script>

<?php if ($tab === 'appearance'): ?>
    <script>
        (function () {
            'use strict';

            // Live preview only -- the saved theme applies after Save.
            var form = document.getElementById('appearance-form');
            var customPanel = document.getElementById('theme-custom');
            var customRadio = document.getElementById('theme_custom');
            var primaryInput = document.getElementById('theme_primary');
            var accentInput = document.getElementById('theme_accent');
            var preview = document.getElementById('theme-preview');

            function rgb(hex) {
                return [1, 3, 5].map(function (i) { return parseInt(hex.substr(i, 2), 16); });
            }

            // Same shading as App\Core\Theme::darken().
            function darken(hex, amount) {
                return '#' + rgb(hex).map(function (channel) {
                    return ('0' + Math.round(channel * (1 - amount)).toString(16)).slice(-2);
                }).join('');
            }

            // Same check as App\Core\Theme::contrastWithWhite() >= 4.5.
            function readableOnWhite(hex) {
                var weights = [0.2126, 0.7152, 0.0722];
                var luminance = rgb(hex).reduce(function (sum, channel, i) {
                    var c = channel / 255;
                    c = c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                    return sum + weights[i] * c;
                }, 0);
                return 1.05 / (luminance + 0.05) >= 4.5;
            }

            function syncCustom() {
                var d = customRadio.dataset;
                d.colorDark = darken(primaryInput.value, 0.55);
                d.colorHover = darken(primaryInput.value, 0.25);
                d.colorMain = primaryInput.value;
                d.colorAccent = accentInput.value;

                var label = form.querySelector('label[for="theme_custom"]');
                label.querySelector('[data-swatch="dark"]').style.background = d.colorDark;
                label.querySelector('[data-swatch="main"]').style.background = d.colorMain;
                label.querySelector('[data-swatch="accent"]').style.background = d.colorAccent;

                [primaryInput, accentInput].forEach(function (input) {
                    form.querySelector('[data-hex-for="' + input.id + '"]').textContent = input.value;
                    form.querySelector('[data-contrast-warning="' + input.id + '"]').hidden = readableOnWhite(input.value);
                });
            }

            // Setting the theme's CSS variables on the preview box makes the
            // real app styles (buttons, links, checkboxes, badges) render in it.
            function render() {
                var selected = form.querySelector('input[name="theme_preset"]:checked');
                customPanel.hidden = !selected || selected.value !== 'custom';
                if (!selected) {
                    return;
                }
                var d = selected.dataset;
                preview.style.setProperty('--color-primary-900', d.colorDark);
                preview.style.setProperty('--color-primary-800', d.colorHover);
                preview.style.setProperty('--color-primary-700', d.colorMain);
                preview.style.setProperty('--color-primary-rgb', rgb(d.colorMain).join(', '));
                preview.style.setProperty('--color-primary-800-rgb', rgb(d.colorHover).join(', '));
                preview.style.setProperty('--color-accent-500', d.colorAccent);
                preview.style.setProperty('--color-accent-rgb', rgb(d.colorAccent).join(', '));
            }

            form.querySelectorAll('input[name="theme_preset"]').forEach(function (radio) {
                radio.addEventListener('change', render);
            });
            [primaryInput, accentInput].forEach(function (input) {
                input.addEventListener('input', function () {
                    syncCustom();
                    render();
                });
            });

            syncCustom();
            render();
        })();
    </script>
<?php endif; ?>

<?php if ($tab === 'general'): ?>
    <script src="/assets/js/media-picker.js"></script>
    <script>
        (function () {
            'use strict';

            var input = document.getElementById('logo_url');
            var preview = document.getElementById('logo-preview');
            var empty = document.getElementById('logo-empty');
            var removeButton = document.getElementById('logo-remove');

            function setLogo(url) {
                input.value = url;
                if (url) {
                    preview.src = url;
                } else {
                    preview.removeAttribute('src');
                }
                preview.hidden = url === '';
                empty.hidden = url !== '';
                removeButton.hidden = url === '';
            }

            document.getElementById('logo-choose').addEventListener('click', function () {
                window.MediaPicker.open({ multiple: false, title: 'Select a Logo', csrfToken: '<?= e(csrf_token()) ?>' }).then(function (items) {
                    if (items.length) {
                        // The picker returns an absolute URL; store the site-relative
                        // path so the logo keeps working if the domain changes.
                        setLogo(new URL(items[0].url, window.location.origin).pathname);
                    }
                });
            });

            removeButton.addEventListener('click', function () {
                setLogo('');
            });
        })();
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../../partials/admin-footer.php'; ?>
