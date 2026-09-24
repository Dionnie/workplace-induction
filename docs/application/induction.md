# Final Database Architecture

## `inductions`

Defines the compliance requirement and its learning content.

```text
inductions
- id
- title
- code
- description
- exam_id              NULL
- validity_months
- content_blocks
- status
- created_at
- updated_at
```

**Rules:**

- `exam_id = NULL` → no exam required.
- `exam_id != NULL` → the induction requires the linked exam.
- `validity_months` determines the resulting compliance validity.
- `content_blocks` contains the induction material. See
  `docs/application/content_blocks_editor.md` for the block JSON shapes
  and editor design.

---

## `exams`

Defines an optional assessment.

```text
exams
- id
- title
- description
- status
- pass_percentage
- exam_blocks
- created_at
- updated_at
```

`exam_blocks` contains questions only.
Admins build them in the Exam Blocks editor (`docs/application/exam_blocks_editor.md`).

```json
[
  {
    "id": "blk_q_8f31",
    "type": "question",
    "question": "What is 1 + 1?",
    "diagram_img_url": "",
    "explanation": "1 + 1 equals 2.",
    "options": [
      {
        "id": "opt_1",
        "text": "2",
        "correct": true
      },
      {
        "id": "opt_2",
        "text": "4",
        "correct": false
      }
    ]
  }
]
```

---

## `exam_attempts`

Records an actual exam attempt.

```text
exam_attempts
- id
- user_id
- induction_id
- exam_id
- score
- total_score
- result
- created_at
```

An exam attempt only exists when the induction has an exam.

### Exam-based induction

```text
Induction
    ↓
Content
    ↓
Exam
    ↓
Exam Attempt
    ↓
Passed
    ↓
Compliance Record
```

### Non-exam induction

```text
Induction
    ↓
Content
    ↓
Completed
    ↓
Compliance Record
```

**Never create a fake exam attempt for a non-exam induction.**

Also, once an attempt is submitted, treat it as historical. Don't modify the score/result later.

---

# `compliance_records`

The authoritative record of issued compliance.

```text
compliance_records
- id
- user_id
- induction_id
- exam_attempt_id       NULL
- verification_token
- certificate_number
- issue_date
- expiry_date
- status
- renewed_from_id       NULL
- created_at
- updated_at
```

### Exam-based compliance

```text
exam_attempt_id = 1042
```

### Non-exam compliance

```text
exam_attempt_id = NULL
```

Both result in the same thing:

```text
COMPLIANCE RECORD
```

---

# Core Architecture

```text
                         ┌─────────────────────┐
                         │     INDUCTION       │
                         │                     │
                         │   Content Blocks    │
                         │   Optional Exam     │
                         └──────────┬──────────┘
                                    │
                       ┌────────────┴────────────┐
                       │                         │
                    NO EXAM                    EXAM
                       │                         │
                       ▼                         ▼
               CONTENT COMPLETED         EXAM ATTEMPT
                       │                         │
                       │                      PASSED
                       │                         │
                       └────────────┬────────────┘
                                    ▼
                           COMPLIANCE RECORD
                                    │
                                    ▼
                               CERTIFICATE
```

---

# Renewal

Renewal **always creates a new compliance record**.

Never overwrite the previous one.

```text
Compliance #1001
2026 → 2027
status: superseded
       │
       │ renewed_from_id
       ▼
Compliance #2045
2027 → 2028
status: active
```

Then:

```text
#1001
  ↓
#2045
  ↓
#3192
```

This gives you a permanent compliance history.

---

# Compliance Status

Use only:

```text
active
expired
superseded
revoked
```

- **active** — currently valid
- **expired** — validity period has ended
- **superseded** — replaced through renewal
- **revoked** — manually invalidated

---

# Current Compliance

The system should ask:

> Does this user currently have valid compliance for this induction?

Not merely:

> Does this user have a compliance record?

Conceptually:

```text
user_id
+
induction_id
↓
current compliance
```

The record must be:

```text
status = active
issue_date <= now
expiry_date >= now
```

This should be centralized in something like:

```text
get_current_compliance(user_id, induction_id)
```

---

# Certificate Identity

Keep these separate:

```text
certificate_number
verification_token
```

### Certificate number

Human-readable:

```text
CERT-2026-000104
```

### Verification token

Random/unpredictable:

```text
f9d8c7a1e...
```

Used for:

```text
/certificate/{verification_token}
```

The certificate number identifies the credential; the verification token provides the secure public lookup.

---

# Final Locked Schema

```text
inductions
- id
- title
- code
- description
- exam_id              NULL
- validity_months
- content_blocks
- status
- created_at
- updated_at
```

```text
exams
- id
- title
- description
- status
- pass_percentage
- exam_blocks
- created_at
- updated_at
```

```text
exam_attempts
- id
- user_id
- induction_id
- exam_id
- score
- total_score
- result
- created_at
```

```text
compliance_records
- id
- user_id
- induction_id
- exam_attempt_id       NULL
- verification_token
- certificate_number
- issue_date
- expiry_date
- status
- renewed_from_id       NULL
- created_at
- updated_at
```

### Core principle

> **Induction = requirement**
> **Exam = optional assessment**
> **Exam attempt = evidence of an assessment**
> **Compliance record = issued credential**
