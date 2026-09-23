## 1. `inductions`

Defines an induction requirement that users can complete.

| Column            | Description                                                                                  |
| ----------------- | -------------------------------------------------------------------------------------------- |
| `id`              | Unique identifier for the induction.                                                         |
| `title`           | Human-readable name of the induction, e.g. `Site Safety Induction`.                          |
| `code`            | Unique short identifier used to reference the induction, e.g. `SITE-SAFETY-01`.              |
| `description`     | Short description explaining what the induction is for.                                      |
| `exam_id`         | Optional reference to the `exams` table. `NULL` when the induction does not require an exam. |
| `validity_months` | Number of months the resulting compliance remains valid.                                     |
| `content_blocks`  | JSON data containing the induction's learning/content blocks.                                |
| `status`          | Whether the induction is currently available, e.g. `active` or `inactive`.                   |
| `created_at`      | Date and time the induction was created.                                                     |
| `updated_at`      | Date and time the induction was last modified.                                               |

**Key point:** An induction is the **requirement**. It may contain content only, or content plus an exam.

---

## 2. `exams`

Defines an exam that can be attached to an induction.

| Column            | Description                                                         |
| ----------------- | ------------------------------------------------------------------- |
| `id`              | Unique identifier for the exam.                                     |
| `title`           | Human-readable name of the exam.                                    |
| `description`     | Short description or instructions for the exam.                     |
| `status`          | Whether the exam is available for use, e.g. `active` or `inactive`. |
| `pass_percentage` | Minimum percentage required to pass the exam, e.g. `80`.            |
| `exam_blocks`     | JSON data containing the exam questions and their answer options.   |
| `created_at`      | Date and time the exam was created.                                 |
| `updated_at`      | Date and time the exam was last modified.                           |

### `exam_blocks`

This contains **questions only**.

Each question can contain:

- Question text
- Optional diagram/image
- Explanation
- Answer options
- Correct answer

An exam therefore remains focused on assessment rather than becoming another generic content builder.

---

## 3. `exam_attempts`

Stores every time a user actually takes an exam.

| Column         | Description                                        |
| -------------- | -------------------------------------------------- |
| `id`           | Unique identifier for the exam attempt.            |
| `user_id`      | Identifies the user who took the exam.             |
| `induction_id` | Identifies the induction the exam was taken for.   |
| `exam_id`      | Identifies the exam that was taken.                |
| `score`        | Number of points/questions answered correctly.     |
| `total_score`  | Maximum possible score for that attempt.           |
| `result`       | Outcome of the attempt, e.g. `passed` or `failed`. |
| `created_at`   | Date and time the exam was submitted.              |

### Why store both `induction_id` and `exam_id`?

Because the **exam attempt happened in the context of an induction**.

It lets you answer:

> "Which induction requirement did this assessment satisfy?"

rather than only:

> "Which exam did this user take?"

This also makes historical records much easier to query.

---

## 4. `compliance_records`

The **authoritative record that a user has successfully completed an induction requirement**.

| Column               | Description                                                                                            |
| -------------------- | ------------------------------------------------------------------------------------------------------ |
| `id`                 | Unique identifier for the compliance record.                                                           |
| `user_id`            | Identifies the user who achieved compliance.                                                           |
| `induction_id`       | Identifies the induction requirement that was completed.                                               |
| `exam_attempt_id`    | Optional reference to the successful exam attempt. `NULL` when the induction does not require an exam. |
| `verification_token` | Unique random token used for public certificate/compliance verification.                               |
| `certificate_number` | Human-readable certificate or compliance number, e.g. `CERT-2026-001245`.                              |
| `issue_date`         | Date the compliance record/certificate was issued.                                                     |
| `expiry_date`        | Date the compliance expires.                                                                           |
| `status`             | Current state of the compliance record: `active`, `expired`, `superseded`, or `revoked`.               |
| `renewed_from_id`    | Optional reference to the previous compliance record when this record is a renewal.                    |
| `legacy_id`          | Optional identifier from a prior induction system, for records migrated from elsewhere. `NULL` otherwise. |
| `created_at`         | Date and time the compliance record was created.                                                       |
| `updated_at`         | Date and time the compliance record was last modified.                                                 |

---

# How the tables relate

The architecture can be understood very simply:

```text
INDUCTION
   │
   ├── content_blocks
   │
   └── optional EXAM
             │
             └── EXAM ATTEMPT
                       │
                       ▼
                COMPLIANCE RECORD
```

For an induction **without an exam**:

```text
User completes induction
        │
        ▼
Compliance Record
exam_attempt_id = NULL
```

For an induction **with an exam**:

```text
User completes induction
        │
        ▼
Takes Exam
        │
        ▼
Exam Attempt
        │
     passed
        │
        ▼
Compliance Record
```

For a failed attempt:

```text
Exam Attempt
result = failed
        │
        X
No compliance record
```

The failed attempt is still retained as historical evidence.

---

# Renewal

Renewal does **not** overwrite the existing compliance record.

```text
Old Compliance Record
        │
        │ renewed_from_id
        ▼
New Compliance Record
```

For example:

```text
compliance_records

ID   user   induction   status       renewed_from_id
101  25     3           superseded   NULL
145  25     3           active       101
```

The old record remains available for audit/history.

The new record gets its own:

- `certificate_number`
- `verification_token`
- `issue_date`
- `expiry_date`

---

# Final relationship summary

| Table                | Purpose                                                      |
| -------------------- | ------------------------------------------------------------ |
| `inductions`         | Defines **what requirement** the user needs to complete      |
| `exams`              | Defines the **optional assessment** attached to an induction |
| `exam_attempts`      | Records **each assessment attempt**                          |
| `compliance_records` | Records the **actual achieved compliance/certificate**       |

The important distinction is:

> **Induction = Requirement**
> **Exam = Assessment**
> **Exam Attempt = Evidence of assessment**
> **Compliance Record = Evidence that the requirement was successfully completed**

That gives you a clean architecture without needing a separate completion table, exam versioning system, or additional relationship layer.
