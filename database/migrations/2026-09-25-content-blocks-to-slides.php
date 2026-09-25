<?php

declare(strict_types=1);

/**
 * Migration: inductions.content_blocks, from one flat list of blocks to
 * Section/Lecture slides (docs/application/content_blocks_editor.md #3).
 * No schema change: the column stays JSON; only the shape of its value
 * changes.
 *
 * Before: a flat list where "section" (or legacy "heading") and "lecture"
 * blocks marked where each part began:
 *   [section, image, lecture, image, lecture, gallery, section, ...]
 * After: a list of Section slides, each holding its own blocks and its
 * Lecture slides:
 *   [{id, title, blocks: [image], lectures: [{id, title, blocks: [...]}]}]
 *
 * Nothing is lost:
 * - Every section/lecture keeps its id and title, in the same order.
 * - A section description or lecture body becomes a text block at the top
 *   of its slide.
 * - Every other block (image, gallery, text, alert, iframe, raw_html) moves
 *   unchanged, id included, onto the slide it followed. Blocks before the
 *   first section go on a leading "Introduction" section slide.
 * The script checks this before writing, and checks that the result passes
 * ContentBlockService::validate() unchanged. Inductions already in the
 * slide shape are skipped, so a second run changes nothing. updated_at
 * keeps its historical value.
 *
 * Back up the database and test on a copy before applying (see
 * docs/core/data-protection.md). Without --commit this only prints what
 * would change; --commit writes, in one transaction:
 *   php database/migrations/2026-09-25-content-blocks-to-slides.php
 *   php database/migrations/2026-09-25-content-blocks-to-slides.php --commit
 * (Point DB_DATABASE at the copy to test there first.)
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script is CLI-only.');
}

require __DIR__ . '/../../bootstrap.php';

use App\ContentBlocks\ContentBlockService;
use App\Core\Database;

/**
 * @param array<int, array<string, mixed>> $blocks
 * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>} The
 *     sections, and the ids of the text blocks created from section
 *     descriptions / lecture bodies.
 */
function convertToSlides(array $blocks): array
{
    $sections = [];
    $createdTextIds = [];
    $lectureIndex = null;

    $addText = function (array &$slide, string $html) use (&$createdTextIds): void {
        $html = trim($html);
        if ($html === '') {
            return;
        }
        $id = 'txt_' . bin2hex(random_bytes(4));
        $createdTextIds[] = $id;
        $slide['blocks'][] = ['id' => $id, 'type' => 'text', 'content' => $html];
    };

    $ensureSection = function () use (&$sections, &$lectureIndex): void {
        if ($sections === []) {
            $sections[] = ['id' => 'sec_' . bin2hex(random_bytes(4)), 'title' => 'Introduction', 'blocks' => [], 'lectures' => []];
            $lectureIndex = null;
        }
    };

    foreach ($blocks as $block) {
        $type = (string) ($block['type'] ?? '');

        if ($type === 'section' || $type === 'heading') {
            $title = trim((string) ($type === 'section' ? ($block['title'] ?? '') : ($block['text'] ?? '')));
            $sections[] = ['id' => (string) $block['id'], 'title' => $title !== '' ? $title : 'Untitled Section', 'blocks' => [], 'lectures' => []];
            $lectureIndex = null;
            $addText($sections[array_key_last($sections)], (string) ($block['description'] ?? ''));
            continue;
        }

        $ensureSection();
        $section = &$sections[array_key_last($sections)];

        if ($type === 'lecture') {
            $title = trim((string) ($block['title'] ?? ''));
            $section['lectures'][] = ['id' => (string) $block['id'], 'title' => $title !== '' ? $title : 'Untitled Lecture', 'blocks' => []];
            $lectureIndex = array_key_last($section['lectures']);
            $addText($section['lectures'][$lectureIndex], (string) ($block['content'] ?? ''));
            unset($section);
            continue;
        }

        // Text blocks from the first, plain-text editor stored "text", not
        // "content". Carry the same words over as the HTML it rendered.
        if ($type === 'text' && !array_key_exists('content', $block)) {
            $block = ['id' => $block['id'], 'type' => 'text', 'content' => '<p>' . nl2br(htmlspecialchars((string) ($block['text'] ?? ''), ENT_QUOTES, 'UTF-8')) . '</p>'];
        }

        if ($lectureIndex === null) {
            $section['blocks'][] = $block;
        } else {
            $section['lectures'][$lectureIndex]['blocks'][] = $block;
        }
        unset($section);
    }

    return [$sections, $createdTextIds];
}

/**
 * Titles, bodies and other blocks, in reading order, from the old flat list.
 *
 * @param array<int, array<string, mixed>> $blocks
 * @return array{titles: array<int, string>, bodies: array<int, string>, blocks: array<int, mixed>}
 */
function legacyContent(array $blocks): array
{
    $content = ['titles' => [], 'bodies' => [], 'blocks' => []];

    foreach ($blocks as $block) {
        $type = (string) ($block['type'] ?? '');
        if ($type === 'section' || $type === 'heading' || $type === 'lecture') {
            $title = $type === 'heading' ? ($block['text'] ?? '') : ($block['title'] ?? '');
            $content['titles'][] = $block['id'] . ' ' . trim((string) $title);
            $body = trim((string) ($block['description'] ?? $block['content'] ?? ''));
            if ($body !== '') {
                $content['bodies'][] = $body;
            }
            continue;
        }
        if ($type === 'text' && !array_key_exists('content', $block)) {
            $block = ['id' => $block['id'], 'type' => 'text', 'content' => '<p>' . nl2br(htmlspecialchars((string) ($block['text'] ?? ''), ENT_QUOTES, 'UTF-8')) . '</p>'];
        }
        $content['blocks'][] = canonical($block);
    }

    return $content;
}

/**
 * The same three lists, read back from the slides.
 *
 * @param array<int, array<string, mixed>> $sections
 * @param array<int, string> $createdTextIds
 * @return array{titles: array<int, string>, bodies: array<int, string>, blocks: array<int, mixed>}
 */
function slideContent(array $sections, array $createdTextIds): array
{
    $content = ['titles' => [], 'bodies' => [], 'blocks' => []];

    $readSlide = function (array $slide) use (&$content, $createdTextIds): void {
        $content['titles'][] = $slide['id'] . ' ' . $slide['title'];
        foreach ($slide['blocks'] as $block) {
            if (in_array($block['id'], $createdTextIds, true)) {
                $content['bodies'][] = $block['content'];
            } else {
                $content['blocks'][] = canonical($block);
            }
        }
    };

    foreach ($sections as $section) {
        $readSlide($section);
        foreach ($section['lectures'] as $lecture) {
            $readSlide($lecture);
        }
    }

    return $content;
}

/** Sorts object keys recursively, so equal data compares equal whatever its key order. */
function canonical(mixed $value): mixed
{
    if (!is_array($value)) {
        return $value;
    }
    if (!array_is_list($value)) {
        ksort($value);
    }

    return array_map('canonical', $value);
}

/**
 * The migration's "fake" section (blocks before the first section) has
 * no title in the old data, so it's left out of the title comparison.
 *
 * @param array<int, array<string, mixed>> $blocks
 */
function hasContentBeforeFirstSection(array $blocks): bool
{
    $first = $blocks[0]['type'] ?? null;

    return $blocks !== [] && $first !== 'section' && $first !== 'heading';
}

$commit = in_array('--commit', $argv, true);
$db = Database::connection();
$validator = new ContentBlockService();
$databaseName = $db->query('SELECT DATABASE()')->fetchColumn();

echo 'Database: ' . $databaseName . ($commit ? ' (COMMIT)' : ' (dry run)') . PHP_EOL;

$rows = $db->query('SELECT id, title, content_blocks FROM inductions ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$updates = [];

foreach ($rows as $row) {
    $label = '#' . $row['id'] . ' ' . $row['title'];
    $blocks = json_decode((string) $row['content_blocks'], true);

    if (!is_array($blocks) || !array_is_list($blocks)) {
        fwrite(STDERR, $label . ': content_blocks is not a JSON list. Nothing written.' . PHP_EOL);
        exit(1);
    }

    $isLegacy = false;
    foreach ($blocks as $block) {
        if (is_array($block) && isset($block['type'])) {
            $isLegacy = true;
            break;
        }
    }

    if (!$isLegacy) {
        echo $label . ': already slides (or empty), skipped.' . PHP_EOL;
        continue;
    }

    [$sections, $createdTextIds] = convertToSlides($blocks);

    // 1. Same titles, bodies and blocks, in the same order.
    $before = legacyContent($blocks);
    $after = slideContent($sections, $createdTextIds);
    if (hasContentBeforeFirstSection($blocks)) {
        array_shift($after['titles']);
    }
    foreach (['titles', 'bodies', 'blocks'] as $part) {
        if ($before[$part] !== $after[$part]) {
            fwrite(STDERR, $label . ': converted ' . $part . ' differ from the original. Nothing written.' . PHP_EOL);
            exit(1);
        }
    }

    // 2. The app accepts the result as-is (drops or changes nothing).
    $json = json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $validated = $validator->validate($json);
    if (is_string($validated) || canonical($validated) !== canonical($sections)) {
        fwrite(STDERR, $label . ': converted content does not pass ContentBlockService::validate() unchanged'
            . (is_string($validated) ? ' (' . $validated . ')' : '') . '. Nothing written.' . PHP_EOL);
        exit(1);
    }

    $lectureCount = array_sum(array_map(fn (array $section): int => count($section['lectures']), $sections));
    echo sprintf(
        '%s: %d blocks -> %d section slides, %d lecture slides, %d blocks (%d text blocks from descriptions/lecture bodies). Verified.',
        $label,
        count($blocks),
        count($sections),
        $lectureCount,
        count($after['blocks']) + count($after['bodies']),
        count($createdTextIds)
    ) . PHP_EOL;

    $updates[(int) $row['id']] = $json;
}

if ($updates === []) {
    echo 'Nothing to migrate.' . PHP_EOL;
    exit(0);
}

if (!$commit) {
    echo 'Dry run: nothing written. Re-run with --commit to apply.' . PHP_EOL;
    exit(0);
}

$db->beginTransaction();
try {
    // Assigning updated_at to itself stops ON UPDATE CURRENT_TIMESTAMP from
    // replacing the induction's real last-edited date with today's.
    $stmt = $db->prepare('UPDATE inductions SET content_blocks = :content_blocks, updated_at = updated_at WHERE id = :id');
    foreach ($updates as $id => $json) {
        $stmt->execute(['content_blocks' => $json, 'id' => $id]);
    }
    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, 'Failed, rolled back: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo 'Committed ' . count($updates) . ' induction(s).' . PHP_EOL;
