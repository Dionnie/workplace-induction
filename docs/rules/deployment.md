# Deployment

Moving the site from a local Laragon install to a cPanel host, and updating it afterwards.

The first deployment copies the local database to the server. **From go-live on, the server's data is the real data**: data only ever flows from the server to a local copy, never the other way (`docs/rules/data-protection.md`).

---

## Start Here

| Task | Go to |
| --- | --- |
| First deployment | §2 to §6, in order. The cron job (§5.5) is set up last, because its first run emails real people. |
| Update the live site | §7 |
| Copy live data to Laragon | §8 |
| Something failed | Watch Out For, and the list after it |

Replace these placeholders in every command and path:

| Placeholder | Meaning | Example |
| --- | --- | --- |
| `induction.example.com` | The live domain | `induction.yourcompany.com` |
| `USER` | The cPanel account name | `yourcpanel` |
| `USER_induction` | The database name and user cPanel creates: the account name, `_`, then `induction` | `yourcpanel_induction` |
| `YYYY-MM-DD` | The deployment date | `2026-10-13` |

Where to run commands:

- **Laragon's terminal** (Cmder), in the project folder: local `php`, `mysqldump` and `git`.
- **cPanel → Terminal**, or SSH: server commands. Paths start with `/home/USER/`.
- **Git Bash**: only the update commands in §7, which use `$(…)`.

---

## Watch Out For

Each of these breaks something quietly. The sections below cover them.

| Trap | What goes wrong | Section |
| --- | --- | --- |
| Site in a subfolder (`example.com/induction/`) | Every link and asset breaks: URLs are root-relative | §1 |
| Dump or zip left in the document root | `.htaccess` blocks the `database/` folder but not `.sql` or `.zip` files elsewhere, so the data or source can be downloaded | §3.2, §3.3 |
| `.htaccess` not uploaded | Source, `.git`, schema and docs are downloadable | §3.3, §6 |
| Credentials set only with `SetEnv` | Cron can't connect: command-line PHP doesn't see Apache variables | §3.4 |
| `url` not set in `config.php` | Emails sent by cron link to `http://localhost/…` | §3.4 |
| Cron uses a different PHP binary | Wrong PHP version, or none | §3.1, §5.5 |
| Cron scheduled before §5 is done | Real inductees get reminders, and the development To list gets the admin report | §5.5 |
| `timezone` in `config.php` changed from the one the data was made in | New times jump against old ones; certificates dated in the wrong zone | §4 |
| Image URLs still on the local domain | Content images load from `workplace-induction.test`, which doesn't exist | §5.1 |
| Only `http://` replaced, not `https://` (or the reverse) | Some images still broken, or mixed-content warnings | §5.1 |
| Development email settings carried over | Mail sent from `example.com` is rejected; administrator emails and CC/BCC copies go to development addresses | §5.2 |
| Default `admin@example.com` still active | A published password | §5.3 |
| PHP upload limit below the Media Library limit | Uploads fail with a size error | §3.1, §5.4 |
| Update bundle built from an empty file list | `git archive` with no paths bundles the whole project, not just the changes | §7 |
| Deleted-files list followed blindly on the update that stopped tracking `config.php` | The server's `config.php` is deleted and the site stops | §7 |
| Server's old `config/` files not carried over when updating | Without `config.php` the site can't connect; without its `url`, cron emails link to `http://localhost/…` | §7 |
| Local database imported over a live site | Everything done on the live site since go-live is lost | §7 |
| Live copy imported over the local database without a backup | Local records from the previous system are lost | §8 |

**Where to look when something fails**

- **PHP errors:** cPanel → Errors. The page itself shows only a generic error, since `display_errors` is off.
- **Cron:** `/home/USER/logs/induction-cron.log` (§5.5).
- **Emails:** check the spam folder, then run Send Test Email (`docs/core/settings.md` §3). Locally, Mailpit is at `http://localhost:8025`.

---

## 1. Requirements

| Need | Detail |
| --- | --- |
| Domain | Its own domain or subdomain (`induction.example.com`), with the project folder as the document root. **Not a subfolder**: every URL in the app starts at `/`. |
| PHP | 8.0 or later (`ea-php80` through `ea-php83`). Extensions `pdo_mysql` and `mbstring` (standard on cPanel). `array_is_list()` is polyfilled in `helpers.php` for PHP 8.0. |
| Database | MySQL 8, or MariaDB 10.5+. The dump has no MySQL-only syntax, and MariaDB accepts the `json` columns. |
| Web server | Apache or LiteSpeed with `mod_rewrite` and `.htaccess` (cPanel's default) |
| HTTPS | AutoSSL, or any certificate |
| Access | SSH or cPanel Terminal, for the checks in §3.1, §4 and §5.5, and any migration scripts |

---

## 2. Prepare on Laragon

1. **Commit everything.** The bundle is built from git, so uncommitted files are left out, including `.htaccess`.
   **Check:** `git status` shows nothing to commit.
2. **Bring the local database up to date.** Apply any migration in `database/migrations/` that the local database hasn't had, in date order (`docs/rules/data-protection.md` §7). Nothing records which ones have run, so keep a note of the last one applied.
   **Check:** a PHP migration's dry run reports nothing to change.
3. **No test accounts left.**
   **Check:** `php database/test-users.php list` ends with `0 test account(s).`
4. **Export the local database**, from Laragon's terminal in the project folder. `-uroot` is Laragon's default login, which has no password (as in `config.php`):

   ```text
   mysqldump -uroot --single-transaction --no-tablespaces --set-gtid-purged=OFF --default-character-set=utf8mb4 --result-file=induction-YYYY-MM-DD.sql workplace-induction
   ```

   `--result-file` writes the file itself, so the dump isn't passed through the shell. Don't add `--databases`: it writes the local database name into the dump. If `mysqldump` isn't found, use its full path, `<laragon>\bin\mysql\<version>\bin\mysqldump.exe`.

   The dump holds inductees' personal details. Keep it off shared drives, and delete it once imported.

   **Check:** the file starts with `-- MySQL dump` and has a `CREATE TABLE` for each table in `database/schema.sql`.
5. **Build the file bundle:**

   ```text
   git archive --format=zip -o ../induction-YYYY-MM-DD.zip HEAD
   ```

   The bundle holds every tracked file, including `.htaccess`, `assets/uploads/` and the template `config.example.php`. It leaves out `config.php`, which isn't tracked (you make the server's in §3.4), and `.git/`, `.claude/` and `database/backup/`. `docs/` and `AGENTS.md` go along, and `.htaccess` blocks them.

   **Check:** open the zip. `.htaccess`, `index.php` and `config.example.php` are inside, and there is no `config.php` and no `.git` folder.

---

## 3. Set Up the Server

### 3.1 Domain and PHP

- **Domain:** create the domain or subdomain, with a document root of its own, e.g. `/home/USER/induction.example.com`.
- **MultiPHP Manager:** PHP 8.0, 8.1, 8.2 or 8.3 (`ea-php80` through `ea-php83`) for that domain.
- **Command-line PHP:** the cron job and the checks in §4 use the server's CLI binary. Its path depends on the host. `ls /opt/cpanel/` lists the installed versions (`ea-php80`, `ea-php83`, etc.; on CloudLinux hosts it's `/opt/alt/php80/usr/bin/php`). Use the full path, not plain `php`:

  ```text
  /opt/cpanel/ea-php80/root/usr/bin/php -v
  ```

  **Check:** it reports PHP 8.0 or later.
- **MultiPHP INI Editor**, for that domain:

  | Setting | Value |
  | --- | --- |
  | `display_errors` | Off |
  | `log_errors` | On |
  | `upload_max_filesize`, `post_max_size` | At least the Media Library maximum (5 MB by default; Upload Settings goes up to 50 MB), e.g. `16M` for the default |

### 3.2 Database

1. **MySQL Databases:** create a database and a user; cPanel prefixes both with the account name (`USER_induction`). Add the user to the database with **All Privileges**.
2. **Import the dump.** Upload `induction-YYYY-MM-DD.sql` to `/home/USER/`, outside the document root. `.htaccess` blocks the `database/` folder, not `.sql` files elsewhere. Then import it in phpMyAdmin (the database → **Import**), or over SSH:

   ```text
   mysql -u USER_induction -p USER_induction < /home/USER/induction-YYYY-MM-DD.sql
   ```

   If phpMyAdmin refuses the file as too large, use SSH. Delete the dump from the server once it's imported.
3. **Check:** `users` and `compliance_records` have the same row counts as locally (`SELECT COUNT(*) FROM users;`, run in both databases).

### 3.3 Files

1. **Upload the zip to `/home/USER/`, outside the document root,** then extract it into the document root with File Manager's **Extract**. The project's root, with `index.php` and `.htaccess`, must *be* the document root, not a folder inside it. Delete the zip afterwards: `.htaccess` doesn't block `.zip` files.
2. **Permissions:** folders 755, files 644. On cPanel, PHP normally runs as the account user, so `assets/uploads/media-library/` is writable at 755. The upload check in §6 confirms it.
3. **Check:** `.htaccess` arrived. Some upload tools skip dotfiles; turn on *Show Hidden Files* in File Manager's settings to see it.

### 3.4 Configuration

`config.php`, in the project root, holds the database connection, the site URL and the timezone. It isn't in the repository: each server, Laragon included, has its own. Make the server's by copying `config.example.php` to `config.php` in the document root (File Manager → **Copy**), then set its values. Change the value after `?:`; the `getenv(...)` part stays.

```php
// config.php
'database' => [
    'host'     => getenv('DB_HOST') ?: 'localhost',          // localhost, not 127.0.0.1: cPanel grants user@localhost
    'database' => getenv('DB_DATABASE') ?: 'USER_induction',
    'username' => getenv('DB_USERNAME') ?: 'USER_induction',
    'password' => getenv('DB_PASSWORD') ?: '…',
    // driver, port and charset stay as they are
],
'url' => getenv('APP_URL') ?: 'https://induction.example.com',  // no trailing slash
'timezone' => getenv('APP_TIMEZONE') ?: 'Australia/Melbourne', // keep as is: see §4
'environment' => getenv('APP_ENV') ?: 'production',           // 'production', 'staging' (auto-blocks crawlers)
```

- **Edit the file rather than setting environment variables.** Variables set with `SetEnv` in `.htaccess` reach web requests only. The cron job runs command-line PHP, which would fall back to the local defaults and fail to connect.
- **`url` is required.** Emails sent by cron have no web request to take the domain from, so without it their links point to `http://localhost`.
- `.gitignore` keeps `config.php` out of git, so server values can't be committed and update bundles never replace the server's copy (§7). `.htaccess` blocks both files from the web (`docs/rules/security.md` §7).
- A missing `config.php` stops every page with an error saying so.
- Because these values live in a file, you can fix them even when the site is down (a 500 error, no way to log in): edit `config.php` in File Manager or over SSH.
- **Check:** the command in §4 runs without an error, and this prints the live address:

  ```text
  /opt/cpanel/ea-php83/root/usr/bin/php -r "require 'bootstrap.php'; echo public_url('/'), PHP_EOL;"
  ```

### 3.5 HTTPS

- Issue the certificate (**SSL/TLS Status** → AutoSSL), then turn on **Domains → Force HTTPS Redirect**.
- The session cookie is `Secure` only when PHP sees the request as HTTPS (`bootstrap.php`). Behind a proxy that ends TLS itself (e.g. Cloudflare's "Flexible" mode), it won't be; use "Full" mode.
- **Check:** `https://induction.example.com/` loads with no certificate warning.

---

## 4. Timezone

`'timezone'` in `config.php` (§3.4) is the organisation's timezone. `bootstrap.php` sets it for PHP, and `App\Core\Database` sets the same offset on every MySQL connection. PHP dates (certificate issue and expiry dates) and MySQL's (`NOW()`, `CURDATE()`, `created_at`) therefore agree, on web requests and in cron, whatever the server's own zones. The server's `date.timezone` and MySQL's global zone don't matter to the app.

- **Keep the value the records were made in.** `DATETIME` columns store no zone, so the imported data's times are in the zone the local install used when they were recorded: `Australia/Melbourne` (UTC+10, UTC+11 in daylight saving) from 2026-10-14, `Asia/Singapore` (UTC+8) before that. Changing the setting later makes new times jump against old ones; treat that as a data change (`docs/rules/data-protection.md` §4).
- An invalid name stops every page with an error naming the `timezone` in `config.php`. On the server, that goes to cPanel → Errors, since `display_errors` is off.
- **Check** from the project folder, with the server's CLI PHP (§3.1). Locally, use plain `php`:

  ```text
  /opt/cpanel/ea-php83/root/usr/bin/php -r "require 'bootstrap.php'; echo date('c'), '  ', App\Core\Database::now()->format('Y-m-d H:i:s'), PHP_EOL;"
  ```

  Both values must show the organisation's local time, for example `2026-10-14T10:00:00+11:00  2026-10-14 10:00:00`. The second value comes from MySQL, so the check also proves the command line can reach the database. Don't use phpMyAdmin's `SELECT NOW()` for this: it shows the server's zone, not the app's.

---

## 5. After Import: Data, Settings and Cron

Log in with an administrator account from the local database, then work through §5.1 to §5.4 **before** telling anyone the site is live. Set up the cron job last (§5.5).

### 5.1 Rewrite Local URLs (Tools → Search & Replace)

Images picked from the Media Library are stored as absolute URLs on the domain they were picked on (`docs/core/media-library.md` §4). Back up the database first (phpMyAdmin → Export). Then run each pass as dry run → check → **Replace** (`docs/core/tools.md` §2):

| Pass | Find | Replace with |
| --- | --- | --- |
| 1 | `https://workplace-induction.test` | `https://induction.example.com` |
| 2 | `http://workplace-induction.test` | `https://induction.example.com` |
| 3 | `workplace-induction.test` (dry run only) | Should find nothing |

- Tick every table: the dry run shows exactly where matches are, and protected columns are never touched.
- Replace both schemes with `https://`, so nothing loads over plain HTTP on the secure site.
- The logo is stored without a domain and needs nothing.
- **Check:** after each Replace, the page reloads the dry run, which should find nothing for that text. Pass 3 must find nothing before you go on.

### 5.2 Settings (`docs/core/settings.md`)

- **General**: Company Name and Logo, and **Search Engine Visibility** (discourage search engines). Staging servers should have `'environment' => 'staging'` in `config.php`, which automatically blocks search crawlers regardless of database settings. Set **Primary Email** to a real address on the organisation's domain; it appears in every email footer.
- **Email**: both From Emails on a domain this server may send for. **Review the administrator To, CC and BCC**: addresses carried over from development get the administrator emails, or a copy of every email. Then follow the deliverability checklist in `docs/core/settings.md` §3 (SPF, DKIM, Send Test Email).
- **Notifications**: the administrator delivery (Instant or a report), which sections are included, and the reminder days.
- **Check:** every address in To, CC and BCC should receive these emails.
- Locally, every email went to Mailpit. **On the server, every email is real from the first request.** Once the cron job runs (§5.5), inductees whose compliance falls inside the reminder window get real reminders.
- Optional: so the first administrator report covers only activity from go-live, record the last report as sent now. Run all three lines together in phpMyAdmin. `SET time_zone` makes `NOW()` use the app's zone for that session (Australia/Melbourne is `+11:00` in daylight saving, from the first Sunday in October to the first Sunday in April, and `+10:00` otherwise). Without it, `NOW()` uses the server's zone and the window is wrong.

  ```sql
  SET time_zone = '+11:00';
  UPDATE email_settings SET admin_report_last_sent_at = NOW() WHERE id = 1;
  SELECT admin_report_last_sent_at, NOW() FROM email_settings WHERE id = 1;
  ```

  **Check:** the two values on the last line match, and show the organisation's local time.

### 5.3 Administrator Accounts

- Create the real administrators: **Users → Add User**, type **Administrator**. A setup link needs working email; otherwise set a password and hand it over (`docs/core/users.md` §8).
- Log in as one of them, then delete any development administrator (**Delete User**, in the Danger Zone). `admin@example.com` from `database/seeds.sql` has a published default password. Administrator accounts don't receive administrator emails by being administrators; who does is the To list under Settings → Email (§5.2).
- **Check:** Users, filtered to administrators, lists only the real people.

### 5.4 Media Library

**Upload Settings**: keep the maximum file size at or below PHP's `upload_max_filesize` (§3.1), or uploads under the app's limit still fail. The upload check is in §6.

### 5.5 Schedule the Cron Job (Last)

`cron/send-notifications.php` sends the inductee expiry reminders, and the administrator report when a new period has started, once a day. What it sends: `docs/core/settings.md` §4–5.

**Do this only after §5.1 to §5.4.** From the first run on, it emails real inductees, and the administrator To list gets the report. Its links need `url` in `config.php` (§3.4).

1. **Create the log folder** if it doesn't exist: `/home/USER/logs/` (File Manager → New Folder, or `mkdir -p /home/USER/logs` over SSH). Without it, the job won't run at all.
2. **Add the job** in **Cron Jobs**, once a day (e.g. `0 2 * * *`, server time), using the CLI PHP from §3.1:

   ```text
   /opt/cpanel/ea-php83/root/usr/bin/php /home/USER/induction.example.com/cron/send-notifications.php >> /home/USER/logs/induction-cron.log 2>&1
   ```

3. **Don't test it by running the script by hand.** It sends real emails straight away, and Notifications → **Run Now** does the same from the browser. Check the PHP binary and the database connection with §3.1 and §4 instead.
4. Use only one of `send-notifications.php` and `send-expiry-reminders.php`. The second is an old name that still works for existing schedules (`docs/core/settings.md` §5).

**Check:** the next morning, `tail -n 20 /home/USER/logs/induction-cron.log` shows two lines for the run: how many expiry reminders were sent, and the administrator report's status.

---

## 6. Go-Live Checks

Work down the list, and tick each row once it passes.

| ☐ | Check | Expect |
| --- | --- | --- |
| ☐ | `https://induction.example.com/` | Landing page with the right name and logo. `http://` redirects to `https://`. |
| ☐ | `/database/schema.sql`, `/.git/HEAD`, `/config.php`, `/config.example.php`, `/AGENTS.md`, `/assets/uploads/media-library/` | 403 (`docs/rules/security.md` §7) |
| ☐ | `/error_log` | Not downloadable (404 or 403). If the file opens, PHP's log is public: block it with a rule in `.htaccess` (`docs/rules/security.md` §7) before go-live. |
| ☐ | Log in as an administrator | The dashboard loads. Right after the first import its figures match the local ones; after go-live they drift, as the server gets new data. |
| ☐ | Open an induction's Studio, and check the browser's network panel | Every image loads. None come from `workplace-induction.test`. No mixed-content warnings. |
| ☐ | Media Library: upload an image | It appears in the library. Then select it and **Delete Selected**. |
| ☐ | Settings → Email → Send Test Email, both audiences | Both arrive, not in spam. The administrator sample goes to the To list (real people); the inductee sample goes to you. |
| ☐ | Compliance → a record → **View Certificate** | The QR code and the page use the live domain |
| ☐ | `/forgot-password.php` with your own administrator email | The email's link uses the live domain. It's a real reset email. |
| ☐ | cPanel → Errors | Nothing new since the checks started |
| ☐ | The cron log, the next morning | §5.5 |

Testing the inductee side on a live site needs a test account, which needs the site owner's approval (`docs/rules/testing.md` §2).

**Tag the deployment** once every row passes, as §7 step 7 describes. §7 diffs from the latest `deployed-*` tag, so the tag must match what's live.

---

## 7. Updating the Live Site

Run these in Git Bash, from the project folder. Only files changed since the last deployment go up.

1. **Back up** the live database and files (cPanel → Backup, or phpMyAdmin → Export plus the files). Keep backups outside the document root.
2. **Find the changes** since the last deployment's tag:

   ```bash
   LAST=$(git describe --tags --match "deployed-*" --abbrev=0)
   FILES=$(git diff --name-only --diff-filter=ACMR "$LAST" HEAD)
   echo "$FILES"
   git diff --name-only --diff-filter=D "$LAST" HEAD -- . ':!config.php'
   git diff "$LAST" HEAD -- config.example.php
   ```

   - The first list is what to upload. **Check** that it holds only the files you expect. It can't hold `config.php`, which isn't tracked.
   - The second list is deleted files: remove those on the server by hand. It leaves out `config.php`: an update from before git stopped tracking it would otherwise list it as deleted, and deleting the server's copy stops the site.
   - The last command shows changes to `config.example.php`: add any new or changed setting to the server's `config.php` by hand (§3.4).

3. **Bundle the upload.** An empty first list would make `git archive` bundle the whole project, so the command only runs when the list isn't empty:

   ```bash
   test -n "$FILES" && git archive --format=zip -o ../update-YYYY-MM-DD.zip HEAD $FILES
   ```

4. **Upload and extract.** Upload the zip to `/home/USER/`, extract it into the document root, then delete the zip. Remove the deleted files from step 2. Never upload over:
   - `config.php`: the server's credentials and timezone. Bundles never hold it, so only a hand upload can replace it;
   - `assets/uploads/`: files uploaded on the live site exist only there. Extracting adds files, but never delete or replace the folder.
5. **Run the new migrations**, those dated after the last deployment, in date order (`docs/rules/data-protection.md` §7). Each migration's header gives its exact command. SQL ones through phpMyAdmin; PHP ones over SSH, as a dry run first, then with `--commit`. Note the last one applied, since nothing records it.
6. **Never import a local database over the live one.** Content edited locally has to be recreated on the live site, or migrated deliberately.
7. **Check, then tag.** Repeat the go-live checks the change touches (§6). Once the site is right, tag the commit that's live: `git tag deployed-YYYY-MM-DD` (add `-2` if there's already one that day). Tag only after the upload is confirmed, so the next update diffs from what's really on the server.

**If the server still has a `config/` folder** (`app.php` and `database.php`), the update that replaces it with `config.php` needs extra steps: the database connection, the site URL and the timezone all move to `config.php`.

- Before anything else, note the server's database settings in `config/database.php`, and the `url` and `timezone` in `config/app.php`. A custom `tagline` or `description` there now goes in `index.php`, and registration is now always open, whatever `registration_enabled` said.
- Create `config.php` in the document root from the repository's `config.example.php`, with the noted values (§3.4). The old code ignores it, and the update bundle leaves it out, so it isn't overwritten.
- Upload the update, then delete the `config/` folder (its files are in step 2's list of deleted files).
- A server that ran `database/migrations/2026-10-13-site-settings-app-config.sql` (since removed) has an unused `site_settings.site_url` column. The code ignores it; drop it after a backup if you like.
- **Check**, before the next cron run: the §4 command shows the organisation's time, and the §3.4 check prints the live address.

To roll back: restore the backup from step 1, both files and database. Anything written to the live site since that backup is lost, so agree that with the site owner first.

---

## 8. Copying Live Data to Laragon

For debugging with real data. Importing a copy replaces your local data, so back that up first.

1. **Back up the local database:** run the §2 export command with a different file name, e.g. `local-backup-YYYY-MM-DD.sql`. The local database holds real records (`docs/rules/data-protection.md` §1).
2. **Export the live database:** in phpMyAdmin, choose the database → **Export** → **Quick**, format **SQL**, and save it to your computer.
   **Check:** the file has no `CREATE DATABASE` or `USE` lines, which would send the import to another database.
3. **Import it into the local database**, in Laragon's terminal or Git Bash (PowerShell has no `<`):

   ```text
   mysql -uroot workplace-induction < live-YYYY-MM-DD.sql
   ```

4. **Point images at the local site** (Tools → Search & Replace, dry run first): Find `https://induction.example.com`, Replace with `http://workplace-induction.test`, or leave the images loading from the live site. Links stay local by themselves: each server's `config.php` has its own `url`.
   **Check:** after the Replace, the dry run finds nothing for the live domain.
5. **Keep copies of live data away from live mail.** Locally, all mail goes to Mailpit, so Run Now and the cron script are safe to run here. Never point a copy of live data at a server with working mail and a cron job: it would send reminders to real inductees.
6. **Delete both dump files** once imported.
