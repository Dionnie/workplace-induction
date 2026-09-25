<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Brand colour presets. Each preset sets the primary shades (700 = buttons
 * and links, 800 = hover, 900 = header) and the accent. A custom theme
 * derives the 800/900 shades from one chosen primary colour.
 */
class Theme
{
    public const DEFAULT_PRESET = 'teal';

    public const PRESETS = [
        'teal' => ['label' => 'Teal', 'primary_900' => '#022a2b', 'primary_800' => '#044647', 'primary_700' => '#0f5d5f', 'accent' => '#a54b17'],
        'navy' => ['label' => 'Navy', 'primary_900' => '#0b1d36', 'primary_800' => '#132f57', 'primary_700' => '#1d4a85', 'accent' => '#b45309'],
        'forest' => ['label' => 'Forest', 'primary_900' => '#0f2618', 'primary_800' => '#183d27', 'primary_700' => '#23593a', 'accent' => '#8a5a00'],
        'charcoal' => ['label' => 'Charcoal', 'primary_900' => '#1a1d21', 'primary_800' => '#2a2f35', 'primary_700' => '#3d444c', 'accent' => '#b4431f'],
        'burgundy' => ['label' => 'Burgundy', 'primary_900' => '#2b0912', 'primary_800' => '#4a0f20', 'primary_700' => '#7a1b34', 'accent' => '#7a5c12'],
        'indigo' => ['label' => 'Indigo', 'primary_900' => '#1b1238', 'primary_800' => '#2b1d5a', 'primary_700' => '#48338c', 'accent' => '#a3470d'],
    ];

    /**
     * Resolved colours for the saved settings.
     *
     * @param array<string, mixed> $site site_settings row
     * @return array{primary_900: string, primary_800: string, primary_700: string, accent: string}
     */
    public static function colors(array $site): array
    {
        $preset = (string) ($site['theme_preset'] ?? self::DEFAULT_PRESET);

        if ($preset === 'custom' && !empty($site['theme_primary']) && !empty($site['theme_accent'])) {
            return self::fromCustom((string) $site['theme_primary'], (string) $site['theme_accent']);
        }

        $colors = self::PRESETS[$preset] ?? self::PRESETS[self::DEFAULT_PRESET];
        unset($colors['label']);
        return $colors;
    }

    /**
     * @return array{primary_900: string, primary_800: string, primary_700: string, accent: string}
     */
    public static function fromCustom(string $primary, string $accent): array
    {
        return [
            'primary_900' => self::darken($primary, 0.55),
            'primary_800' => self::darken($primary, 0.25),
            'primary_700' => strtolower($primary),
            'accent' => strtolower($accent),
        ];
    }

    /**
     * CSS overriding the brand tokens in assets/css/app.css (see
     * docs/rules/design-system.html#colour). Emitted after the stylesheet in
     * every page head by views/partials/head.php.
     */
    public static function css(array $site): string
    {
        $c = self::colors($site);

        return ':root{'
            . "--color-primary-900:{$c['primary_900']};"
            . "--color-primary-800:{$c['primary_800']};"
            . "--color-primary-700:{$c['primary_700']};"
            . '--color-primary-rgb:' . implode(',', self::rgb($c['primary_700'])) . ';'
            . '--color-primary-800-rgb:' . implode(',', self::rgb($c['primary_800'])) . ';'
            . "--color-accent-500:{$c['accent']};"
            . '--color-accent-rgb:' . implode(',', self::rgb($c['accent'])) . ';'
            . '}';
    }

    /**
     * WCAG contrast ratio of white text on the given colour (4.5 or more is
     * readable for normal text).
     */
    public static function contrastWithWhite(string $hex): float
    {
        $luminance = 0.0;
        foreach (self::rgb($hex) as $i => $channel) {
            $c = $channel / 255;
            $c = $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
            $luminance += [0.2126, 0.7152, 0.0722][$i] * $c;
        }

        return 1.05 / ($luminance + 0.05);
    }

    public static function isHex(string $value): bool
    {
        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $value);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function rgb(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    private static function darken(string $hex, float $amount): string
    {
        return '#' . implode('', array_map(
            fn (int $channel): string => sprintf('%02x', (int) round($channel * (1 - $amount))),
            self::rgb($hex)
        ));
    }
}
