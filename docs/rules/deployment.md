# Deployment

Moving the site from a local Laragon install to a cPanel host, and updating it afterwards.

The first deployment copies the local database to the server. **From go-live on, the server's data is the real data**: data only ever flows from the server to a local copy, never the other way (`docs/rules/data-protection.md`).

---

## Watch Out For

Each of these breaks something quietly. The sections below cover them.

| Trap | What goes wrong | Section |
| --- | --- | --- |
| Site in a subfolder (`example.com/induction/`) | Every link and asset breaks: URLs are root-relative | §1 |
| `.htaccess` not uploaded | Source, `.git`, schema and docs are downloadable | §2, §6 |
| Credentials set only with `SetEnv` | Cron can't connect: command-line PHP doesn't see Apache variables | §3.4 |
| `url` not set in `config/app.php` | Emails sent by cron link to `http://localhost/…` | §3.4 |
| Cron uses a different PHP binary | Wrong PHP version, or none | §3.6 |
| `timezone` in `config/app.php` changed from the one the data was made in | New times jump against old ones; certificates dated in the wrong zone | §4 |
| Image URLs still on the local domain | Content images load from `workplace-induction.test`, which doesn't exist | §5.1 |
| Only `http://` replaced, not `https://` (or the reverse) | Some images still broken, or mixed-content warnings | §5.1 |
| Development email settings carried over | Mail sent from `example.com` is rejected; CC/BCC copies go to development addresses | §5.2 |
| Default `admin@example.com` still active | A published password, and administrator emails that go nowhere | §5.3 |
| PHP upload limit below the Media Library limit | Uploads fail with a size error | §3.1, §5.4 |
| Local database imported over a live site | Everything done on the live site since go-live is lost | §7 |

---

## 1. Requirements

| Need | Detail |
| --- | --- |
| Domain | Its own domain or subdomain (`induction.example.com`), with the project folder as the document root. **Not a subfolder**: every URL in the app starts at `/`. |
| PHP | 8.0 or later; use 8.3, which development runs. Extensions `pdo_mysql` and `mbstring` (standard on cPanel). |
| Database | MySQL 8, or MariaDB 10.5+. The dump has no MySQL-only syntax, and MariaDB accepts the `json` columns. |
| Web server | Apache or LiteSpeed with `mod_rewrite` and `.htaccess` (cPanel's default) |
| HTTPS | AutoSSL, or any certificate |
| Access | SSH / cPanel Terminal, for the cron check and any migration scripts |

---

## 2. Prepare on Laragon

1. **Commit everything.** The bundle is built from git, so uncommitted files are left out. That includes `.htaccess`.
2. **Bring the database up to date.** Apply every migration in `database/migrations/` locally. The dump carries the structure, so a first deployment runs no migrations on the server. `php database/migrations/2026-09-25-content-blocks-to-slides.php` should report "Nothing to migrate".
3. **No test accounts left:** `php database/test-users.php list` shows 0.
4. **Export the database** (from Laragon's terminal, Cmder, where `mysqldump` is on the path; in Git Bash, end lines with `\` instead of `^`):

   ```text
   mysqldump -uroot --single-transaction --no-tablespaces --set-gtid-purged=OFF ^
       --default-character-set=utf8mb4 workplace-induction > induction-YYYY-MM-DD.sql
   ```

   Don't use `--databases`: it would write the local database name into the dump. The dump holds inductees' personal details; keep it off shared drives, and delete it once imported.
5. **Build the file bundle**, and tag what was deployed (§7 uses the tag):

   ```text
   git archive --format=zip -o ..\induction-YYYY-MM-DD.zip HEAD
   git tag deployed-YYYY-MM-DD
   ```

   It contains every tracked file, including `.htaccess` and `assets/uploads/`, and leaves out `.git/`, `.claude/` and `database/backups/`. `docs/` and `CLAUDE.md` go along; `.htaccess` blocks them.

---

## 3. Set Up the Server

### 3.1 Domain and PHP

- **Domains**: create the domain or subdomain, with a document root of its own (e.g. `/home/USER/induction.example.com`).
- **MultiPHP Manager**: PHP 8.3 for that domain.
- **MultiPHP INI Editor**, for that domain:

  | Setting | Value |
  | --- | --- |
  | `display_errors` | Off |
  | `log_errors` | On |
  | `upload_max_filesize`, `post_max_size` | At least the Media Library maximum (5 MB by default), e.g. 16M |

### 3.2 Database

1. **MySQL Databases**: create a database and a user; cPanel prefixes both with the account name (`USER_induction`). Add the user to the database with **All Privileges**.
2. Import the dump: phpMyAdmin → the database → Import, or over SSH:

   ```text
   mysql -u USER_induction -p USER_induction < induction-YYYY-MM-DD.sql
   ```

3. Check the tables, and a few row counts against the local database.

### 3.3 Files

1. Upload the zip to the document root and extract it there. The project's root, with `index.php` and `.htaccess`, must *be* the document root, not a folder inside it.
2. Permissions: folders 755, files 644. PHP runs as the account user on cPanel, so `assets/uploads/media-library/` is writable at 755.
3. Check `.htaccess` arrived; some upload tools skip dotfiles.

### 3.4 Configuration

Edit two files **on the server only**:

```php
// config/database.php
'host'     => getenv('DB_HOST') ?: 'localhost',          // localhost, not 127.0.0.1: cPanel grants user@localhost
'database' => getenv('DB_DATABASE') ?: 'USER_induction',
'username' => getenv('DB_USERNAME') ?: 'USER_induction',
'password' => getenv('DB_PASSWORD') ?: '…',
```

```php
// config/app.php
'url' => getenv('APP_URL') ?: 'https://induction.example.com',   // no trailing slash
'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Singapore',          // keep as is: see §4
'registration_enabled' => true,                                    // false: no public Register page
// 'tagline', 'description': the landing page text (docs/rules/public-pages.md)
```

- **Edit the files rather than setting environment variables.** Variables set with `SetEnv` in `.htaccess` reach web requests only. The cron job runs command-line PHP, which would fall back to the local defaults and fail to connect.
- **`url` is required.** Emails sent by cron have no web request to take the domain from, so without it their links point to `http://localhost`.
- These are the server's copies. Never commit them, and don't overwrite them on later updates (§7). `.htaccess` blocks `config/` from the web (`docs/rules/security.md` §7).

### 3.5 HTTPS

- Issue the certificate (**SSL/TLS Status** → AutoSSL) and turn on **Domains → Force HTTPS Redirect**.
- The session cookie is `Secure` only when PHP sees the request as HTTPS. Behind a proxy that ends TLS itself (e.g. Cloudflare's "Flexible" mode), it won't be; use "Full" mode.

### 3.6 Cron

**Cron Jobs** → once a day, early morning server time:

```text
/opt/cpanel/ea-php83/root/usr/bin/php /home/USER/induction.example.com/cron/send-notifications.php >> /home/USER/logs/induction-cron.log 2>&1
```

- Use the same PHP as the site. Plain `php` or `/usr/local/bin/php` may be the server's default version, not 8.3. `ls /opt/cpanel/` lists the installed ones (on CloudLinux hosts, also `/opt/alt/php83/usr/bin/php`).
- Create the log folder first. Run the command once in Terminal: it prints how many reminders it sent and whether the report was due.
- What it sends: `docs/core/settings.md` §4–5.

---

## 4. Timezone

`'timezone'` in `config/app.php` (§3.4) is the organisation's timezone. `bootstrap.php` sets it for PHP, and `App\Core\Database` sets the same offset on every MySQL connection. PHP dates (certificate issue and expiry dates) and MySQL's (`NOW()`, `CURDATE()`, `created_at`) therefore always agree, on web requests and in cron, whatever the server's own zones. The server's `date.timezone` and MySQL's global zone don't matter to the app.

- **Keep the value the records were made in.** `DATETIME` columns store no zone, so the imported data's times are in the local install's zone (`Asia/Singapore`, UTC+8). Changing the setting later makes new times jump against old ones; treat that as a data change (`docs/rules/data-protection.md` §4).
- An invalid name stops every page with an error naming the setting, rather than dating certificates wrongly.
- Check it after deploying, in Terminal from the document root. Both times must be the organisation's local time (phpMyAdmin's `SELECT NOW()` shows the server's zone instead, not the app's):

  ```text
  php -r 'require "bootstrap.php"; echo date("c"), "  ", App\Core\Database::now()->format("Y-m-d H:i:s"), "\n";'
  ```

---

## 5. After Import: Data and Settings

Log in with an administrator account from the local database, then work through these **before** telling anyone the site is live.

### 5.1 Rewrite local URLs (Tools → Search & Replace)

Images picked from the Media Library are stored as absolute URLs on the domain they were picked on (`docs/core/media-library.md` §4). Back up the database first (phpMyAdmin → Export), then run each pass as dry run → check → **Replace** (`docs/core/tools.md`):

| Pass | Find | Replace with |
| --- | --- | --- |
| 1 | `https://workplace-induction.test` | `https://induction.example.com` |
| 2 | `http://workplace-induction.test` | `https://induction.example.com` |
| 3 | `workplace-induction.test` (dry run only) | Should find nothing |

- Tick every table: the dry run shows exactly where matches are, and protected columns are never touched.
- Replace both schemes with `https://`, so nothing loads over plain HTTP on the secure site.
- The logo is stored without a domain and needs nothing.

### 5.2 Settings (`docs/core/settings.md`)

- **General**: Company Name and Logo. Set **Primary Email** to a real address on the organisation's domain; it appears in every email footer.
- **Email**: both sender emails on a domain this server may send for. **Review CC and BCC**: addresses carried over from development get a copy of every email. Then follow the deliverability checklist in `docs/core/settings.md` §3 (SPF, DKIM, Send Test Email).
- **Notifications**: the administrator delivery (Instant or a report), which sections are included, and the reminder days.
- Locally, every email went to Mailpit. **On the server, every email is real from the first request.** The first cron run emails real inductees whose compliance is within the reminder window, and every active administrator gets the administrator emails.
- Optional: so the first administrator report covers only activity from go-live, run in phpMyAdmin `UPDATE email_settings SET admin_report_last_sent_at = NOW() WHERE id = 1;`.

### 5.3 Administrator accounts

- Create the real administrators (**Users → Add User**, type Administrator). A setup link needs working email; otherwise set a password and hand it over.
- Log in as one of them, then delete, or change the email of, any development administrator. `admin@example.com` from `seeds.sql` has a published default password. Every active administrator receives administrator emails, so one at `example.com` sends mail nowhere.

### 5.4 Media Library

**Upload Settings**: keep the maximum file size at or below PHP's `upload_max_filesize` (§3.1), or uploads under the app's limit still fail.

---

## 6. Go-Live Checks

| Check | Expect |
| --- | --- |
| `https://…/` | Landing page with the right name and logo; `http://` redirects to `https://` |
| `/database/schema.sql`, `/.git/HEAD`, `/config/app.php`, `/CLAUDE.md`, `/assets/uploads/media-library/` | 403 |
| Log in as an administrator | Dashboard figures match the local ones |
| Open the induction's Studio; check the browser's network panel | Every image loads, none from `workplace-induction.test`, no mixed-content warnings |
| Media Library: upload an image | Appears in the library |
| Settings → Email → Send Test Email, both audiences | Arrives, not in spam |
| Compliance → a record → View Certificate | The QR code and page use the live domain |
| `/forgot-password.php` with an administrator's email | The email's link uses the live domain |
| Settings → Notifications → Run Now; then the cron log the next day | Both report what was sent |
| cPanel → Errors, and `error_log` files in the document root | Empty |

Testing the inductee side on a live site needs a test account, which needs the site owner's approval (`docs/rules/testing.md` §2).

---

## 7. Updating the Live Site

1. **Back up** the live database and files (cPanel → Backup, or phpMyAdmin → Export plus the document root).
2. **Upload only code.** Tag each deployment (`git tag deployed-YYYY-MM-DD`), then bundle just what changed since the last one (Git Bash):

   ```bash
   git archive -o ../update.zip HEAD $(git diff --name-only --diff-filter=ACMR deployed-LAST HEAD -- . ':!config')
   git diff --name-only --diff-filter=D deployed-LAST HEAD     # files to delete on the server by hand
   ```

   Never upload over:
   - `config/database.php`, `config/app.php`: the server's credentials and URL (excluded above);
   - `assets/uploads/`: files uploaded on the live site exist only there. Extracting adds files, but never delete or replace the folder.
3. **Run the new migrations**, those dated after the last deployment, in date order (`docs/rules/data-protection.md` §7). SQL ones through phpMyAdmin; PHP ones over SSH, as a dry run first, then with `--commit`. Note the last one applied, since nothing records it.
4. **Never import a local database over the live one.** Content edited locally has to be recreated on the live site, or migrated deliberately.
5. Repeat the go-live checks the change touches.

To roll back: restore the backup from step 1, both files and database.

---

## 8. Copying Live Data to Laragon

For debugging with real data:

1. Export the live database (§2's `mysqldump`, or phpMyAdmin) and import it into a local database.
2. Search & Replace the live domain back to `http://workplace-induction.test`, or leave the images loading from the live site.
3. Local mail goes to Mailpit, so Run Now and the cron script are safe to run locally. Never point a copy of live data at a server with working mail and a cron job: it would send reminders to real inductees.
4. It holds real personal data. Delete the dump once imported, and treat the local database as real data (`docs/rules/data-protection.md` §1).
