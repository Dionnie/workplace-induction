# Tools

**Core.** Maintenance utilities for administrators, at `/admin/tools/`. The Tools page lists each tool as a link card. There is one tool today: **Search & Replace**.

A new tool gets its own page under `admin/tools/`, a service in `app/Tools/`, and a link card on `views/admin/tools/index.php`.

---

## 1. Search & Replace

Replaces text across database tables. It exists for maintenance such as rewriting absolute URLs after the site moves to a new domain (`docs/core/media-library.md` §4). It is not a general data editor.

`/admin/tools/search-replace.php`, `App\Tools\SearchReplaceService`.

---

## 2. How It Works

A two-step page (`docs/rules/design.md` §13):

1. **Dry Run.** Tick the tables, enter **Find** (required) and **Replace With**, and run. Nothing is written. The page lists, per table and column, every matching row with how many times the text appears and highlighted snippets.
2. **Replace.** Below the results, **Replace** applies exactly what was previewed, in one transaction. The page then reloads the dry run, which should find no matches: that confirms the change.

Matching is exact and case-sensitive (PHP `str_replace()`, not MySQL's collation-dependent `LIKE`/`REPLACE()`), so the preview is exactly what gets written.

---

## 3. Safety Nets

These apply whatever the administrator selects, and can't be switched off from the page:

- **Tables and columns are discovered live** from `information_schema`, so a new table is searchable without code changes. Request input only chooses among names read from there, so there is no SQL identifier injection.
- **Only text columns** are searched: `char`, `varchar`, the `text` types and `json`. Numbers, dates and enums never are. Tables without a primary key are skipped.
- **Protected columns** are never touched: `users.password`, `email_verification_token`, `password_reset_token`; `compliance_records.verification_token`, `certificate_number`, `legacy_id`; `media_items.filename` (the name of a file on disk). Add a column here when editing its text would break security or something outside the database.
- **All or nothing.** If a replacement would make a JSON column invalid, or the database rejects any change (for example a unique key), nothing is written anywhere and the page says why.

Back up the database before a large replacement on a live site (`docs/rules/data-protection.md` §4). Rows it changes get a new `updated_at`.

### After moving to a new domain

Find the old address and replace it with the new one, including the scheme, e.g. `http://workplace-induction.test` → `https://induction.example.com`, with `inductions` and `exams` ticked. That fixes images and galleries in content blocks and exam question diagrams.
