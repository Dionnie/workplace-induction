<?php

declare(strict_types=1);

function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require dirname(__DIR__, 2) . '/config/app.php';
    }
    return $config;
}

/**
 * White-label branding (company name, logo, primary email) managed from
 * Admin > Settings > General, falling back to config/app.php.
 *
 * @return array{company_name: string, logo_url: ?string, primary_email: ?string}
 */
function site_settings(): array
{
    static $settings = null;
    if ($settings === null) {
        $settings = (new \App\Core\SiteSettingsService())->get();
    }
    return $settings;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * $url when it is a root-relative path on this site, otherwise null. Guards
 * redirect_to so a crafted link can't send users to another site: rejects
 * absolute and protocol-relative URLs, backslashes (browsers read "/\" as
 * "//") and control characters (browsers drop tabs and newlines).
 */
function safe_redirect_path(mixed $url): ?string
{
    if (!is_string($url) || !str_starts_with($url, '/') || str_starts_with($url, '//')
        || preg_match('/[\\\\\x00-\x1F\x7F]/', $url)) {
        return null;
    }
    return $url;
}

/**
 * "?redirect_to=..." carrying $path on to the next page, or "" without one.
 */
function redirect_to_query(?string $path): string
{
    return $path !== null ? '?redirect_to=' . rawurlencode($path) : '';
}

/**
 * Absolute URL for a root-relative path. Uses config/app.php 'url' when set,
 * which is needed wherever there is no request host (e.g. cron-sent emails).
 */
function public_url(string $path): string
{
    $baseUrl = rtrim((string) (app_config()['url'] ?? ''), '/');
    if ($baseUrl !== '') {
        return $baseUrl . $path;
    }

    $scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . $path;
}

/**
 * admin@ the site's domain: what a blank Primary Email or administrator To
 * uses. Settings shows it as those fields' placeholder, so the fallback is
 * visible, not hidden (docs/core/settings.md §1, §3).
 */
function default_email(): string
{
    $host = (string) parse_url(public_url('/'), PHP_URL_HOST);
    return 'admin@' . preg_replace('/^www\./', '', $host);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || $token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Your form submission has expired. Please go back and try again.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['old'][$key] ?? $default);
}

/**
 * Like old(), but returns the raw, unescaped value. Use this when the caller
 * will escape it itself (e.g. building a $values array for a shared form partial).
 */
function old_raw(string $key, string $default = ''): string
{
    return $_SESSION['old'][$key] ?? $default;
}

function set_old(array $input): void
{
    $_SESSION['old'] = $input;
}

function clear_old(): void
{
    unset($_SESSION['old']);
}

function set_errors(array $errors): void
{
    $_SESSION['errors'] = $errors;
}

/**
 * @return array<string, string>
 */
function get_errors(): array
{
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    return $errors;
}

function error_for(array $errors, string $field): ?string
{
    return $errors[$field] ?? null;
}

/**
 * Status badge. Each status has one colour everywhere in the application
 * (docs/rules/design-system.html#badges), so views never pick badge colours
 * themselves.
 */
function status_badge(string $status, ?string $label = null): string
{
    $variant = match ($status) {
        'active', 'compliant', 'passed', 'correct', 'saved', 'complete' => 'success',
        'expiring', 'pending', 'unsaved', 'incomplete' => 'warning',
        'expired', 'failed', 'revoked', 'suspended', 'incorrect' => 'danger',
        default => 'secondary', // inactive, superseded, not_started
    };

    return '<span class="badge text-bg-' . $variant . '">'
        . e($label ?? ucwords(str_replace('_', ' ', $status)))
        . '</span>';
}

/**
 * <style> block applying the chosen colour theme. views/partials/head.php
 * outputs it after assets/css/app.css on every page.
 */
function theme_style_tag(): string
{
    return '<style>' . \App\Core\Theme::css(site_settings()) . '</style>';
}
