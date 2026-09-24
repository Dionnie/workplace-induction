# Content Blocks Editor

## Purpose & Status Legend

This document is the **target-state specification** for the Content Blocks
editor — a centered, WYSIWYG course-authoring canvas with a sticky course
structure sidebar, teleport scroll, and a mobile outline drawer, built on
top of the existing modular JSON block architecture.

It documents the intended design as a blueprint for implementation, while
staying explicit about what already exists. Every claim below carries one
of these tags:

- **[BUILT]** — implemented today. A file/line pointer is given.
- **[PLANNED]** — target design described in this spec. Not implemented.
- **[PARTIAL]** — some part of the behavior exists, but not all of it.

Sections that mix built and planned behavior tag individual fields/bullets
rather than the whole section.

---

## Implementation Status Summary

| Capability | Status | Current Implementation |
| --- | --- | --- |
| Block JSON architecture (array of `{id, type, ...}`, order = array position) | **BUILT** | `InductionService::parseContentBlocks()`, `assets/js/content-blocks.js` |
| Block types `heading`, `text`, `image` | **BUILT** | `InductionService::BLOCK_TYPES` |
| Block types `section`, `lecture`, `alert`, `iframe`, `raw_html` | **PLANNED** | do not exist |
| Rich text (contenteditable + allow-listed HTML) | **PLANNED** | `text`/`heading` are always escaped plain text (`e()`, `nl2br()`) |
| Add / remove / reorder blocks | **BUILT** | `assets/js/content-blocks.js` (`addBlock`, DOM-order swap) |
| Single-active-block WYSIWYG editing (inline preview for inactive blocks) | **PLANNED** | today every block is an always-open form card |
| Dedicated `app/ContentBlocks/` feature folder | **PLANNED** | block logic lives inline in `InductionService` |
| Fullscreen Studio editor route | **PLANNED** | editor is an embedded section of `views/admin/inductions/_form.php` |
| Centered reading canvas (700px text / 1024px media) | **PLANNED** | admin form has a 720px wrapper around the *whole form*; inductee view has no width cap |
| Course structure sidebar (Section/Lecture outline) | **PLANNED** | no sidebar/outline exists anywhere |
| Teleport scroll + `.teleport-highlight-pulse` | **PLANNED** | no scroll-to, no such class, anywhere |
| Mobile offcanvas drawer | **PLANNED** | Bootstrap JS bundle isn't loaded anywhere (prerequisite) |
| Media library (browse/upload) | **PLANNED**, phased | image blocks take a plain URL today |

---

## 1. Scope & Relationship to Exam Blocks

This spec covers the admin authoring experience and the inductee-facing
rendering of an induction's `content_blocks`. It does not cover **Exam
Blocks** (`assets/js/exam-blocks.js`, `views/admin/exams/_form.php`) — a
separate, parallel system for exam questions, per
`docs/application/terminology.md`.

Exam Blocks are cited only as an existing precedent for the modular
block-editor UI pattern already established in this codebase (a container
+ hidden input + serialize-on-save). They are a different data concept and
must stay separate — a reference implementation this spec draws from (see
§9) explicitly removed a `quiz` block type from its own content-block
system in favor of a dedicated exam system, which is exactly the separation
this project's terminology already enforces.

---

## 2. Terminology Resolution

`docs/application/terminology.md` requires:

| Concept | Required Term | Do Not Use |
| --- | --- | --- |
| Induction learning material | Content Blocks | Lessons, Modules, Sections |

This rule forbids using "Sections" as a substitute name for the **Content
Blocks** data concept. This spec does not do that. "Section" and "Lecture"
here name two specific **block types** within Content Blocks (the h2- and
h3-equivalent structural blocks), not a rename of the overall concept.

- The data column, the service layer, and the feature folder are still
  named around **Content Blocks** (`content_blocks`, `ContentBlockService`,
  `app/ContentBlocks/`).
- "This induction has X content blocks" — correct.
- "This induction has X sections" — avoid; say "X content blocks,
  including X sections" if a count is needed, or just refer to the
  Section/Lecture block types by name where precision matters (e.g. in the
  admin UI's block-type badges: "Section", "Lecture", "Text", "Alert",
  "Image", "Video", "Raw HTML" — these are type labels, not renames of the
  whole feature).

---

## 3. Feature Folder Architecture

**Status: PLANNED.** All content-block domain logic moves into a dedicated
feature folder, `app/ContentBlocks/`, matching this project's existing
per-domain layout (`app/Induction/`, `app/Admin/`, `app/Compliance/`,
`app/Notification/`, `app/Core/`).

```text
app/ContentBlocks/
- ContentBlockService.php     Validates and normalizes block JSON (replaces
                               InductionService::parseContentBlocks()).
                               Normalizes legacy shapes on read (e.g. a
                               persisted "heading" block becomes "section")
                               instead of requiring a destructive DB
                               migration.
- ContentBlockRenderer.php    One shared function mapping a block array to
                               its rendered HTML fragment. Used by the
                               inductee view AND the Studio editor's
                               inactive-block preview, so the two surfaces
                               cannot drift out of sync.
- CourseOutlineBuilder.php    Builds the 2-level Section/Lecture outline
                               tree server-side, for the inductee sidebar.
- BlockTypes.php              A single lookup table of block type
                               metadata (label, icon) shared by the Studio
                               editor and the outline builder.
```

`app/Induction/InductionService.php` keeps owning the induction row itself
(title, code, exam link, status, validity) and delegates block-JSON
handling to `ContentBlockService` rather than parsing it inline. This
avoids duplicating block logic between the two folders.

---

## 4. Data Model — Block JSON Shapes

### 4.1 Storage & Envelope

**Status: BUILT.** `inductions.content_blocks` is a native `JSON NOT NULL`
column (`database/schema.sql`) — no separate table. Blocks are a flat JSON
array; order is the array position. There is no `order` field — this is a
deliberate simplification to keep, not a gap to fix.

### 4.2 Block Types

| Type | Status | Fields | Notes |
| --- | --- | --- | --- |
| `heading` | **BUILT**, superseded | `id, type, text` | Legacy. On read, normalized to `section` (`title = text`, `description = ''`). The editor never writes this type again. |
| `text` | **BUILT** plain text; **PLANNED** rich | `id, type, content` | Today: `text` field, escaped + `nl2br`. Planned: `content` field, allow-listed rich HTML (§5). |
| `image` | **BUILT** basic; **PLANNED** rich | `id, type, src1, caption1, width, align, shape, aspect` | Today: `url, caption`, renders `img-fluid` with no layout control. Planned: adds `width` (`100\|75\|50\|35`), `align` (`left\|center\|right`), `shape` (`rounded\|square\|circle\|as-is`), `aspect` (`natural\|16-9\|4-3\|1-1`). |
| `section` | **PLANNED** (new) | `id, type, title, description` | h2-equivalent. `description` is rich HTML, optional. |
| `lecture` | **PLANNED** (new) | `id, type, title, content` | h3-equivalent. `content` is rich HTML. |
| `alert` | **PLANNED** (new) | `id, type, variant, title, content` | `variant` is `info\|warning\|danger\|success` — the same four values `docs/core/ui-guidelines.md` already mandates for semantic state colors, so this block type reuses that system rather than inventing new colors. |
| `iframe` | **PLANNED** (new) | `id, type, url, aspect_ratio, caption` | `aspect_ratio` is `16:9\|4:3`. YouTube watch/short URLs are auto-rewritten to a `youtube-nocookie.com` embed URL at render/save time. |
| `raw_html` | **PLANNED** (new) | `id, type, content` | Arbitrary admin-authored HTML, intentionally **not** run through the rich-text allow-list (§5.2) — its purpose is to allow markup nothing else here supports. Trust boundary: identical to today's `image.url`, admin-authored and never sanitized against a hostile admin. If this system ever admits non-admin authors, `raw_html` must be locked down first. |
| `gallery` | **BUILT** | `id, type, columns, images: [{id, url, caption}]` | Groups small/related images into one block instead of each taking a full-width `image` block. `columns` (`2\|3\|4`) is the desktop column count; renders as a Bootstrap `row row-cols-*` grid that wraps extra images into further rows automatically — no custom CSS grid. Each `images[]` entry needs its own `url` (dropped if missing/invalid, same rule as `image.url`); the whole block is dropped if it ends up with zero valid images. |

### 4.3 Current Shape — BUILT example

```json
[
  { "id": "blk_a1b2c3d4", "type": "heading", "text": "Site Safety Overview" },
  { "id": "blk_e5f6g7h8", "type": "text", "text": "Welcome to the induction. Please read carefully." },
  { "id": "blk_i9j0k1l2", "type": "image", "url": "https://example.com/ppe.jpg", "caption": "Required PPE" }
]
```

### 4.4 Target Shape — PLANNED example

```json
[
  {
    "id": "sec_1a2b3c4d",
    "type": "section",
    "title": "Section 1: Site Safety Overview",
    "description": "<p>What you need to know before entering the site.</p>"
  },
  {
    "id": "lec_5e6f7g8h",
    "type": "lecture",
    "title": "Personal Protective Equipment",
    "content": "<p>High-vis vests and steel-cap boots are <strong>mandatory</strong> at all times.</p>"
  },
  {
    "id": "blk_9i0j1k2l",
    "type": "alert",
    "variant": "warning",
    "title": "Attention Required",
    "content": "<p>Report any damaged PPE to your supervisor immediately.</p>"
  },
  {
    "id": "blk_3m4n5o6p",
    "type": "image",
    "src1": "https://example.com/ppe.jpg",
    "caption1": "Figure 1: Required PPE",
    "width": "75",
    "align": "center",
    "shape": "rounded",
    "aspect": "natural"
  },
  {
    "id": "blk_7q8r9s0t",
    "type": "iframe",
    "url": "https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ",
    "aspect_ratio": "16:9",
    "caption": "Site induction walkthrough video"
  }
]
```

### 4.5 Validation Rules — PLANNED, `ContentBlockService`

- `BLOCK_TYPES` becomes `['section', 'lecture', 'text', 'alert', 'image', 'iframe', 'raw_html']`; `heading` accepted on read only (normalized, never written).
- Unknown `type` or missing `id` still rejects the whole submission with
  `'Content contains invalid blocks.'` — unchanged from today.
- `section`/`lecture`: `title` required (non-empty, trimmed); `description`/
  `content` optional, run through the server-side rich-text sanitizer
  (§5.2) before persisting.
- `text`: `content` optional after sanitizing; an empty-after-sanitize
  block is dropped, matching today's drop-not-reject behavior for blank
  `text`/`heading` blocks.
- `alert`: `variant` validated against the four allowed values (default
  `info` if missing/invalid — never rejects the submission for a bad
  variant); `title` optional; `content` sanitized like `text`.
- `image`: `src1` required and must pass `FILTER_VALIDATE_URL` (dropped if
  missing/invalid, same as today); `width`/`align`/`shape`/`aspect`
  validated against their allowed value lists, defaulting to `50` / `left`
  / `rounded` / `natural` if missing or invalid.
- `iframe`: `url` required, must pass `FILTER_VALIDATE_URL` **and** use the
  `https` scheme (dropped otherwise); `aspect_ratio` defaults to `16:9` if
  missing/invalid.
- `raw_html`: `content` stored as-is (trimmed only) — no allow-list
  applied, per §4.2's trust-boundary note.

---

## 5. Rich Text & Sanitization

**Status: PLANNED.** This is the one part of the spec with real security
weight: today, `text`/`heading` content is always `e()`-escaped and can
never contain markup. Moving to rich text changes that trust boundary, and
needs a deliberate answer, not an assumption.

### 5.1 Client-side editing

`section.description`, `lecture.content`, `text.content`, and
`alert.content` become `contenteditable` regions with a small formatting
toolbar (bold, italic, underline, strikethrough, paragraph, blockquote,
bullet/numbered list, clear formatting) driven by `document.execCommand`.

Pasted content is cleaned client-side before insertion: parse the clipboard
HTML, strip `script, style, link, iframe, object, embed, svg, button, form,
input, meta, head, noscript, template` elements entirely, strip every
attribute except link (`href`, `title`, `target`, `rel`) and table
(`colspan`, `rowspan`) essentials, and keep only this tag allow-list:

```text
p, strong, b, em, i, u, s, a, ul, ol, li, br, blockquote,
h1, h2, h3, h4, h5, h6, table, thead, tbody, tr, th, td, code, pre, hr
```

Bare URLs in pasted plain text are auto-linked with
`rel="noopener noreferrer" target="_blank"`.

### 5.2 Server-side sanitization (required, not optional)

Client-side sanitization is not a trust boundary — a request can always
bypass the browser entirely. `ContentBlockService` must re-apply an
equivalent allow-list to every rich-text field (`section.description`,
`lecture.content`, `text.content`, `alert.content`) before persisting,
using the same tag list as §5.1.

This should be a small hand-written PHP allow-list function (strip
disallowed tags/attributes), **not** a new Composer dependency such as
HTMLPurifier — consistent with this project's "avoid unnecessary
dependencies" rule. `raw_html.content` is the deliberate exception and is
never run through this filter (§4.2).

---

## 6. Layout & Reading Canvas

**Status: PLANNED.** The main canvas is locked to a maximum width and
horizontally centered, identically in the Studio editor and the inductee
view, so the editor is a true WYSIWYG preview of what inductees see:

- 700px max-width for text-based blocks: `section`, `lecture`, `text`,
  `alert`.
- 1024px max-width for media blocks: `image`, `gallery`, `iframe`.
- Technique: an outer `.content-canvas` wrapper (1024px, centered) holds
  every block; text-based blocks get an additional inner
  `.content-block-inner` wrapper capped at 700px and centered; media
  blocks render directly at the full 1024px.
- The existing admin form's `style="max-width: 720px;"`
  (`views/admin/inductions/create.php`, `edit.php`) wraps the *whole
  form* (title/code/exam fields included) — leave that as-is; the new
  700px/1024px rule applies only inside the block canvas.

**Flat canvas, no nested containers.** Per explicit product direction, a
`section`'s following blocks (`lecture`, `text`, `image`, `alert`,
`iframe`, `raw_html`) are **not** wrapped in an indented or bordered
container under their section. The canvas stays a single flat vertical
stack — consistent with the original "strict, vertically stacked block
constraints" principle. Relatedness is conveyed through spacing alone: a
larger top margin before a `section` block (a "chapter break") versus
normal spacing between blocks within a section. This rule applies only to
the content canvas — the sidebar outline (§8) is a separate navigation UI
and does use indentation there, since a nested list is the standard
pattern for a nav tree, not a "nested block."

---

## 7. Admin Editor — Studio Experience

**Status: PLANNED.** The block editor moves out of the embedded
`_form.php` card list into its own dedicated route, e.g.
`admin/inductions/editor.php?id=<id>`, linked from
`admin/inductions/edit.php` ("Edit Content Blocks"). This "Studio" page:

- Uses no admin nav chrome (distraction-free canvas), with its own sticky
  top bar: back-to-induction link, induction title, an unsaved/saved
  status badge, a "Course Outline" button that opens the mobile offcanvas
  (§9), and a Save button.
- **Single-active-block editing.** Only the clicked block renders its full
  edit UI (fields + rich-text toolbar); every other block renders its
  read-only, inductee-facing preview markup inline in the same canvas via
  `ContentBlockRenderer` (§3) — this is what makes the editor a true
  WYSIWYG surface rather than a stacked form builder. Clicking outside any
  block deselects it back to preview mode.
- A quick-insert toolbar (add Section / Lecture / Text / Alert / Image /
  Video / Raw HTML) follows the active (or last-focused) block, so new
  blocks insert immediately after the current position rather than always
  appending at the end.
- Move up/down, duplicate, and remove controls per block, matching today's
  add/remove/reorder pattern but operating on the in-memory block array
  instead of raw DOM-node order.
- **Saving is AJAX**, not a native form POST: `fetch()` with `FormData`
  (CSRF token + the serialized block JSON) to a small save endpoint (§11),
  returning JSON. This is the first JSON-returning endpoint in this
  project; keep it to a plain
  `header('Content-Type: application/json'); echo json_encode(...);` —
  no new response-helper abstraction is needed for one endpoint.
  `Ctrl`/`Cmd`+`S` triggers the same save action.

---

## 8. Course Structure Sidebar

**Status: PLANNED.**

- **Automatic parsing, Section/Lecture only**: walk the block list in
  order. Every `section` block starts a new outline node (labeled with its
  `title`). Every following `lecture` block becomes a nested item under the
  current section (labeled with its `title`). Every other block type
  (`text`, `alert`, `image`, `gallery`, `iframe`, `raw_html`) is canvas
  content and never appears in the outline — a deliberate simplification so
  the outline stays a clean chapter/sub-chapter map rather than a full
  content index.
- **Orphan lectures**: if a `lecture` block appears before any `section`
  block exists, the outline builder synthesizes an implicit leading node
  ("Section 1: Course Overview") so it's never left unindented or dropped
  from the outline.
- **Two levels only** — Section, then its direct child Lecture items. No
  deeper nesting.
- **Sticky positioning**: `position: sticky` on desktop, visible at the
  Bootstrap `lg` breakpoint and up (`d-none d-lg-block`), offset below the
  page header.
- **Computed twice, deliberately**: live in JavaScript in the Studio
  editor (recomputed on every add/remove/reorder/edit), and once
  server-side in PHP (`CourseOutlineBuilder`, §3) for the inductee view.
  This duplication is accepted, matching the existing
  `content-blocks.js`/`exam-blocks.js` parallel-file convention in this
  codebase — it is not a defect to consolidate later.
- **Mobile offcanvas**: below `lg`, the sidebar is replaced by Bootstrap's
  native `.offcanvas.offcanvas-start` component, opened by a topbar button
  and a floating "dock" button that hides while the offcanvas is open.
  Bootstrap's JS bundle is loaded on every layout by
  `views/partials/scripts.php` (the Studio loads it itself). This uses
  Bootstrap's own component, so it does not violate
  `docs/core/ui-guidelines.md`'s "no other UI framework" rule.

---

## 9. Teleport Scroll

**Status: PLANNED.**

- Every `section`/`lecture` (and any block with a sidebar entry) gets a
  stable DOM id, e.g. `id="block-<blockId>"`.
- Clicking a sidebar item calls
  `element.scrollIntoView({ behavior: 'smooth', block: 'center' })`.
- Sticky-header offset is handled via CSS `scroll-margin-top` on the
  heading elements, not manual offset math.
- The highlight pulse is re-triggered on every click, including repeat
  clicks on the same item, using remove → forced reflow → re-add:

  ```js
  el.classList.remove('teleport-highlight-pulse');
  void el.offsetWidth; // force reflow so the animation restarts
  el.classList.add('teleport-highlight-pulse');
  ```

- CSS (added to `assets/css/app.css`, the project's only stylesheet),
  reusing the existing `--color-warm-500` token rather than introducing a
  new color, per `docs/core/ui-guidelines.md`'s "don't create
  near-identical new values":

  ```css
  @keyframes teleport-highlight-pulse {
    0%   { background-color: var(--color-warm-500); }
    100% { background-color: transparent; }
  }
  .teleport-highlight-pulse {
    animation: teleport-highlight-pulse 1.2s ease-out;
  }
  ```

- On viewports narrower than 768px, teleport scroll also closes the
  offcanvas drawer.
- "Single continuous canvas" is a constraint to *preserve*, not new work:
  teleport scroll is pure in-page scrolling and must never trigger a page
  reload. The app already renders one page per induction with no
  pagination, so this invariant already holds.

---

## 10. Inductee-Facing Rendering

**Status: PLANNED,** mirrors §7's read-only rendering via the shared
`ContentBlockRenderer` (§3) so the admin preview and the inductee page can
never diverge:

- All 8 block types render (`section` → `<h2 id="block-...">` + optional
  description; `lecture` → `<h3 id="block-...">` + content; `text` →
  sanitized rich HTML; `alert` → a Bootstrap `alert-<variant>` box; `image`
  → `<figure>` honoring `width`/`align`/`shape`/`aspect`; `gallery` → a
  Bootstrap `row row-cols-*` grid of `<figure>`s; `iframe` → a responsive
  `.ratio .ratio-16x9`/`.ratio-4x3` wrapper; `raw_html` → printed as-is).
- Canvas width rule (§6) and flat-spacing rule apply identically to the
  admin Studio and the inductee page.
- Sidebar (§8) is read-only here — no quick-insert toolbar, no
  click-to-edit.
- Empty-state text is unchanged: **[BUILT]** "This induction has no
  content yet." (`views/inductee/inductions/show.php:49`). New rule: the
  sidebar renders nothing (no empty shell) when there are zero
  `section`/`lecture` blocks.

---

## 11. Media Library

**Status: PLANNED, phased.** A browse/search/drag-drop upload modal
backing the `image` block's source field, in the spirit of the reference
implementation this spec draws from. This is materially new scope beyond
the block editor itself — file storage, upload validation, and an assets
listing endpoint — so it is placed in the last implementation milestone
(§13). Until then, and always as a fallback, the `image` block's `src1`
field accepts a plain URL directly, exactly as `image.url` works today.

---

## 12. CSS / JS Reference

| Asset | Status | Notes |
| --- | --- | --- |
| `assets/css/app.css` additions (canvas width, `.teleport-highlight-pulse`, sidebar/offcanvas layout) | **PLANNED** | Stays in the project's single existing stylesheet per `docs/core/ui-guidelines.md`'s "keep custom CSS small and purposeful," unless it grows large enough to justify a split. |
| `assets/js/course-editor.js` | **PLANNED** (new file) | Studio editor logic: block CRUD, single-active-block rendering, rich-text toolbar, quick-insert, AJAX save. |
| `assets/js/course-outline.js` | **PLANNED** (new file) | Small, inductee-page-only: teleport scroll + offcanvas wiring. No editing capability — kept separate from `course-editor.js` since the inductee page never edits, consistent with this project's existing narrow-purpose-JS-file convention (only two JS files exist today, both single-purpose). |
| `assets/js/content-blocks.js` | Retired | Superseded by `course-editor.js` once the Studio route ships; not deleted until then. |

---

## 13. Server-Side Changes Checklist

- Add `app/ContentBlocks/` (§3): `ContentBlockService`,
  `ContentBlockRenderer`, `CourseOutlineBuilder`, `BlockTypes`.
- `InductionService` delegates block validation/normalization to
  `ContentBlockService` instead of its own `parseContentBlocks()`.
- New route `admin/inductions/editor.php` (Studio page), following the
  existing `admin/inductions/*.php` pattern: `Auth::requireRole('admin')`,
  load the induction via `InductionService::find()`.
- New AJAX save endpoint (can be the same route handling a POST, or a
  sibling like `admin/inductions/save-blocks.php`):
  `Auth::requireRole('admin')` + `verify_csrf()` + a JSON response.
- Add the Bootstrap JS bundle `<script>` tag to
  `views/partials/admin-footer.php` (and `inductee-footer.php` for the
  inductee-side offcanvas/teleport JS).
- `InductionRepository` and the inductee controller
  (`inductee/inductions/show.php`) need **no changes** — both already
  treat `content_blocks` as an opaque JSON string/array.

---

## 14. Open Design Decisions

1. **Server-side HTML sanitization approach** (§5.2) — recommended: a
   small hand-written allow-list function in `ContentBlockService`
   mirroring the client-side tag list, not a new Composer dependency.
2. **`raw_html` inclusion in v1** — recommended: keep it, admin-authored
   only, explicitly excluded from the sanitizer, with the trust boundary
   documented (§4.2) rather than silently assumed.
3. **Media Library phase timing** (§11) — recommended: after the core
   editor, sidebar, and teleport milestones; the URL-input fallback stays
   available throughout, including after the library ships.
4. **Exact Studio route filename** — recommended:
   `admin/inductions/editor.php?id=`, fitting the existing
   `admin/inductions/*.php` convention. Not load-bearing elsewhere in this
   spec; safe to rename during implementation.
5. **Where media-library server code lives** — recommended: inside
   `app/ContentBlocks/` for now (its only consumer today); split into a
   standalone `app/Media/` later only if another feature needs it too.

---

## 15. Suggested Implementation Milestones

1. `app/ContentBlocks/` service + new data model + legacy `heading` →
   `section` normalization. No UI change yet.
2. Canvas-width CSS (§6) on both the existing admin form and the inductee
   view.
3. Studio route (§7) with single-active-block editing. No sidebar/outline
   yet.
4. Two-level outline sidebar (§8) + teleport scroll (§9), inductee view
   first.
5. Mirror the sidebar/teleport into the Studio editor.
6. Mobile offcanvas (§8), after the Bootstrap JS bundle is added.
7. Media library (§11).

---

## 16. Related Documentation

- `docs/application/terminology.md` — required terms; see §2 for how this
  spec stays compliant.
- `docs/application/induction.md` — has no `content_blocks` JSON example
  today (unlike `exam_blocks`, which has one); consider adding a one-line
  pointer from its `content_blocks` field description to this document.
- `docs/core/ui-guidelines.md` — Bootstrap-only UI framework rule,
  "keep custom CSS small and purposeful" rule, and the semantic
  info/warning/danger/success color system reused by the `alert` block
  type.
- `assets/js/exam-blocks.js` / `views/admin/exams/_form.php` — the
  existing sibling modular block-editor pattern (a different system; see
  §1).
