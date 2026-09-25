# Project Instructions

## Project

A generic, white-label workplace induction system: inductees complete inductions (slides and an optional exam) and receive compliance records with verifiable certificates; administrators manage it all. Plain PHP 8 + MySQL + Bootstrap 5.3, no framework, no Composer, no build step. Local site: `http://workplace-induction.test`.

## General Rules

- Prefer the simplest implementation that satisfies the requirement.
- Don't introduce architecture, abstractions, dependencies, UI patterns or terminology unless they are necessary.
- Implement only what was asked. No unrelated refactoring.
- The code is the truth; the docs describe it. If they disagree, check the code, then fix whichever is wrong in the same change.

## Sources of Truth

| What | Where |
| --- | --- |
| Database structure | `database/schema.sql` (describes the target; never authorizes resetting an existing database) |
| UI markup and classes | `docs/rules/design-system.html` |
| Words used in the UI | `docs/rules/terminology.md` |
| Admin sections | `views/partials/admin-menu.php` |
| Status → badge colour | `status_badge()` in `app/Core/helpers.php` |

## Documentation

Don't read every doc. Read the ones for the task.

**`docs/rules/`: how to build anything**

| Doc | Read when |
| --- | --- |
| `architecture.md` | Adding a page, feature, service, repository, hook or script; deciding where code goes |
| `design.md` + `design-system.html` | Building or changing any UI. Check it works with every theme in Settings → Appearance. |
| `terminology.md` | Naming anything a user sees, or a new concept in code |
| `security.md` | Forms, auth guards, output, uploads, tokens, redirects, config |
| `data-protection.md` | Schema changes, migrations, seeds, anything that changes existing records |
| `testing.md` | Checking a change end to end; test accounts |
| `deployment.md` | Deploying from Laragon to cPanel, updating the live site, copying live data locally |
| `public-pages.md` | The landing page, auth pages, certificate verification page |

**`docs/core/`: platform features** (admin menu group "Core")

| Doc | Covers |
| --- | --- |
| `users.md` | User types, profiles, registration, login, email verification, password reset and setup links, profile completion, admin Users, My Profile |
| `media-library.md` | Uploads, categories, upload rules, the picker, how picked URLs are stored |
| `tools.md` | Search & Replace |
| `settings.md` | General, Appearance, Email senders, Notifications, cron, email templates |

**`docs/application/`: induction features** (admin menu group "Application")

| Doc | Covers |
| --- | --- |
| `inductions.md` | The induction record, how completing one works, admin Inductions, inductee dashboard and induction page |
| `content-blocks.md` | Slides and blocks: data, validation, sanitizing, the inductee slide view, the Studio |
| `exams.md` | The exam record, Exam Blocks, the Studio, exam and result pages, scoring |
| `exam-attempts.md` | Attempt records and their admin pages |
| `compliance.md` | Compliance records, statuses, issuing, renewal, current compliance, certificates, revoke, admin dashboard |

## Workflow: New Feature or Change

1. **Read** the feature's doc, then the rules the change touches (at least `architecture.md`; `design.md` + `design-system.html` for UI; `data-protection.md` for the database).
2. **Look at existing code** for the same kind of thing and follow it. Reuse services, partials, helpers and classes before creating new ones.
3. **Place it**: Core or Application (`architecture.md` §2), then controller / service / repository / view as needed (§10).
4. **Database changes**: update `schema.sql` and add a dated migration in `database/migrations/` (`data-protection.md` §7). Never reset or truncate a database.
5. **UI**: use the design system's patterns. A new class goes in `app.css` and in the design system's Class reference and examples, in the same change. A new admin section goes in `views/partials/admin-menu.php`.
6. **Test** the workflow end to end with a temporary test account, and delete it afterwards (`testing.md`).
7. **Update the docs** in the same change (below), then report what changed.

## Keeping the Docs Right

- **One home per fact.** Each fact lives in one doc; others link to it (`docs/core/users.md` §9) rather than repeat it. Don't copy column lists from `schema.sql`, class lists from the design system, or terms from `terminology.md`.
- **Where a change is documented**: the feature's behaviour goes in its feature doc; a rule that applies across features goes in `docs/rules/`; a new feature gets a new doc in `docs/core/` or `docs/application/` and a row in the tables above.
- **Code comments cite docs** by path and section, e.g. `(docs/core/users.md §9)`. When renumbering or moving a section, update those references (search for the doc's file name).
- Docs describe what is built, not plans. Write plainly and briefly.
