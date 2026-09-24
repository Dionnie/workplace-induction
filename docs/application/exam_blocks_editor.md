# Exam Blocks Editor

## Purpose

The Exam Blocks editor is the admin "Studio" for building an exam's questions. It uses the same layout and editing model as the Content Blocks Studio (`docs/application/content_blocks_editor.md` §7–9), so authoring an induction and authoring its exam feel the same.

Exam Blocks and Content Blocks stay separate data concepts (`docs/application/terminology.md`); only the editing experience is shared.

---

## 1. Where It Lives

| Part | File |
| --- | --- |
| Studio page and AJAX save endpoint | `admin/exams/editor.php?id=<exam id>` |
| Studio view | `views/admin/exams/editor.php` |
| Editor script | `assets/js/exam-editor.js` |
| Validation and saving | `App\Exam\ExamService::updateExamBlocks()` / `parseExamBlocks()` |
| Storage | `exams.exam_blocks` (JSON; shape in `docs/application/induction.md`) |

The exam details page (`admin/exams/edit.php`) links to the Studio with **Edit Exam Blocks**. Creating an exam redirects straight to the Studio.

---

## 2. Data

`exam_blocks` holds questions only: a JSON array of `question` blocks, in display order, each with `id`, `question`, `diagram_img_url`, `explanation` and `options` (`id`, `text`, `correct`). The editor reads and writes exactly this shape, so the inductee exam pages (`inductee/exams/take.php`, the result page) and scoring (`ExamService::score()`) need no knowledge of the editor.

Question and option ids never change once created: an inductee's submitted answers are keyed by them. Duplicating a question gives the copy and its options new ids.

---

## 3. Studio Layout

The same shell as the Content Blocks Studio:

- **Sticky top bar**: "Exam Details" back link, exam title, an Inactive badge when the exam is inactive, the Saved / Unsaved changes badge, an **Exam Outline** button (drawer on narrower screens), and **Save Changes**.
- **Exam Outline**: every question, numbered, with a warning icon on questions that aren't ready to save. Clicking one scrolls to it (teleport scroll + highlight pulse). Shown as a floating rail beside the canvas at 1600px and wider, as an offcanvas drawer otherwise.
- **Canvas**: the centered 1024px canvas; questions read at the 700px text width.

---

## 4. Editing

- **One active question at a time.** The active question shows its fields: question text, diagram image (URL or **Browse Library**, the shared Media Library picker), answer options (a radio marks the one correct answer; add and remove options, minimum two), and an explanation shown with the exam result.
- **Every other question previews** as inductees see it on the exam page, plus what only the admin needs: the correct answer (tick and "Correct" badge), the explanation, and any problems that would stop it saving.
- **Quick insert** after the active question: **Multiple Choice** (four empty options) or **True / False** (options "True" and "False"). Both create a `question` block.
- **Per-question controls** on hover: move up / down, duplicate, remove (asks first).
- **Saving** is AJAX (`fetch`, CSRF token, JSON response), from the button or `Ctrl`/`Cmd`+`S`. Leaving the page with unsaved changes asks first.

---

## 5. Validation

`ExamService::parseExamBlocks()` is authoritative; `exam-editor.js` repeats the same checks only to show warnings while editing. A save is refused, with the question's number, when a question:

1. has no question text;
2. has an empty option ("Fill it in or remove it");
3. has fewer than two options;
4. doesn't have exactly one correct answer;
5. has a diagram image URL that isn't a valid web address.

The response includes the question's block id, and the Studio opens that question and scrolls to it.

Nothing is silently dropped: what the admin sees is what is saved.

---

## 6. Exam Status

An exam can't be **active** without questions, so inductees are never given an empty exam:

- A new exam is created **inactive** (the create form has no Status field) and opens in the Studio.
- The details page refuses Status "Active" while the exam has no questions, and says why.
- The Studio refuses to save an empty question list while the exam is active.

Only active exams can be linked to an induction (`views/admin/inductions/_form.php`).
