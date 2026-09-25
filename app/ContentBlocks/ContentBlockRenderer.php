<?php

declare(strict_types=1);

namespace App\ContentBlocks;

/**
 * Renders a single validated content block to its read-only HTML fragment.
 * Used by the inductee-facing induction page, so admin-authored blocks of
 * every type render identically wherever this class is called from. The
 * slide around the blocks (title, outline, navigation) is page markup in
 * views/inductee/inductions/show.php, not a block.
 *
 * Block "content" fields are already sanitized rich HTML
 * (ContentBlockService) and are printed unescaped by design; every other
 * field is user-authored plain text and is always escaped.
 *
 * Every block is wrapped in a .content-block element so block spacing
 * applies identically to this page and to the Studio editor's own .cb-block
 * markup. Images load lazily: every slide is in the page, but only the one
 * on screen should download its images (slide-viewer.js prefetches the next).
 */
class ContentBlockRenderer
{
    private const ALERT_ICONS = [
        'info' => 'bi-info-circle',
        'warning' => 'bi-exclamation-triangle',
        'danger' => 'bi-exclamation-octagon',
        'success' => 'bi-check-circle',
    ];

    /**
     * @param array<string, mixed> $block
     */
    public function render(array $block): string
    {
        $type = (string) ($block['type'] ?? '');

        $body = match ($type) {
            BlockTypes::TEXT => $this->renderText($block),
            BlockTypes::ALERT => $this->renderAlert($block),
            BlockTypes::IMAGE => $this->renderImage($block),
            BlockTypes::IFRAME => $this->renderIframe($block),
            BlockTypes::RAW_HTML => $this->renderRawHtml($block),
            BlockTypes::GALLERY => $this->renderGallery($block),
            default => null,
        };

        if ($body === null) {
            return '';
        }

        return '<div class="content-block">' . $body . '</div>';
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     */
    public function renderAll(array $blocks): string
    {
        $html = '';
        foreach ($blocks as $block) {
            $html .= $this->render($block);
        }

        return $html;
    }

    /** @param array<string, mixed> $block */
    private function renderText(array $block): string
    {
        return '<div class="content-block-inner">' . (string) ($block['content'] ?? '') . '</div>';
    }

    /** @param array<string, mixed> $block */
    private function renderAlert(array $block): string
    {
        $variant = in_array($block['variant'] ?? '', BlockTypes::ALERT_VARIANTS, true) ? $block['variant'] : 'info';
        $title = e((string) ($block['title'] ?? ''));
        $content = (string) ($block['content'] ?? '');
        $icon = self::ALERT_ICONS[$variant];

        $html = '<div class="content-block-inner"><div class="cb-alert-box alert alert-' . $variant . ' d-flex mb-0">'
            . '<i class="bi ' . $icon . ' flex-shrink-0 me-2 mt-1" aria-hidden="true"></i><div>';
        if ($title !== '') {
            $html .= '<div class="alert-heading fw-semibold mb-1">' . $title . '</div>';
        }
        $html .= $content . '</div></div></div>';

        return $html;
    }

    /** @param array<string, mixed> $block */
    private function renderImage(array $block): string
    {
        $url = e((string) ($block['url'] ?? ''));
        $caption = e((string) ($block['caption'] ?? ''));
        $width = in_array($block['width'] ?? '', BlockTypes::IMAGE_WIDTHS, true) ? $block['width'] : 'content';
        $align = in_array($block['align'] ?? '', BlockTypes::IMAGE_ALIGNS, true) ? $block['align'] : 'center';
        $shape = in_array($block['shape'] ?? '', BlockTypes::IMAGE_SHAPES, true) ? $block['shape'] : 'as-is';
        $aspect = in_array($block['aspect'] ?? '', BlockTypes::IMAGE_ASPECTS, true) ? $block['aspect'] : '4-3';

        $html = '<figure class="cb-image cb-align-' . $align . ' cb-w-' . $width . ' mb-0">'
            . '<img src="' . $url . '" alt="' . $caption . '" loading="lazy" class="img-fluid cb-shape-' . $shape . ' cb-aspect-' . $aspect . '">';
        if ($caption !== '') {
            $html .= '<figcaption class="text-muted small mt-1">' . $caption . '</figcaption>';
        }
        $html .= '</figure>';

        return $html;
    }

    /** @param array<string, mixed> $block */
    private function renderIframe(array $block): string
    {
        $url = e((string) ($block['url'] ?? ''));
        $caption = e((string) ($block['caption'] ?? ''));
        $ratioClass = ($block['aspect_ratio'] ?? '16:9') === '4:3' ? 'ratio-4x3' : 'ratio-16x9';

        $html = '<div class="ratio ' . $ratioClass . '">'
            . '<iframe src="' . $url . '" allowfullscreen loading="lazy"></iframe></div>';
        if ($caption !== '') {
            $html .= '<div class="text-muted small mt-1">' . $caption . '</div>';
        }

        return $html;
    }

    /** @param array<string, mixed> $block */
    private function renderRawHtml(array $block): string
    {
        return (string) ($block['content'] ?? '');
    }

    /**
     * A Bootstrap grid row (docs/rules/design.md §2, "Bootstrap
     * First") of `.col` figures, using `row-cols-*` so images wrap into
     * further rows automatically once there are more than one row's worth —
     * no custom CSS grid needed.
     *
     * @param array<string, mixed> $block
     */
    private function renderGallery(array $block): string
    {
        $images = is_array($block['images'] ?? null) ? $block['images'] : [];
        if ($images === []) {
            return '';
        }

        $columns = in_array($block['columns'] ?? null, BlockTypes::GALLERY_COLUMNS, true) ? (int) $block['columns'] : 3;

        $html = '<div class="row ' . $this->galleryRowClasses($columns) . ' g-3">';
        foreach ($images as $image) {
            $url = e((string) ($image['url'] ?? ''));
            $caption = e((string) ($image['caption'] ?? ''));

            $html .= '<div class="col"><figure class="mb-0"><img src="' . $url . '" alt="' . $caption . '" loading="lazy" class="img-fluid rounded">';
            if ($caption !== '') {
                $html .= '<figcaption class="text-muted small mt-1">' . $caption . '</figcaption>';
            }
            $html .= '</figure></div>';
        }
        $html .= '</div>';

        return $html;
    }

    private function galleryRowClasses(int $columns): string
    {
        return match ($columns) {
            2 => 'row-cols-1 row-cols-sm-2',
            4 => 'row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4',
            default => 'row-cols-1 row-cols-sm-2 row-cols-md-3',
        };
    }
}
