# Exam Blocks Editor

## Purpose

The Exam Blocks editor is the admin "Studio" for building an exam's questions. It shares the Content Blocks Studio's top bar and block editing model (`docs/application/content_blocks_editor.md` §6), so authoring an induction and authoring its exam feel the same. Its canvas differs: an exam is one scrolling list of questions, not slides.

Every question is the same **exam question card** in the Studio, on the inductee's exam page and on the result page (§7, `docs/core/design-system.html#exam-question`), so the Studio is a true preview of the exam.

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

The Content Blocks Studio's top bar, with one scrolling canvas instead of slides:

- **Sticky top bar**: "Exam Details" back link, exam title, an Inactive badge when the exam is inactive, the Saved / Unsaved changes badge, an **Exam Outline** button (drawer on narrower screens), and **Save Changes**.
- **Exam Outline**: every question, numbered, with a warning icon on questions that aren't ready to save. Clicking one scrolls to it (teleport scroll + highlight pulse on its card). Shown as a floating rail beside the questions at 1300px and wider, as an offcanvas drawer otherwise.
- **Canvas**: the exam page's centred 720px column (`.page-narrow.mx-auto`), one question card per question.

---

## 4. Editing

- **One active question at a time**, ringed in primary. Its card shows its fields in place: the question text where the question reads, then each option as its answer row (a radio marks the one correct answer, which turns green with "Correct"; add and remove options, minimum two), then the diagram image (URL or **Browse Library**, the shared Media Library picker) and an explanation shown with the exam result.
- **Every other question previews** as inductees see it on the exam page, plus what only the admin needs: the correct answer (green row, tick and "Correct", as on the result page), the explanation, and any problems that would stop it saving.
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

---

## 7. Exam Page and Result Page (inductee)

`inductee/exams/take.php` (`views/inductee/exams/take.php`, `assets/js/exam-take.js`) and the result it shows after submitting (`views/inductee/exams/result.php`). Both use the Studio's question card in a centred 720px reading column.

**Exam page**

- The page header says how many questions there are and the pass mark.
- Each question is a card: "Question 3 of 20", the question, its diagram, then the options. The eyebrow and question are the fieldset's legend, so screen readers announce them with the options.
- **Each option is a full-width row, and the whole row is the click target** (pointer cursor, hover tint), not just the small radio. The chosen row gets a primary edge and tint; keyboard users move between options with the arrow keys, and the focused row is ringed.
- A bar sticks to the bottom of the screen while scrolling (the slide bar's look): **Cancel**, the answered count ("3 of 20 answered", "All 20 answered"), **Submit Exam**. Every question is required: submitting with one unanswered, the browser takes the inductee to it.
- Leaving the page with answers not yet submitted asks first.

**Result page**

- The pass / fail alert with the score, then every question's card with a Correct / Incorrect badge.
- The inductee's answer and the correct answer are marked in the options themselves: green with a tick for the correct answer, red with a cross for a wrong answer, each with its words ("Your answer", "Correct answer"). An unanswered question says "Not answered."
- The explanation, when there is one, below the options.
