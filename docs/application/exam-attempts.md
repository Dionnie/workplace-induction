# Exam Attempts

**Application.** An exam attempt is the record of one exam submission: who took which exam, for which induction, their score and whether they passed. It is evidence, kept as history.

---

## 1. The Record (`exam_attempts`)

| Field | Meaning |
| --- | --- |
| `user_id`, `induction_id`, `exam_id` | The inductee, the induction they were completing, and the exam they took |
| `score`, `total_score` | Correct answers, and the number of questions at the time |
| `result` | `passed` or `failed`, against the exam's pass percentage at the time |
| `created_at` | When it was submitted ("Attempted") |

The individual answers aren't stored; the inductee sees them once, on the result page (`exams.md` §7).

---

## 2. Rules

- An attempt is created only by submitting an exam (`InductionService::submitExam()`), and only for an induction that has one. **Never create an attempt for an induction without an exam** (`inductions.md` §2).
- Every submission is kept, passed or failed. There is no limit on retries.
- **Never edit an attempt after submission.** Score and result stay as they were, even if the exam's questions or pass mark change later.
- A passed attempt is linked from the compliance record it produced (`compliance_records.exam_attempt_id`).

---

## 3. Admin: Exam Attempts (`/admin/exam-attempts/`)

- **List**: inductee, induction, exam, score, result, attempted; search, and filters for induction, exam and result. Rows link to View. This is a read-only list; attempts aren't created or edited here.
- **View** (`show.php`): the attempt's values as read-only fields (`docs/rules/design.md` §7), each related record with a View button (inductee, induction, exam).
- **Delete Exam Attempt** is in the Danger Zone, for a genuine mistake. A compliance record that cited the attempt is **kept**, and just loses the link. Deleting a user or an induction with cascade, or an exam with cascade, also deletes their attempts (`docs/core/users.md` §10, `inductions.md` §3, `exams.md` §8).

---

## 4. Files

| File | Role |
| --- | --- |
| `app/Exam/ExamAttemptService.php` | Admin list, find, delete |
| `app/Exam/ExamAttemptRepository.php` | SQL, including creating an attempt and the latest attempt per inductee and induction |
| `admin/exam-attempts/`, `views/admin/exam-attempts/` | List, View, Delete |
