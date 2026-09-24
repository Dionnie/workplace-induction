<?php

declare(strict_types=1);

namespace App\Core;

class SiteSettingsService
{
    private const LOGO_PATH_PREFIX = '/assets/uploads/media-library/';

    private SiteSettingsRepository $settings;

    public function __construct()
    {
        $this->settings = new SiteSettingsRepository();
    }

    public function get(): array
    {
        return $this->settings->get();
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function updateGeneral(array $data): array
    {
        $data = [
            'company_name' => trim($data['company_name'] ?? ''),
            'logo_url' => $this->toPath(trim($data['logo_url'] ?? '')),
            'primary_email' => trim($data['primary_email'] ?? ''),
        ];

        $errors = $this->validate($data);
        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->settings->updateGeneral($data);

        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function updateAppearance(array $data): array
    {
        $preset = trim($data['theme_preset'] ?? '');
        $primary = strtolower(trim($data['theme_primary'] ?? ''));
        $accent = strtolower(trim($data['theme_accent'] ?? ''));

        $errors = [];
        if ($preset !== 'custom' && !isset(Theme::PRESETS[$preset])) {
            $errors['theme_preset'] = 'Choose a colour theme.';
        }

        // Custom colours carry white text (header, buttons), so they must be
        // dark enough to read. Kept even when a preset is chosen, so switching
        // back to Custom restores them.
        foreach (['theme_primary' => [$primary, 'Primary'], 'theme_accent' => [$accent, 'Accent']] as $field => [$color, $label]) {
            if ($color === '') {
                if ($preset === 'custom') {
                    $errors[$field] = "Choose a {$label} colour.";
                }
            } elseif (!Theme::isHex($color)) {
                $errors[$field] = "Enter the {$label} colour as a hex code, e.g. #1d4a85.";
            } elseif ($preset === 'custom' && Theme::contrastWithWhite($color) < 4.5) {
                $errors[$field] = "This {$label} colour is too light for white text. Choose a darker shade.";
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->settings->updateAppearance(['theme_preset' => $preset, 'theme_primary' => $primary, 'theme_accent' => $accent]);

        return ['success' => true, 'errors' => []];
    }

    /**
     * The media picker hands back absolute URLs (http://host/assets/...);
     * keep only the path so the logo survives a domain change.
     */
    private function toPath(string $url): string
    {
        if (preg_match('~^https?://~i', $url)) {
            return (string) parse_url($url, PHP_URL_PATH);
        }
        return $url;
    }

    /**
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];

        if ($data['company_name'] === '') {
            $errors['company_name'] = 'Company name is required.';
        } elseif (mb_strlen($data['company_name']) > 150) {
            $errors['company_name'] = 'Company name must be 150 characters or fewer.';
        }

        if ($data['primary_email'] !== '' && !filter_var($data['primary_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['primary_email'] = 'Enter a valid email address.';
        }

        // The logo is always picked from the media library, so anything else
        // (external URLs, other paths) is rejected.
        if ($data['logo_url'] !== ''
            && (!str_starts_with($data['logo_url'], self::LOGO_PATH_PREFIX) || str_contains($data['logo_url'], '..'))) {
            $errors['logo_url'] = 'Choose the logo from the media library.';
        }

        return $errors;
    }
}
