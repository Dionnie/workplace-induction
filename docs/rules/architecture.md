# Architecture

How the code is organised and where new code goes. Read this before adding a page, a feature or a layer.

The application is plain PHP 8 with MySQL, Bootstrap 5.3 and no framework. There is no Composer, no build step and no router: every URL is a PHP file.

The guiding rule:

> **Separate responsibilities without creating unnecessary layers.**

---

## 1. Directory Layout

```text
index.php, login.php, register.php,     Public entry pages (rules/public-pages.md)
forgot-password.php, reset-password.php,
verify-email.php, logout.php,
certificate.php
admin/<section>/*.php                   Administrator pages (controllers)
inductee/<section>/*.php                Inductee pages (controllers)
app/<Feature>/                          Services and repositories, namespace App\<Feature>
app/Core/                               Shared infrastructure
views/<area>/<section>/*.php            Templates: admin/, inductee/, auth/, public/, emails/
views/partials/                         Layouts shared by every page
assets/css/app.css, assets/js/*.js      The design system and page scripts
assets/uploads/                         Uploaded files (Media Library)
config/*.php                            Settings read from environment variables
cron/                                   Scheduled CLI scripts
database/                               schema.sql, seeds.sql, migrations/, CLI scripts
docs/                                   This documentation
bootstrap.php                           Autoloader, helpers, hooks, session
```

`bootstrap.php` autoloads `App\Foo\Bar` from `app/Foo/Bar.php`, loads `app/Core/helpers.php`, `app/Core/hooks.php` and the hook listeners, sets the timezone from `config/app.php` (MySQL connections follow it, `docs/rules/deployment.md` §4), and starts the session. Every entry page and CLI script requires it first.

---

## 2. Core and Application

The code, the admin menu and these docs share one split:

| Area | What it is | Code | Docs |
| --- | --- | --- | --- |
| **Core** | Platform functions any white-label deployment has | `app/Core/` (auth, database, mail, theme, settings, helpers, hooks), `app/Admin/` (dashboard, user management), `app/MediaLibrary/`, `app/Tools/`, `app/Notification/`, `app/Inductee/` (the inductee profile) | `docs/core/` |
| **Application** | The induction features | `app/Induction/`, `app/ContentBlocks/`, `app/Exam/` (exams and exam attempts), `app/Compliance/` | `docs/application/` |

Keep Core small. Don't move feature logic into Core just because two features use it. Core must not hold application rules (for example, what an inductee's profile requires lives in `App\Inductee`, not in `Auth`).

---

## 3. Request Flow

```text
Entry page (controller)  admin/inductions/edit.php
    ↓ require bootstrap.php
    ↓ Auth::requireRole('admin')            authentication + authorization
    ↓ verify_csrf() on POST
    ↓ Service                               business rules, validation
    ↓ Repository → PDO                      SQL
    ↓ flash() + redirect()   or   require views/…/edit.php
```

Not every feature needs every layer. Add a layer only when it earns its place.

### Controllers (entry pages)

A file under `admin/`, `inductee/` or the root. It:

- requires `bootstrap.php`, then guards access (`Auth::requireRole()`, `Auth::requireCompletedProfile()` where needed);
- reads and trims request input, and calls `verify_csrf()` before any POST;
- calls a service, then either redirects with a flash message (POST, the Post/Redirect/Get pattern) or requires a view;
- on a validation failure: `set_old($data)`, `set_errors($result['errors'])`, redirect back to the form;
- answers a missing record with a 404 and a short message, and a wrong method with a 405.

Controllers stay thin: no SQL and no business rules. An action with its own URL (delete, revoke, send a setup email) is its own file, e.g. `admin/users/delete.php`.

### Services (`app/<Feature>/<Feature>Service.php`)

Business rules and validation. A method describes a business operation (`issue`, `revoke`, `submitExam`, `completeWithoutExam`), not a UI action and not generic CRUD.

Methods that change data return:

```php
['success' => true,  'errors' => []]                         // plus e.g. 'id' => 12
['success' => false, 'errors' => ['field' => 'Message.', 'form' => 'Message.']]
```

`errors` is keyed by form field; `form` holds an error that belongs to no single field. A service that changes several tables does it in one transaction (`Database::connection()->beginTransaction()`).

### Repositories (`app/<Feature>/<Feature>Repository.php`)

SQL only, through `App\Core\Database::connection()` (PDO, exceptions on, associative fetches, real prepared statements). Always bind values. No HTML, redirects or authorization.

### Views (`views/<area>/<section>/*.php`)

Plain PHP templates. They display what the controller passes, escape every value with `e()`, and use the layout partials and the design system (`docs/rules/design.md`). No queries and no business rules. Shared form markup is a partial named with a leading underscore (`views/admin/inductions/_form.php`).

### JSON endpoints

The Studio editors save with `fetch` to their own page (`admin/inductions/editor.php`, `admin/exams/editor.php`), which answers JSON. The Media Library has `list.php` (GET) and `upload.php` (POST) for the picker. JSON endpoints use the same `Auth` guard as pages, and every POST checks the CSRF token.

---

## 4. Roles Share Business Logic

The two user types (`docs/core/users.md`) use the same features very differently, so they have separate controllers and views, but **one** service and repository per feature:

```text
admin/compliance/index.php ─┐
inductee/compliance/index.php ─┼→ ComplianceService → ComplianceRepository
certificate.php (public) ──────┘
```

- Don't create `AdminComplianceService` / `InducteeComplianceService`, or methods like `adminGetRecords()`.
- A method is named for its meaning, and exists when the operation genuinely differs: `listActiveForInductee($userId)` computes each induction's state for one inductee; `findOwnedByUser($id, $userId)` enforces ownership.
- Different experiences get different views. Don't fill one template with role conditionals.

---

## 5. Hooks

`app/Core/hooks.php` provides `add_action()` and `do_action()` for **secondary** side effects: things that react after the primary operation has already succeeded.

| Event | Fired by | Listeners (`app/Notification/listeners.php`) |
| --- | --- | --- |
| `inductee_registered` | `AuthService::registerInductee()` | Tell administrators (instant mode) |
| `induction_completed` | `InductionService::completeWithoutExam()`, `submitExam()` | Tell administrators (instant mode); tell the inductee |

Rules:

- The primary workflow stays explicit in the service. Issuing a compliance record is never done in a hook.
- A listener catches and logs its own failures (`error_log`), so a failed email never undoes or breaks the operation that triggered it.
- If something is required for the operation to be valid, it belongs in the service, not a hook.

---

## 6. Features Talk Through Services

A feature changes another feature's data through that feature's service, not its tables:

```php
// InductionService::submitExam()
(new ComplianceService())->issue($userId, $inductionId, $validityMonths, $attemptId);
```

Reading another feature's repository for a count or a lookup is fine (`ExamAttemptRepository::countForInduction()`).

---

## 7. Scripts Outside the Web Request

| Script | Purpose |
| --- | --- |
| `cron/send-notifications.php` | Daily: expiry reminders and the administrator report (`docs/core/settings.md` §5) |
| `database/test-users.php` | Temporary test accounts (`docs/rules/testing.md`) |
| `database/migrations/*.php` | Data migrations (`docs/rules/data-protection.md` §7) |
| `database/import-legacy-contacts.php` | One-off import from the previous induction system |

Every CLI script refuses to run over HTTP (`PHP_SAPI !== 'cli'` → 403). `.htaccess` already blocks `cron/` and `database/` (`docs/rules/security.md` §7); the check is kept in case a server ignores `.htaccess`.

---

## 8. Database Schema

- `database/schema.sql` is the authoritative structure: it must rebuild an empty database. Its comments explain columns whose meaning isn't obvious.
- `database/seeds.sql` holds default data: the first administrator and the `email_settings` row. No secrets. It must run cleanly on a fresh `schema.sql`, so update it in the same change as any column it uses.
- A structural change updates `schema.sql` **and** adds a migration in `database/migrations/` for existing databases. `docs/rules/data-protection.md` governs how.
- Feature docs explain what the data means and its rules. They don't copy column lists from `schema.sql`.

---

## 9. What Not to Build

Don't add these without a real requirement:

```text
Framework, router, Composer dependency, build step
BaseController, BaseService, BaseRepository, GenericRepository
Interfaces with one implementation, factories, providers, managers
Event bus, command bus, generic CRUD engine
Migration framework (a dated SQL/PHP file is enough)
```

Some repetition is fine when it keeps the code easy to read. Two deliberate duplicates exist, and both are documented: the Studio block previews in JavaScript mirror the PHP renderers, so editing needs no server round trip (`docs/application/content-blocks.md` §6, `docs/application/exams.md` §4).

---

## 10. Where Does It Go?

| The code… | Goes in |
| --- | --- |
| Reads the request, guards access, redirects | Controller |
| Is a business rule or validation | Service |
| Is SQL | Repository |
| Is markup | View (and `app.css` + the design system, if it's a new pattern) |
| Reacts after something succeeded (email, log) | Hook listener |
| Is shared infrastructure with no feature rules | `app/Core/` |
| Belongs to one feature | That feature's folder |
| Exists for a hypothetical future need | Nowhere yet |
