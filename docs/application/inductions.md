# Inductions

**Application.** An induction is a requirement an inductee must complete: learning material (content blocks) and, optionally, an exam. Completing it issues a compliance record, valid for a set number of months.

> **Induction** = requirement · **Exam** = optional assessment · **Exam Attempt** = evidence of an assessment · **Compliance Record** = issued credential

Related: `content-blocks.md` (the material and its Studio), `exams.md`, `exam-attempts.md`, `compliance.md`.

---

## 1. The Induction Record (`inductions`)

| Field | Rules |
| --- | --- |
| Title | Required |
| Code | Required, unique, stored in capitals; letters, numbers and dashes only (e.g. `SITE-SAFETY`) |
| Description | Optional; shown to inductees on the induction's About modal |
| Exam (`exam_id`) | Optional. Blank: completing the content is enough. Set: the inductee must pass that exam. Only active exams can be chosen. |
| Validity (months) | Required, 1 or more. How long the issued compliance record is valid. |
| Status | Active: shown to inductees. Inactive: hidden from them; existing compliance records are unaffected. |
| Content blocks | JSON, edited in the Studio (`content-blocks.md`) |

---

## 2. Completing an Induction

```text
                       INDUCTION
                (content blocks, optional exam)
                            │
             ┌──────────────┴──────────────┐
          NO EXAM                         EXAM
             │                              │
      Mark as Complete               Exam Attempt ── failed → retry
             │                              │ passed
             └──────────────┬───────────────┘
                            ▼
                   COMPLIANCE RECORD  →  Certificate
```

- **No exam** (`InductionService::completeWithoutExam()`): after the last slide, **Mark as Complete** issues the compliance record. No exam attempt is created. **Never create a fake exam attempt for an induction without an exam.**
- **With an exam** (`InductionService::submitExam()`): every submission is scored and stored as an exam attempt. A pass issues the compliance record, linked to the attempt. A fail issues nothing; the inductee can retry as often as they like.
- Both call `ComplianceService::issue()` (`compliance.md` §3), then fire the `induction_completed` hook, which sends the completion emails (`docs/core/settings.md` §4).
- Both need an active induction and a completed profile (`docs/core/users.md` §9). An exam must also be active: if the linked exam is made inactive, inductees can't take it until it is active again.

---

## 3. Admin: Inductions (`/admin/inductions/`)

- **List**: title, code, validity, status, created; search and a status filter. Rows link to Edit.
- **Add Induction**: the fields above, then straight into the Studio to add content.
- **Edit**: the same fields, and **Edit Content Blocks** to open the Studio. If the linked exam has become inactive, it stays listed as "(inactive)" so saving doesn't unlink it.
- **Delete Induction** is in the Danger Zone. An induction with exam attempts or compliance records can only be deleted with **cascade**, which deletes those too, in one transaction. Prefer making it Inactive: that keeps everyone's compliance history.

---

## 4. Inductee: Dashboard and Induction Page

The dashboard (`/inductee/`) lists every **active** induction with its state for this inductee (`InductionService::listActiveForInductee()`):

| State | When | Action |
| --- | --- | --- |
| Not Started | No compliance record, or the latest one is revoked; and no failed attempt as the latest | **Start** |
| Not Passed | Same, but the induction has an exam and the latest attempt failed | **Retry** |
| Compliant | Latest compliance record is active | View |
| Expired | Latest compliance record is expired (or superseded) | **Renew** |

The state comes from the latest compliance record for that induction (`compliance.md` §5); before one exists, from the latest exam attempt. Rows that need doing get a primary button (`docs/rules/design.md` §15).

The induction page (`/inductee/inductions/show.php?id=…`) shows the content one slide at a time (`content-blocks.md` §5). On the last slide the finish action replaces Next: **Mark as Complete**, **Start Exam** or **Retry Exam**. A compliant inductee has no finish action; they can reread the content any time. After expiry, completing it again is a renewal (`compliance.md` §4).

---

## 5. Files

| File | Role |
| --- | --- |
| `app/Induction/InductionService.php` | Validation, delete, per-inductee state, completion and exam submission |
| `app/Induction/InductionRepository.php` | SQL |
| `admin/inductions/`, `views/admin/inductions/` | Admin list, create, edit, delete, Studio |
| `inductee/index.php`, `views/inductee/dashboard.php` | Inductee dashboard |
| `inductee/inductions/show.php`, `complete.php`; `views/inductee/inductions/show.php` | Induction page, Mark as Complete |
