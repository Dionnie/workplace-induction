<?php

declare(strict_types=1);

namespace App\ContentBlocks;

/**
 * Validates and normalizes the content_blocks JSON stored on an induction.
 * See docs/application/content_blocks_editor.md for the block shapes.
 */
class ContentBlockService
{
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
     * @return array<int, array<string, mixed>>|null Null when the input is malformed.
     */
    public function validate(string $json): ?array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return null;
        }

        $blocks = [];

        foreach ($decoded as $block) {
            if (!is_array($block) || !isset($block['type'], $block['id'])) {
                return null;
            }

            $type = (string) $block['type'];
            $id = (string) $block['id'];

            $result = match ($type) {
                BlockTypes::LEGACY_HEADING => $this->validateHeading($id, $block),
                BlockTypes::TEXT => $this->validateText($id, $block),
                BlockTypes::IMAGE => $this->validateImage($id, $block),
                BlockTypes::SECTION => $this->validateSection($id, $block),
                BlockTypes::LECTURE => $this->validateLecture($id, $block),
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
     * Converts legacy block shapes into their current equivalent. Read-time
     * only — callers never re-persist the converted shape as-is, so no
     * destructive migration of existing induction data is required.
     *
     * @param array<int, array<string, mixed>> $blocks
     * @return array<int, array<string, mixed>>
     */
    public function normalize(array $blocks): array
    {
        return array_map(function (array $block): array {
            if (($block['type'] ?? null) === BlockTypes::LEGACY_HEADING) {
                return [
                    'id' => $block['id'],
                    'type' => BlockTypes::SECTION,
                    'title' => $block['text'] ?? '',
                    'description' => '',
                ];
            }

            return $block;
        }, $blocks);
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateHeading(string $id, array $block): ?array
    {
        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return null;
        }

        return ['id' => $id, 'type' => BlockTypes::LEGACY_HEADING, 'text' => $text];
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateText(string $id, array $block): ?array
    {
        // The Studio editor writes rich HTML under "content"; only blocks
        // persisted by the old plain-text editor use "text". Distinguish by
        // field presence rather than by a version flag, so old and new
        // persisted data both keep validating without a migration.
        if (array_key_exists('content', $block)) {
            $content = $this->sanitizeRichHtml((string) ($block['content'] ?? ''));
            if ($content === '') {
                return null;
            }

            return ['id' => $id, 'type' => BlockTypes::TEXT, 'content' => $content];
        }

        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return null;
        }

        return ['id' => $id, 'type' => BlockTypes::TEXT, 'text' => $text];
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
    private function validateSection(string $id, array $block): ?array
    {
        $title = trim((string) ($block['title'] ?? ''));
        if ($title === '') {
            return null;
        }

        return [
            'id' => $id,
            'type' => BlockTypes::SECTION,
            'title' => $title,
            'description' => $this->sanitizeRichHtml((string) ($block['description'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateLecture(string $id, array $block): ?array
    {
        $title = trim((string) ($block['title'] ?? ''));
        if ($title === '') {
            return null;
        }

        return [
            'id' => $id,
            'type' => BlockTypes::LECTURE,
            'title' => $title,
            'content' => $this->sanitizeRichHtml((string) ($block['content'] ?? '')),
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
