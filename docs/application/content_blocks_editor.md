# Content Blocks Editor

## Purpose

An induction's learning material is shown as **slides**, one at a time, like a
slide deck: Section slides, each followed by its Lecture slides. Every slide
has a title and holds its own content blocks (text, alerts, images,
galleries, videos, raw HTML).

This replaced one long scrolling page with Section/Lecture heading blocks and
a scroll-to ("teleport") outline, which was too long a read for inductees.
The block editor itself (vertical blocks, one active block at a time) did
not change; it now edits the blocks of one slide at a time.

---

## 1. Scope & Relationship to Exam Blocks

This spec covers the admin Studio editor and the inductee-facing rendering
of an induction's `content_blocks`. **Exam Blocks** are a separate data
concept (`docs/application/terminology.md`) with their own Studio
(`docs/application/exam_blocks_editor.md`). That Studio shares this one's top
bar and block editing model but keeps a single scrolling canvas, with its own
outline rail and teleport scroll.

---

## 2. Terminology

| Term | Meaning |
| --- | --- |
| **Content Blocks** | The induction's learning material as a whole (`inductions.content_blocks`, `ContentBlockService`, `app/ContentBlocks/`), and the blocks on each slide. |
| **Slide** | One screen of content: a title plus its content blocks. |
| **Section slide** | A first-level slide. It opens a part of the induction and holds that part's Lecture slides. |
| **Lecture slide** | A second-level slide, inside a Section. |

"Section" and "Lecture" name the two slide levels, not a rename of Content
Blocks: say "this induction has 6 sections and 40 lectures", not "6
modules" or "40 lessons". There are exactly two levels; a Lecture never
holds slides.

---

## 3. Data Model

`inductions.content_blocks` is a `JSON NOT NULL` column (`database/schema.sql`).
Its value is a list of Section slides. Each has its own blocks and a list of
Lecture slides, which have blocks but no further levels. Order is array
position; there is no `order` field.

```json
[
  {
    "id": "sec_000_e01a46",
    "title": "Getting Started",
    "blocks": [
      { "id": "img_002_460577", "type": "image", "url": "https://…/cover.webp", "caption": "", "width": "smaller", "align": "center", "shape": "as-is", "aspect": "natural" }
    ],
    "lectures": [
      {
        "id": "lec_001_2835cd",
        "title": "Introduction",
        "blocks": [
          { "id": "txt_4f1c9a2b", "type": "text", "content": "<p>Stark Food Systems started operations in 2016…</p>" },
          { "id": "img_003_c17366", "type": "image", "url": "https://…/team.webp", "caption": "", "width": "content", "align": "center", "shape": "as-is", "aspect": "4-3" }
        ]
      }
    ]
  }
]
```

The slide **title is not a block**. It is a field of the slide, always shown
at the top of the slide.

### 3.1 Block types

`App\ContentBlocks\BlockTypes` lists them; `course-editor.js` mirrors the lists.

| Type | Label | Fields | Notes |
| --- | --- | --- | --- |
| `text` | Text | `id, type, content` | Rich HTML, sanitized (§7). |
| `alert` | Alert | `id, type, variant, title, content` | `variant` is `info\|warning\|danger\|success`, the semantic colours of `docs/core/ui-guidelines.md`. |
| `image` | Image | `id, type, url, caption, width, align, shape, aspect` | `width`: `full` (1024px canvas), `content` (700px), `small` (50%), `smaller` (35%); every width but `full` becomes 100% below 576px. `align`: `left\|center\|right`. `shape`: `rounded\|square\|circle\|as-is`. `aspect`: `natural\|16-9\|4-3\|1-1`. |
| `gallery` | Gallery | `id, type, columns, images: [{id, url, caption}]` | `columns` `2\|3\|4` at desktop width; a Bootstrap `row row-cols-*` grid that wraps into more rows. |
| `iframe` | Video | `id, type, url, aspect_ratio, caption` | `https` only. YouTube watch/share/shorts URLs are rewritten to `youtube-nocookie.com/embed/…`. `aspect_ratio` `16:9\|4:3`. |
| `raw_html` | Raw HTML | `id, type, content` | Admin-authored HTML, deliberately **not** sanitized: it exists to allow markup nothing else supports. Same trust boundary as image URLs (admins only). If non-admins ever author content, lock this down first. |

### 3.2 Validation (`ContentBlockService::validate()`)

`validate()` returns the cleaned sections, or an error message string that the
Studio shows as-is.

- The value must be a JSON list of sections; each slide needs an `id` and a
  `blocks` list, and `lectures` (sections only) must be a list. Otherwise:
  *"Content contains invalid slides or blocks."*
- Every slide needs a non-empty title: *"Every slide needs a title."* A
  titleless slide is refused rather than dropped, since dropping it would
  silently discard its blocks. The Studio checks this before saving and
  opens the slide that needs a title.
- A block with an unknown `type` or no `id` refuses the whole save.
- Empty blocks are dropped, not refused: a text block with no content, an
  alert with neither title nor content, an image or video without a valid
  URL, a gallery with no valid images.
- Enum fields (`variant`, `width`, `align`, `shape`, `aspect`, `columns`,
  `aspect_ratio`) fall back to their defaults when missing or invalid; they
  never refuse the save.

---

## 4. Reading Canvas

The same widths in the Studio and on the inductee page, so the Studio is a
true preview:

- The slide area (`.cb-slides-stage`) is at most **1024px**. Media blocks
  (image, gallery, video, raw HTML) use that width.
- Text-based blocks (text, alert) sit in `.content-block-inner`, a centered
  **700px** reading column.
- The slide header (eyebrow and title) sits on the same **700px** column
  (`.cb-slide-header`), so it lines up with the text below it.
- Each slide card has a brand band along its top and bottom edge
  (`.cb-slide-band`): the Appearance primary colour with a slanted accent
  block, mirrored at the bottom. Decorative only (`aria-hidden`).
- Blocks on a slide are spaced close together (`.content-block`,
  `.cb-block`); they are never nested or indented.

---

## 5. Inductee View (`inductee/inductions/show.php`)

- **One slide at a time.** Every slide is in the page as a card
  (`.cb-slide`); `assets/js/slide-viewer.js` shows one and hides the rest.
  The first slide is shown by the server, so the page never flashes the
  whole induction.
- **Slide header**: a small eyebrow saying where the slide sits ("Section 2"
  on a Section slide, the section's title on a Lecture slide), then the
  title as an `h2`.
- **No page header.** The slide starts at the top of the page. The
  induction's title sits at the top of the sidebar with an **About** button,
  which opens a modal with the title and description. The top navbar's
  Dashboard link is the way back.
- **Outline sidebar** on the left: sections with their lectures nested
  under them, the current slide highlighted. Clicking an item opens that
  slide. From `lg` up it is a sticky column that scrolls on its own; below
  `lg` the same markup is a Bootstrap `.offcanvas-lg` drawer, opened from
  the slide bar. About, from the drawer, closes the drawer before opening
  the modal.
- **Slide bar** (`.cb-slide-nav`) fixed to the bottom of the screen and
  centred on the slide: Previous, "Slide N of M" ("N of M" below `sm`),
  Next. It stays in the same place on every slide, however short or long,
  so Next never moves under the reader's finger. Left/Right arrow keys also
  move between slides (not while the About modal is open).
- **Finishing**: on the last slide, Next is replaced by the page's finish
  action, **Start Exam** / **Retry Exam** (exam-based induction) or **Mark
  as Complete** (no exam). A currently compliant inductee has no finish
  action; Next is just disabled.
- **The URL hash** (`#slide-<id>`) follows the current slide, so a reload or
  a shared link opens the same slide. Slides deliberately have no matching
  element `id`, so the browser never jumps to them on load.
- **Media**: images load lazily, so only the slide on screen downloads its
  images, and the next slide's images are fetched ahead of time. Leaving a
  slide reloads its video frames, which stops playback.
- **No content**: the usual page header (breadcrumb, title, description),
  "This induction has no content yet." with the finish action below it,
  and no sidebar or slide bar.

---

## 6. Studio Editor (`admin/inductions/editor.php?id=<id>`)

Linked from the induction's edit page (**Edit Content Blocks**).

- **Top bar** (sticky): back to Induction Details, the induction title, the
  Saved / Unsaved changes badge, **Save Changes**.
- **Outline sidebar**: **Add Section** and **Add Lecture**, then the same
  two-level outline as the inductee page, updated live as titles are typed;
  untitled slides show as *Untitled Section* / *Untitled Lecture*. Add
  Section inserts a new section after the current one (after its
  lectures); Add Lecture inserts a lecture right after the current slide,
  in the current section. Below `lg` the sidebar is a drawer.
- **Slide toolbar** above the slide: a Section / Lecture badge, move up /
  move down, and **Delete Section** / **Delete Lecture**. Moving a section
  takes its lectures with it; moving a lecture past the first or last
  lecture of its section moves it into the neighbouring section. Delete
  asks first, and says when a section's lectures go with it. Outline rows
  only navigate; they have no delete controls.
- **The slide**: the same card as the inductee page. The title is an
  input styled as the real heading; below it, the slide's blocks.
- **Blocks: single-active-block WYSIWYG editing.** Only the clicked block
  shows its fields (with the rich-text toolbar for text and alerts); every
  other block shows its inductee-facing preview. Clicking outside a block
  returns it to preview. Blocks have hover controls (move up/down,
  duplicate, remove). A quick-insert bar (Text, Alert, Image, Gallery,
  Video, Raw HTML) follows the active block, so new blocks go right after
  it. Image and gallery blocks can pick files from the Media Library
  (`assets/js/media-picker.js`); a plain URL always works too.
- **Slide bar** fixed to the bottom of the screen, as on the inductee page:
  Previous, "Slide N of M", Next (and the outline button below `lg`). The
  current slide is kept in the URL hash.
- **Saving** is AJAX (`fetch` with the CSRF token and the whole sections
  list as JSON) to the same route, which returns JSON. The button, or
  `Ctrl`/`Cmd`+`S`. Leaving with unsaved changes asks first.

The block preview markup in `course-editor.js` is a deliberate client-side
duplicate of `ContentBlockRenderer`, like `exam-editor.js` and
`ExamService`, so editing needs no server round trip. Change both together.

---

## 7. Rich Text & Sanitization

`text.content` and `alert.content` are `contenteditable` fields with a small
toolbar (bold, italic, underline, strikethrough, paragraph, quote, bullet
and numbered lists, clear formatting).

Client-side cleaning is not a trust boundary. `ContentBlockService`
re-sanitizes every rich-text field before saving, with a hand-written
allow-list (no Composer dependency):

```text
p, strong, b, em, i, u, s, a, ul, ol, li, br, blockquote,
h1, h2, h3, h4, h5, h6, table, thead, tbody, tr, th, td, code, pre, hr
```

`script, style, iframe, object, embed, svg, form, input, button, meta, head,
noscript, template, link` are removed with their content; other tags are
unwrapped. Only `a` (`href`, `title`, `target`, `rel`) and `td`/`th`
(`colspan`, `rowspan`) keep attributes; links must be `http(s)` and open in
a new tab with `rel="noopener noreferrer"`. `raw_html` is the one exception
(§3.1).

---

## 8. Files

| File | Role |
| --- | --- |
| `app/ContentBlocks/BlockTypes.php` | Block type names and allowed enum values. |
| `app/ContentBlocks/ContentBlockService.php` | Validates and sanitizes the sections JSON (§3.2, §7). |
| `app/ContentBlocks/ContentBlockRenderer.php` | Renders one block to its inductee-facing HTML. The slide around it is view markup. |
| `app/Induction/InductionService.php` | `updateContentBlocks()`: validates through `ContentBlockService`, then saves. |
| `admin/inductions/editor.php`, `views/admin/inductions/editor.php` | Studio page and its AJAX save endpoint. |
| `assets/js/course-editor.js` | Studio: slides, outline, block editing, save. |
| `inductee/inductions/show.php`, `views/inductee/inductions/show.php` | Inductee slide view. |
| `assets/js/slide-viewer.js` | Inductee slide navigation (read-only). |
| `assets/css/app.css` §9 | `.content-canvas`, `.content-block*`, `.cb-slides-*`, `.cb-slide*`, `.cb-*` editor classes (listed in `docs/core/design-system.html`). |

---

## 9. Migration From the Flat Block List

Before slides, `content_blocks` was one flat list in which `section` (or the
older `heading`) and `lecture` blocks marked where each part began.
`database/migrations/2026-09-25-content-blocks-to-slides.php` converts that
shape without losing anything:

- every section and lecture keeps its id and title, in order;
- a section description or lecture body becomes a text block at the top of
  its slide;
- every other block moves, unchanged, onto the slide it followed (blocks
  before the first section go on a leading "Introduction" section);
- old plain-text `text` blocks become the same words as HTML.

It checks that titles, bodies and blocks match the original, and that the
result passes `ContentBlockService::validate()` unchanged, before writing
anything. It skips inductions already in the slide shape and keeps
`updated_at`. It runs as a dry run unless given `--commit`. Back up and test
on a copy first (`docs/core/data-protection.md`). The application reads only
the slide shape, so run it wherever old data exists.
