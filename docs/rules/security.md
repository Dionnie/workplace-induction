# Security

Rules every page and feature follows. How accounts, login and passwords work is in `docs/core/users.md`; this file is the rules that apply to all code.

---

## 1. Authorization Is Server-Side

Every protected entry page guards itself before doing anything else:

```php
Auth::requireRole('admin');      // or 'inductee'; sends a guest to login, answers 403 otherwise
```

- There are two user types and no permission system. `admin/*` pages require `admin`; `inductee/*` pages require `inductee`.
- Hiding a link, disabling a button, a hard-to-guess URL or JavaScript is never protection.
- Load a user's own records through an ownership check (`ComplianceService::findOwnedByUser($id, $userId)`), never by id alone, so changing an id in the URL shows nothing.
- `/admin/` and `/inductee/` are entry points, not security boundaries. The guard in each file is.
- Administrators have full application access, but the same controls apply to them: authentication, sessions, CSRF, validation, safe file handling.

---

## 2. CSRF

- Every POST form includes `<?= csrf_field() ?>`.
- Every POST controller calls `verify_csrf()` before reading or changing anything. It answers 419 on a missing or wrong token.
- `fetch` requests send the same token (`csrf_token()`).
- A GET request never changes data, so it needs no token.

---

## 3. Output and Input

- Escape every value written into HTML with `e()`. The exceptions are HTML that was sanitized on save (content blocks' rich text) or is trusted by design (the admin-only Raw HTML block, `docs/application/content-blocks.md` §3.1, §7).
- Rich text is sanitized on the server against an allow-list (`ContentBlockService`). Cleaning in the browser is never a trust boundary.
- Validate on the server. Browser validation (`required`, `type="email"`) is a convenience.
- SQL values are always bound parameters. A table or column name that can't be bound must come from the code or from `information_schema`, never from the request (`SearchReplaceService`).
- Redirect targets from the request go through `safe_redirect_path()`, which accepts only a root-relative path on this site.

---

## 4. Passwords and Tokens

- Hash passwords with `password_hash()`; check them with `password_verify()`. Minimum 8 characters.
- Never email a password, and never show one after it is set.
- Tokens (email verification, password reset, account setup, certificate verification) come from `random_bytes()`, and expire where they grant access.
- Password reset and setup tokens are stored as a SHA-256 hash, so a copy of the database can't be used to set a password (`docs/core/users.md` §7).
- A "forgot password" request gives the same answer whether or not the email exists.

---

## 5. Sessions

`bootstrap.php` starts every session with `HttpOnly`, `SameSite=Lax`, `Secure` when the request is HTTPS, and a cookie that ends with the browser session.

- `Auth::login()` regenerates the session ID (prevents session fixation). `Auth::logout()` clears the session and regenerates it. Switch Account and Switch Back regenerate it too (`docs/core/users.md` §10).
- The session holds only the user's id, and during Switch Account the administrator's id beside it. Nothing sensitive goes in browser storage.
- Account status is checked at login and on every request (`Auth::user()`). A user who is suspended, made inactive or deleted is logged out on their next page (`docs/core/users.md` §2).

---

## 6. File Uploads

Uploads go through the Media Library only (`docs/core/media-library.md` §3):

- The extension must be enabled in the upload settings, and one of the image types the code allows (JPG, PNG, GIF, WebP). SVG is never allowed, since it can carry scripts.
- The file must pass `is_uploaded_file()`, the size limit, and `getimagesize()` with the MIME type matching the extension.
- It is stored under a random name; the original name is kept for display only.

---

## 7. Configuration and Secrets

- Database and URL settings are in `config/*.php`: environment variables, falling back to local-development defaults. A server gets its own copy of those files (`docs/rules/deployment.md` §3.4). Mail needs no credentials (`docs/core/settings.md` §3). Real credentials never go in the repository, seeds, docs or code comments.
- `database/seeds.sql` creates a default administrator with a known password. Change it on first login, on every installation.
- CLI scripts refuse to run over HTTP (`docs/rules/architecture.md` §7).

### What the web server serves

The whole project sits in the web root, so the root `.htaccess` returns 403 for everything that isn't a page or an asset:

| Blocked | Why |
| --- | --- |
| `app/`, `config/`, `cron/`, `database/`, `views/` | Loaded by PHP or run from the command line, never requested. `database/` holds the schema, seeds, migrations and any backups. |
| `*.md` (`CLAUDE.md`, `docs/`) | Internal documentation. `docs/rules/design-system.html` stays viewable. |
| Dotfiles and dot-folders (`.git`, `.claude`, `.gitignore`) | `.git` would expose the full source and history. `.well-known` stays open for SSL certificate checks. |
| Folder listings under `assets/` and `docs/` | Would list every uploaded file. Files themselves are still served. |

- A new top-level folder that shouldn't be served goes in the first rule.
- The rules need Apache or LiteSpeed with `mod_rewrite` and `.htaccess` allowed (true on Laragon and cPanel). On nginx, add the same rules to the server config.
- After deploying, check that `/database/schema.sql` and `/.git/HEAD` answer 403.

---

## 8. Trust Boundaries to Know

| Boundary | Why it is acceptable | Revisit when |
| --- | --- | --- |
| Raw HTML content block is not sanitized | Only administrators author content | Anyone else can edit content |
| Image and video URLs in content blocks can point anywhere (`https` for videos) | Same | Same |
| Search & Replace can rewrite any text column except the protected ones | Admin-only, preview first, one transaction | — |
