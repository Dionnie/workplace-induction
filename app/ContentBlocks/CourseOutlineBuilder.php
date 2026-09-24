<?php

declare(strict_types=1);

namespace App\ContentBlocks;

/**
 * Builds the 2-level Section > Lecture outline used by the course structure
 * sidebar (docs/application/content_blocks_editor.md #8). Every "section"
 * (or legacy "heading") block starts a new node; every following "lecture"
 * block becomes a nested item under it. Every other block type (text,
 * alert, image, gallery, iframe, raw_html) is canvas content, not a
 * navigable outline item, so it never appears here — the outline is
 * deliberately kept to these two structural levels only.
 *
 * This is a deliberate, independent duplicate of the equivalent JS outline
 * builder in the Studio editor (see course-editor.js) rather than a shared
 * network round trip — the same pattern this project already uses for
 * course-editor.js/exam-editor.js as parallel client/server implementations.
 */
class CourseOutlineBuilder
{
    /**
     * @param array<int, array<string, mixed>> $blocks
     * @return array<int, array{id: string, title: string, items: array<int, array{id: string, type: string, label: string}>}>
     */
    public function build(array $blocks): array
    {
        $sections = [];
        $currentIndex = null;
        $sectionNumber = 0;

        foreach ($blocks as $block) {
            $type = (string) ($block['type'] ?? '');

            if ($type === BlockTypes::SECTION || $type === BlockTypes::LEGACY_HEADING) {
                $sectionNumber++;
                $title = $type === BlockTypes::SECTION
                    ? trim((string) ($block['title'] ?? ''))
                    : trim((string) ($block['text'] ?? ''));

                $sections[] = [
                    'id' => (string) ($block['id'] ?? ''),
                    'title' => $title !== '' ? $title : ('Section ' . $sectionNumber),
                    'items' => [],
                ];
                $currentIndex = array_key_last($sections);
                continue;
            }

            if ($type !== BlockTypes::LECTURE) {
                continue;
            }

            // A lecture block before any section exists still needs
            // somewhere to live in the outline — it gets an implicit
            // leading section rather than being dropped or left unindented.
            if ($currentIndex === null) {
                $sections[] = [
                    'id' => 'section-overview',
                    'title' => 'Section 1: Course Overview',
                    'items' => [],
                ];
                $currentIndex = array_key_last($sections);
            }

            $title = trim((string) ($block['title'] ?? ''));
            $sections[$currentIndex]['items'][] = [
                'id' => (string) ($block['id'] ?? ''),
                'type' => BlockTypes::LECTURE,
                'label' => $title !== '' ? $title : 'Lecture',
            ];
        }

        return $sections;
    }
}
