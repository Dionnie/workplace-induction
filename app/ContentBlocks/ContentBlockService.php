<?php

declare(strict_types=1);

namespace App\ContentBlocks;

/**
 * Validates and normalizes the content_blocks JSON stored on an induction:
 * a list of Section slides, each holding its own content blocks and its
 * Lecture slides. See docs/application/content-blocks.md #3 for the
 * shape.
 */
class ContentBlockService
{
    private const INVALID = 'Content contains invalid slides or blocks.';

    private const ALLOWED_TAGS = [
        'p', 'strong', 'b', 'em', 'i', 'u', 's', 'a', 'ul', 'ol', 'li', 'br', 'blockquote',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'code', 'pre', 'hr',
    ];

    /**
     * Disallowed tags whose content must be discarded along with the tag
     * itself, rather than unwrapped, since their text content is either
     * executable (script/style) or not meaningful as visible text.
     */
    private const STRIP_ENTIRELY_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'form',
        'input', 'button', 'meta', 'head', 'noscript', 'template', 'link',
    ];

    /**
     * @return array<int, array<string, mixed>>|string The validated sections,
     *     or an error message when the input can't be saved.
     */
    public function validate(string $json): array|string
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || !array_is_list($decoded)) {
            return self::INVALID;
        }

        $sections = [];

        foreach ($decoded as $section) {
            $slide = $this->validateSlide($section);
            if (is_string($slide)) {
                return $slide;
            }

            $lectures = $section['lectures'] ?? [];
            if (!is_array($lectures) || !array_is_list($lectures)) {
                return self::INVALID;
            }

            // Lectures are the second and last level: any "lectures" key on
            // a lecture is ignored, since validateSlide() never copies it.
            $slide['lectures'] = [];
            foreach ($lectures as $lecture) {
                $lectureSlide = $this->validateSlide($lecture);
                if (is_string($lectureSlide)) {
                    return $lectureSlide;
                }
                $slide['lectures'][] = $lectureSlide;
            }

            $sections[] = $slide;
        }

        return $sections;
    }

    /**
     * @return array{id: string, title: string, blocks: array<int, array<string, mixed>>}|string
     */
    private function validateSlide(mixed $slide): array|string
    {
        if (!is_array($slide) || !isset($slide['id']) || !is_array($slide['blocks'] ?? null)) {
            return self::INVALID;
        }

        // Titles are required rather than dropped-when-empty: dropping the
        // slide would silently discard every block on it.
        $title = trim((string) ($slide['title'] ?? ''));
        if ($title === '') {
            return 'Every slide needs a title.';
        }

        $blocks = $this->validateBlocks($slide['blocks']);
        if ($blocks === null) {
            return self::INVALID;
        }

        return ['id' => (string) $slide['id'], 'title' => $title, 'blocks' => $blocks];
    }

    /**
     * @param array<mixed> $decoded
     * @return array<int, array<string, mixed>>|null Null when any block is malformed.
     */
    private function validateBlocks(array $decoded): ?array
    {
        $blocks = [];

        foreach ($decoded as $block) {
            if (!is_array($block) || !isset($block['type'], $block['id'])) {
                return null;
            }

            $type = (string) $block['type'];
            $id = (string) $block['id'];

            $result = match ($type) {
                BlockTypes::TEXT => $this->validateText($id, $block),
                BlockTypes::IMAGE => $this->validateImage($id, $block),
                BlockTypes::ALERT => $this->validateAlert($id, $block),
                BlockTypes::IFRAME => $this->validateIframe($id, $block),
                BlockTypes::RAW_HTML => $this->validateRawHtml($id, $block),
                BlockTypes::GALLERY => $this->validateGallery($id, $block),
                default => false,
            };

            if ($result === false) {
                return null;
            }

            if ($result !== null) {
                $blocks[] = $result;
            }
        }

        return $blocks;
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateText(string $id, array $block): ?array
    {
        $content = $this->sanitizeRichHtml((string) ($block['content'] ?? ''));
        if ($content === '') {
            return null;
        }

        return ['id' => $id, 'type' => BlockTypes::TEXT, 'content' => $content];
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateImage(string $id, array $block): ?array
    {
        $url = trim((string) ($block['url'] ?? ''));
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return [
            'id' => $id,
            'type' => BlockTypes::IMAGE,
            'url' => $url,
            'caption' => trim((string) ($block['caption'] ?? '')),
            'width' => $this->oneOf((string) ($block['width'] ?? ''), BlockTypes::IMAGE_WIDTHS, 'content'),
            'align' => $this->oneOf((string) ($block['align'] ?? ''), BlockTypes::IMAGE_ALIGNS, 'center'),
            'shape' => $this->oneOf((string) ($block['shape'] ?? ''), BlockTypes::IMAGE_SHAPES, 'as-is'),
            'aspect' => $this->oneOf((string) ($block['aspect'] ?? ''), BlockTypes::IMAGE_ASPECTS, '4-3'),
        ];
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateAlert(string $id, array $block): ?array
    {
        $title = trim((string) ($block['title'] ?? ''));
        $content = $this->sanitizeRichHtml((string) ($block['content'] ?? ''));
        if ($title === '' && $content === '') {
            return null;
        }

        return [
            'id' => $id,
            'type' => BlockTypes::ALERT,
            'variant' => $this->oneOf((string) ($block['variant'] ?? ''), BlockTypes::ALERT_VARIANTS, 'info'),
            'title' => $title,
            'content' => $content,
        ];
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateIframe(string $id, array $block): ?array
    {
        $url = $this->normalizeIframeUrl(trim((string) ($block['url'] ?? '')));
        if ($url === '' || !str_starts_with(strtolower($url), 'https://') || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return [
            'id' => $id,
            'type' => BlockTypes::IFRAME,
            'url' => $url,
            'aspect_ratio' => $this->oneOf((string) ($block['aspect_ratio'] ?? ''), BlockTypes::IFRAME_ASPECT_RATIOS, '16:9'),
            'caption' => trim((string) ($block['caption'] ?? '')),
        ];
    }

    /**
     * A plain YouTube watch/share/shorts URL cannot be used as an <iframe>
     * src — YouTube blocks it from framing. Rewrites recognized YouTube URL
     * shapes (including the share-link "youtu.be/ID?si=..." format) to the
     * dedicated embed URL; anything else (Vimeo, other providers, an
     * already-embed URL) passes through unchanged. This is the authoritative
     * copy — course-editor.js mirrors it client-side only for instant
     * preview feedback while typing.
     */
    private function normalizeIframeUrl(string $url): string
    {
        $patterns = [
            '#^https?://(?:www\.)?youtu\.be/([A-Za-z0-9_-]{11})#i',
            '#^https?://(?:www\.)?youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})#i',
            '#^https?://(?:www\.)?youtube\.com/shorts/([A-Za-z0-9_-]{11})#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches) === 1) {
                return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
            }
        }

        return $url;
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateRawHtml(string $id, array $block): ?array
    {
        $content = trim((string) ($block['content'] ?? ''));
        if ($content === '') {
            return null;
        }

        return ['id' => $id, 'type' => BlockTypes::RAW_HTML, 'content' => $content];
    }

    /**
     * Groups small/related images into a Bootstrap-grid row (2/3/4 columns,
     * wrapping into further rows automatically) so they don't each take a
     * full-width block. An image entry needs its own "id" (client-generated,
     * like a block id) so the Studio editor can edit/remove one row without
     * disturbing the others.
     *
     * @param array<string, mixed> $block
     */
    private function validateGallery(string $id, array $block): ?array
    {
        $images = [];
        foreach ((array) ($block['images'] ?? []) as $image) {
            if (!is_array($image)) {
                continue;
            }

            $imageId = trim((string) ($image['id'] ?? ''));
            $url = trim((string) ($image['url'] ?? ''));
            if ($imageId === '' || $url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $images[] = [
                'id' => $imageId,
                'url' => $url,
                'caption' => trim((string) ($image['caption'] ?? '')),
            ];
        }

        if ($images === []) {
            return null;
        }

        return [
            'id' => $id,
            'type' => BlockTypes::GALLERY,
            'columns' => $this->oneOfInt((int) ($block['columns'] ?? 0), BlockTypes::GALLERY_COLUMNS, 3),
            'images' => $images,
        ];
    }

    /**
     * @param array<int, string> $allowed
     */
    private function oneOf(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    /**
     * @param array<int, int> $allowed
     */
    private function oneOfInt(int $value, array $allowed, int $default): int
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    /**
     * Strips any tag not in the allow-list (unwrapping its content) and any
     * attribute not explicitly permitted. Client-side sanitization of the
     * same rich-text fields is not a trust boundary, so this must be
     * re-applied here regardless of what the request claims to have already
     * cleaned.
     */
    private function sanitizeRichHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $root = $document->getElementsByTagName('div')->item(0);
        if ($root === null) {
            return '';
        }

        $this->sanitizeNode($root);

        $result = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function sanitizeNode(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }

            if (!$child instanceof \DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::STRIP_ENTIRELY_TAGS, true)) {
                $node->removeChild($child);
                continue;
            }

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes ?? []) as $attribute) {
                $name = strtolower($attribute->name);
                $keep = ($tag === 'a' && in_array($name, ['href', 'title', 'target', 'rel'], true))
                    || (in_array($tag, ['td', 'th'], true) && in_array($name, ['colspan', 'rowspan'], true));

                if (!$keep) {
                    $child->removeAttribute($attribute->name);
                }
            }

            if ($tag === 'a' && $child->hasAttribute('href')) {
                $href = trim($child->getAttribute('href'));
                if ($href === '' || !preg_match('#^https?://#i', $href)) {
                    $child->removeAttribute('href');
                } else {
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            }

            $this->sanitizeNode($child);
        }
    }
}
