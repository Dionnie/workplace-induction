# Data Protection and Database Changes

How to change the database once real data exists. Read this before changing `database/schema.sql`, writing a migration or seed, or writing code that changes existing records in bulk.

> **Protect real data, not the current schema.**

The structure may change whenever there is a good technical or product reason. What must not happen is losing or silently changing data that records something real.

---

## 1. Real Data Is Protected

Treat as protected anything that records what actually exists or happened: user accounts and profiles, exam attempts, compliance records and their dates, certificate numbers and verification tokens, renewal links, uploaded files, settings, and the relationships between them.

**Assume every database holds real data** unless the developer has said it is disposable. That includes the local database: it holds records imported from the previous induction system.

Temporary test accounts from `database/test-users.php` are disposable by definition (`docs/rules/testing.md`).

---

## 2. Schema and Data Are Different

- `database/schema.sql` is where the structure should end up. It rebuilds an **empty** database.
- A migration is how an **existing** database gets there without losing data.
- `schema.sql` never authorizes dropping and recreating an existing database.

Adding `users.profile_completed` changes the schema; it doesn't justify touching existing users. Changing an id from UUID to `AUTO_INCREMENT` is a valid improvement; the records it identifies must survive it, with every foreign key remapped.

---

## 3. Don't Protect Bad Architecture

Renaming columns, splitting or merging tables, changing identifiers, normalizing data and removing obsolete columns are all allowed, provided existing data is carried across deliberately. Don't let fear of the database preserve a poor structure.

---

## 4. Before Changing a Database With Real Data

Answer these first:

1. What is changing, and why?
2. Which existing rows and relationships are affected?
3. How is each affected row carried across?
4. Which historical values must stay exactly as they are (dates, certificate numbers, statuses)?
5. What happens if the migration fails halfway?

Then: **backup → run on a copy → verify → run on the real database → verify again.** Keep the backup until the result has been checked.

---

## 5. Verify the Result

A migration finishing without errors doesn't mean the data is right. Compare before and after:

- row counts per table, and per status where statuses matter;
- relationships: every compliance record still points at its user, induction, exam attempt and renewal;
- historical values: `created_at`, issue and expiry dates, certificate numbers, verification tokens.

Test on a copy with realistic data: old records, nulls, every status, renewal chains.

---

## 6. Preserve History

- Never rewrite historical dates with the migration date. A record created on 2026-03-15 stays created on 2026-03-15.
- `updated_at` columns use `ON UPDATE CURRENT_TIMESTAMP`, so any `UPDATE` stamps them. When a migration shouldn't count as an edit, avoid a plain `UPDATE`, or set `updated_at = updated_at` in it.
- Transform records in place. Don't delete and re-insert them, which changes ids, dates and the relationships that point at them.
- Exam attempts are never edited after submission, and compliance records are superseded, not overwritten (`docs/application/exam-attempts.md`, `docs/application/compliance.md`).

---

## 7. Migrations in This Project

There is no migration framework. A migration is one dated file in `database/migrations/`:

| Kind | File | Use for |
| --- | --- | --- |
| SQL | `YYYY-MM-DD-short-name.sql` | Structural changes: add, rename or change columns and tables |
| PHP | `YYYY-MM-DD-short-name.php` | Changing the shape of data SQL can't safely express (e.g. the JSON in `inductions.content_blocks`) |

Every migration:

- **starts with a comment** saying what changes, why, what happens to existing rows, and the exact command to run, with the reminder to back up and test on a copy;
- **is additive where possible**, with defaults that are right for existing rows. Example: `users.profile_completed` was added with `DEFAULT 1` so existing (imported) users count as complete, then the default became `0` for new accounts, with no `UPDATE`;
- **is updated in `schema.sql` in the same change**, so a fresh install gets the final structure directly.

A PHP migration also:

- refuses to run over HTTP (`PHP_SAPI` check);
- runs as a **dry run** by default, printing what would change, and writes only with `--commit`;
- checks its own result before writing (nothing lost, the new shape passes the app's validation), and writes in one transaction;
- skips rows already migrated, so running it twice changes nothing.

**Applying migrations.** Nothing records which migrations a database has had. When updating an installation, apply the migrations dated after its last update, in date order. A fresh installation runs only `schema.sql` and `seeds.sql`, never the migrations.

---

## 8. Destructive Changes Need Explicit Approval

`DROP TABLE`, `TRUNCATE`, `DELETE` without a narrow condition, removing a column that holds data, narrowing a column so values are cut, and overwriting production data are high-risk. Use one only when it is:

1. identified as destructive, to the developer;
2. explicitly approved;
3. backed up;
4. tested on a copy;
5. run deliberately.

Never use them as a convenient way to apply an ordinary change. A separate local database holding only disposable test data may be rebuilt freely, once the developer confirms that is what it is.

In-app deletes (a user, an induction, an exam) are a different matter: they are deliberate administrator actions behind a Danger Zone and confirmation (`docs/rules/design.md` §13), and each feature doc says what they remove.

---

## 9. Rules for AI-Assisted Changes

AI-written code and commands must not, unless the developer explicitly approves it for a database they have said is disposable:

- drop, recreate or truncate tables, or reset the database;
- delete existing records, or replace them with seed data;
- rewrite historical dates or recreate historical records;
- change identifiers without remapping what depends on them.

When a schema change is needed, reason it through first: current structure → required structure → affected data → migration → how data is preserved → how it is verified. If it's unclear whether a database holds real data, treat it as real.

---

## 10. A Safe Change

A database change is safe when it has a clear reason, the affected data is known, history and relationships are preserved, a backup exists, it was tested on a copy, and the result was verified.

**The architecture is not sacred. The data is not disposable.**
