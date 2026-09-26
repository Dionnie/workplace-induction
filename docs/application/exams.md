# Exams

**Application.** An exam is an optional assessment an induction can require (`inductions.md` §2). Its questions are its **Exam Blocks**, built in the Exam Blocks Studio. Passing it issues the induction's compliance record; every submission is kept as an exam attempt (`exam-attempts.md`).

Every question is the same **exam question card** in the Studio, on the inductee's exam page and on the result page (`docs/rules/design-system.html#exam-question`), so the Studio is a true preview of the exam.

Exam Blocks and Content Blocks are separate data (`docs/rules/terminology.md`); only the editing experience is shared (`content-blocks.md` §6).

---

## 1. The Exam Record (`exams`)

| Field | Rules |
| --- | --- |
| Title | Required |
| Description | Optional, for administrators |
| Pass percentage | Required, 1–100. The attempt passes when its score is at least this share of the questions. |
| Status | Active or Inactive (§6) |
| Exam blocks | JSON, the questions (§2), edited in the Studio |

One exam can be used by several inductions. Each attempt records which induction it was for.

---

## 2. Exam Blocks (`exams.exam_blocks`)

A JSON array of questions, in display order. Questions only; there are no other block types.

```json
[
  {
    "id": "blk_q_8f31",
    "type": "question",
    "question": "Where is smoking allowed on site?",
    "diagram_img_url": "",
    "explanation": "Only in the marked smoking area outside the gate.",
    "options": [
      { "id": "opt_1", "text": "In the marked smoking area", "correct": true },
      { "id": "opt_2", "text": "Anywhere outdoors", "correct": false }
    ]
  }
]
```

- Each question has exactly one correct option.
- Question and option ids never change once created: submitted answers are keyed by them. Duplicating a question gives the copy and its options new ids.
- The editor reads and writes exactly this shape, so the exam pages and scoring need no knowledge of the editor.

---

## 3. Studio Layout (`admin/exams/editor.php?id=…`)

Reached from the exam's edit page (**Edit Exam Blocks**); creating an exam opens it straight away. It uses the Content Blocks Studio's top bar, with one scrolling canvas instead of slides:

- **Sticky top bar**: "Exam Details" back link, exam title, an Inactive badge when the exam is inactive, the Saved / Unsaved changes badge, an **Exam Outline** button (drawer on narrower screens), and **Save Changes**.
- **Exam Outline**: every question, numbered, with a warning icon on questions that aren't ready to save. Clicking one scrolls to it and pulses its card. A floating rail beside the questions at 1300px and wider; an offcanvas drawer otherwise.
- **Canvas**: the exam page's centred 720px column (`.page-narrow.mx-auto`), one question card per question.

---

## 4. Editing

- **One active question at a time**, ringed in primary. Its card shows its fields in place: the question text, then each option as its answer row (a radio marks the one correct answer, which turns green with "Correct"; add and remove options, minimum two), then the diagram image (URL or **Browse Library**, `docs/core/media-library.md` §4) and an explanation shown with the result.
- **Every other question previews** as inductees see it, plus what only the administrator needs: the correct answer (green row, tick and "Correct", as on the result page), the explanation, and any problems that would stop it saving.
- **Quick insert** after the active question: **Multiple Choice** (four empty options) or **True / False** (options "True" and "False"). Both create a `question` block.
- **Per-question controls** on hover: move up / down, duplicate, remove (asks first).
- **Saving** is AJAX (`fetch`, CSRF token, JSON response), from the button or `Ctrl`/`Cmd`+`S`. Leaving with unsaved changes asks first.

The question card markup in `exam-editor.js` deliberately duplicates the exam and result views, so editing needs no server round trip. Change them together.

---

## 5. Validation

`ExamService::parseExamBlocks()` is authoritative; `exam-editor.js` repeats the checks only to warn while editing. A save is refused, naming the question's number, when a question:

1. has no question text;
2. has an empty option ("Fill it in or remove it");
3. has fewer than two options;
4. doesn't have exactly one correct answer;
5. has a diagram image URL that isn't a valid web address.

The response includes the question's block id, and the Studio opens that question and scrolls to it. Nothing is silently dropped: what the administrator sees is what is saved.

---

## 6. Exam Status

An exam can't be **active** without questions, so inductees are never given an empty exam:

- A new exam is created **inactive** (the create form has no Status field) and opens in the Studio.
- The edit page refuses Status "Active" while the exam has no questions, and says why.
- The Studio refuses to save an empty question list while the exam is active.

Only active exams can be linked to an induction. If a linked exam is made inactive, inductees can't take it, and so can't complete that induction, until it is active again.

---

## 7. Exam Page and Result Page (inductee)

`inductee/exams/take.php?induction_id=…`, reached from the induction's last slide. Needs a completed profile. Both pages use the question card in a centred 720px reading column.

**Exam page** (`views/inductee/exams/take.php`, `assets/js/exam-take.js`)

- The correct answers are removed before the questions reach the page.
- The page header is centred, with no breadcrumb (the bar's Cancel leaves the page), and says how many questions there are and the pass mark.
- Each question is a card: "Question 3 of 20", the question, its diagram, then the options. The eyebrow and question are the fieldset's legend, so screen readers announce them with the options.
- **Each option is a full-width row, and the whole row is the click target** (pointer cursor, hover tint). The chosen row gets a primary edge and tint; keyboard users move between options with the arrow keys, and the focused row is ringed.
- A bar sticks to the bottom of the screen (the slide bar's look): **Cancel**, the answered count ("3 of 20 answered", "All 20 answered"), **Submit Exam**. Every question is required: submitting with one unanswered takes the inductee to it.
- Leaving with answers not yet submitted asks first.

**Scoring** (`ExamService::score()`, server-side only): one point per question whose chosen option is the correct one. Passed when `score / total × 100 ≥ pass percentage`. The attempt is stored whatever the result, and a pass issues compliance (`inductions.md` §2).

**Result page** (`views/inductee/exams/result.php`)

- The pass / fail alert with the score, then every question's card with a Correct / Incorrect badge.
- The inductee's answer and the correct answer are marked in the options: green with a tick for the correct answer, red with a cross for a wrong answer, each with its words ("Your answer", "Correct answer"). An unanswered question says "Not answered."
- The explanation, when there is one, below the options.

---

## 8. Admin: Exams (`/admin/exams/`)

- **List**: title, number of questions, pass %, status, created; search and a status filter. Rows link to Edit.
- **Add Exam**: title, description, pass percentage; created inactive, then the Studio (§6).
- **Edit**: the same fields plus Status, and **Edit Exam Blocks**.
- **Delete Exam** is in the Danger Zone. An exam used by inductions or with attempts can only be deleted with **cascade**, which unlinks it from those inductions (they are kept, without an exam), deletes its attempts, and clears the attempt link on any compliance record that cited one. Compliance records themselves are kept.

---

## 9. Files

| File | Role |
| --- | --- |
| `app/Exam/ExamService.php` | Validation, exam blocks parsing, scoring, delete |
| `app/Exam/ExamRepository.php` | SQL |
| `admin/exams/`, `views/admin/exams/` | Admin list, create, edit, delete; Studio page and its AJAX save |
| `assets/js/exam-editor.js` | The Studio |
| `inductee/exams/take.php`, `views/inductee/exams/take.php`, `result.php`, `assets/js/exam-take.js` | Exam and result pages |
