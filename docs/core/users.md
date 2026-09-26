# Users

**Core.** Who can log in, how accounts are created and authenticated, and each user type's profile. Covers the admin **Users** section and **My Profile**.

The general security rules (sessions, CSRF, password hashing, server-side authorization) are in `docs/rules/security.md`. Test accounts are in `docs/rules/testing.md`.

---

## 1. User Types and Profiles

One `users` table holds identity and authentication state: email, password hash, `user_type`, `status`, `profile_completed`, email verification and password reset tokens. Each user type has its own profile table for everything else:

```text
users ─┬─ admin_profiles      first and last name
       └─ inductee_profiles   name, company, employment type, contact number,
                              job position, emergency contact name and phone
```

Profile rows are deleted with their user (`ON DELETE CASCADE`). This keeps `users` from becoming a pile of nullable fields for every user type.

| User type | `user_type` | Area | Home |
| --- | --- | --- | --- |
| **Administrator** | `admin` | `/admin/` | Dashboard: stats, compliance expiring soon, section shortcuts |
| **Inductee** | `inductee` | `/inductee/` | Dashboard: every active induction with its state (`docs/application/inductions.md` §4) |

- The user type is explicit. Never infer it from which profile fields exist.
- The **Administrator** is a Core user type with full access to every admin section. Application profile requirements never apply to administrators.
- There are no roles or permissions beyond the two types. A new kind of user would be a new user type with its own profile table.

---

## 2. Account Status

| Status | Meaning |
| --- | --- |
| Active | Can log in |
| Inactive | Can't log in; the account is kept |
| Suspended | Can't log in; the account is kept |

Status is checked at login (`AuthService::attemptLogin()`) and on every request after it: `Auth::user()` ends the session of an account that is no longer active or has been deleted, so suspending someone logs them out on their next page. Email verification is a separate state: an active account may still be unverified. Don't add statuses without a real need; for example, an account waiting for its setup link needs no "pending" status (§8).

---

## 3. Registration

`/register.php`, only when `registration_enabled` is true in `config/app.php`. It creates an **inductee** and asks only for an email and a password (at least 8 characters, entered twice):

```text
Register (email, password)
    ↓ users row: active, unverified, profile_completed = 0
    ↓ verification email (§4)
    ↓ inductee_registered hook → administrators told (docs/core/settings.md §4)
Verify email → Log in → Complete profile (§9) → Inductions
```

Everything else about the inductee is collected by profile completion after the first login, not at registration.

---

## 4. Email Verification

- The verification link (`/verify-email.php?token=…`) lasts 24 hours and is single-use. The token is random; opening the page without a valid token verifies nothing.
- **Inductees must verify before they can log in.** Administrators are created verified.
- **Lost or expired link:** logging in with the right password while unverified emails the link again, and the login page says so. The current link is resent while it lasts, and its 24 hours restart, so every copy works; an expired one is replaced. The invalid-link page tells the user to log in for a new one.
- **Resend limit:** at most one verification email per account per minute (`AuthService::RESEND_LIMIT_MINUTES`). Someone can register with another person's address and keep logging in, so without a limit this could flood that inbox. A login within the minute sends nothing, and the page says a link was sent moments ago. The time of the last email comes from the link's expiry, which is set when it is sent.
- Setting a password from a valid reset or setup link also verifies the email: the link was sent there, so using it proves ownership.
- On Edit User, an administrator can tick **Email verified** for an inductee whose email never arrived. It can't be unticked.

---

## 5. Login and Logout

```text
Email + password → find user → password_verify() → status active? → inductee verified? (no: email the link again, §4)
    → Auth::login() (new session id) → redirect (§6)
```

- A wrong email and a wrong password give the same message.
- Logout (`/logout.php`) clears the session and returns to the landing page.
- Authentication is one shared system (`App\Core\Auth`, `App\Core\Auth\AuthService`). Don't create `AdminAuth` / `InducteeAuth`.

---

## 6. Where Users Land After Login

After login, a user goes to their area's home (`Auth::homeUrl()`): `/admin/` or `/inductee/`.

A guest who opens a protected page (for example from an email link) is sent to login and brought back afterwards:

```text
/admin/users/index.php (logged out)
    → /login.php?redirect_to=%2Fadmin%2Fusers%2Findex.php
    → log in → /admin/users/index.php
```

- `Auth::requireLogin()` adds `redirect_to`, so every protected page gets it without changing links. Only GET requests are recorded; a form submission can't be replayed.
- `safe_redirect_path()` accepts only a root-relative path on this site, and `Auth::intendedUrl()` follows it only inside the user's own area. Anything else goes home.
- The login form keeps `redirect_to` through failed attempts. A logged-in user opening `/login.php?redirect_to=…` goes straight there.
- Profile completion (§9) uses the same `redirect_to`.

---

## 7. Forgot Password and Setup Links

`/forgot-password.php` → email with a link → `/reset-password.php?token=…` ("Set Your Password") → choose a password → log in.

- The response is the same whether or not the email exists.
- **Resend limit:** at most one reset email per account per minute (`AuthService::RESEND_LIMIT_MINUTES`). Anyone can request a reset for any address, so without a limit repeated requests could flood an inbox. A request within the minute sends nothing and changes nothing, so the link already sent keeps working, and the page gives its usual answer, adding that at most one is sent a minute. The time of the last email comes from the link's expiry. A setup link lasts days, so it only falls in that window for one minute of its life, and then delays a reset by a minute at most. Administrators sending setup links (§8) aren't limited.
- A reset link lasts 1 hour; a setup link (§8) lasts 7 days (`AuthService::SETUP_LINK_DAYS`). Both are single-use, and one page serves both.
- The token is stored as a SHA-256 hash in `users.password_reset_token` (`UserRepository` hashes it when storing and looking up). Only the email holds the token itself.
- Any password change clears the token, and setting one from a link also verifies the email (§4).
- Inductees change their password this way. Administrators can also change theirs on My Profile (§11).

---

## 8. Accounts Created by an Administrator

Admin → Users → **Add User**: email, user type, status, and how the user gets a password. The account is created verified, with no profile; an inductee completes theirs at first login (§9), an administrator's counts as complete.

- **Email a setup link** (the default): the account gets a random password nobody knows, and the user is emailed a setup link (§7) to choose their own. The administrator never sets or sees it, and no password is ever emailed. Only an **active** account can be sent one.
- **Set a password** and hand it over in person. Nothing is emailed.
- If the email fails, the account is still created and the administrator lands on Edit User to send it again.
- Edit User has **Send Setup Email** for a lost or expired link, or for an account that never had its own password. The current password keeps working until the link is used.

---

## 9. Profile Completion

A user can be logged in with an incomplete profile. Some pages need it complete.

| Part | Where |
| --- | --- |
| **State** (Core) | `users.profile_completed`. New accounts start at `0`; administrator accounts are created complete. |
| **Gate** (Core) | `Auth::requireCompletedProfile($profileUrl, $message)` sends the user to their profile page with the message, adding `redirect_to` for a GET. `Auth::profileCompleted()` reads the state. |
| **Requirements** (Application) | `App\Inductee\InducteeProfileService::REQUIRED_FIELDS`: first and last name, company, employment type, contact number, emergency contact name and phone. Job position is optional. A save needs all of them, so a successful save marks the profile complete. |
| **Where it applies** | Starting or completing an induction and taking an exam (`inductee/inductions/show.php`, `complete.php`, `inductee/exams/take.php`). The dashboard, compliance records and certificates never need it. |

- While incomplete, the inductee dashboard shows a notification that can't be dismissed, linking to the profile page.
- Completing it for the first time returns the inductee to the page they were stopped at, or the dashboard.
- On Edit User, an administrator can untick **Profile completed** so an inductee reviews their profile at the next visit. It can only be ticked when the saved profile already has every required field.

---

## 10. Admin: Users (`/admin/users/`)

- **List**: name, email, type, status, profile state, created; search and a user type filter. Rows link to Edit.
- **Add User**: §8.
- **Edit User**: email, status, Profile completed (inductees), Email verified (while unverified), and an optional new password. The user type can't be changed. A Password Setup Email card sends a setup link.
- **Switch Account** card: use the site as an inductee, without their password (below).
- **Delete User** is in the Danger Zone. A user with exam attempts or compliance records can only be deleted with **cascade**, which deletes those records too, in one transaction. Administrators can't delete their own account.

An administrator doesn't edit an inductee's profile details on Edit User; the inductee keeps them up to date on My Profile (an administrator can do it there by switching to their account).

### Switch Account

```text
Edit User → Switch Account (POST) → the inductee's dashboard, with a banner on every page
    → Switch Back (POST) → back on that Edit User page, as the administrator
```

- Administrators are trusted: a switched session is the inductee's account with full, normal access. Anything done in it (completing an induction, passing an exam, editing the profile) counts as the inductee's, emails included, and isn't logged as a switch.
- Only **active inductees** can be switched to, never another administrator (`UserManagementService::switchTo()`). The card stays visible but disabled otherwise, with the reason.
- The session keeps the administrator's id beside the inductee's (`Auth::switchTo()`, `Auth::switchedFrom()`), and gets a new session ID on the switch and on the way back (`docs/rules/security.md` §5).
- A switched session is an inductee's, so admin pages answer 403 and switching again isn't possible until Switch Back.
- **Switch Back** (`/admin/users/switch-back.php`) checks that the administrator's account is still an active administrator. If it isn't, the session ends and the login page says why.
- The banner (`views/partials/inductee-header.php`) names the account in use and holds Switch Back.
- **Log Out** while switched ends the whole session, the administrator's included. The same happens if the inductee's account is suspended, made inactive or deleted meanwhile (§2).

---

## 11. My Profile

From the account menu, for both user types:

| | Administrator (`/admin/profile/`) | Inductee (`/inductee/profile/`) |
| --- | --- | --- |
| Profile | First and last name | Every inductee profile field (§1) |
| Account | Email (read-only), change password | Email (read-only) |

Users can't change their own email; an administrator changes it on Edit User.

---

## 12. Core Boundary

Core authentication knows about users, email, passwords, verification, password recovery, sessions, user type, account status and whether a profile is complete. It does **not** know what an inductee must provide, which pages need a complete profile, or anything about inductions, exams or compliance. Those belong to the application and call into Core.

---

## 13. Files

| File | Role |
| --- | --- |
| `app/Core/Auth.php` | Session state and guards: `requireLogin`, `requireRole`, `requireCompletedProfile`, `homeUrl`, `intendedUrl`; `switchTo`, `switchedFrom`, `switchBack` (§10) |
| `app/Core/Auth/AuthService.php` | Register, login, verify, password reset and setup links |
| `app/Core/Auth/UserRepository.php` | `users` and profile names |
| `app/Admin/Services/UserManagementService.php` | Admin Users section and the administrator's My Profile |
| `app/Inductee/InducteeProfileService.php`, `InducteeProfileRepository.php` | Inductee profile, required fields |
| `login.php`, `register.php`, `verify-email.php`, `forgot-password.php`, `reset-password.php`, `logout.php`; `views/auth/` | Public auth pages |
| `admin/users/`, `admin/profile/`, `inductee/profile/` | Admin and self-service pages |
