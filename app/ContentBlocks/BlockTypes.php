<?php

declare(strict_types=1);

namespace App\ContentBlocks;

/**
 * Content block type names and the allowed values for their enum-like
 * fields. Shared source of truth for ContentBlockService and, once built,
 * the Studio editor and course outline builder.
 */
final class BlockTypes
{
    /** Legacy type, accepted on read/write for backward compatibility. */
    public const LEGACY_HEADING = 'heading';

    public const TEXT = 'text';
    public const IMAGE = 'image';
    public const SECTION = 'section';
    public const LECTURE = 'lecture';
    public const ALERT = 'alert';
    public const IFRAME = 'iframe';
    public const RAW_HTML = 'raw_html';
    public const GALLERY = 'gallery';

    public const ALERT_VARIANTS = ['info', 'warning', 'danger', 'success'];

    /** Number of columns the gallery's images wrap into at desktop width. */
    public const GALLERY_COLUMNS = [2, 3, 4];

    /**
     * "full" = the 1024px media canvas edge to edge; "content" = a fixed
     * 700px matching the text reading width, so the image lines up with
     * paragraph text above/below it; "small"/"smaller" are percentages of
     * the canvas. All but "full" collapse to 100% width below the 576px
     * breakpoint (see .cb-image in app.css) so a "smaller" image never
     * becomes illegibly tiny on a phone.
     */
    public const IMAGE_WIDTHS = ['full', 'content', 'small', 'smaller'];
    public const IMAGE_ALIGNS = ['left', 'center', 'right'];
    public const IMAGE_SHAPES = ['rounded', 'square', 'circle', 'as-is'];
    public const IMAGE_ASPECTS = ['natural', '16-9', '4-3', '1-1'];
    public const IFRAME_ASPECT_RATIOS = ['16:9', '4:3'];
}
