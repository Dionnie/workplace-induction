# Authentication

## Purpose

Define the authentication system for the application.

Authentication is a **Core** capability. It manages user identity, login, registration, email verification, password recovery, sessions, and access to authenticated areas.

Application-specific requirements must not be embedded into the Core authentication system.

---

# 1. User Identity

The application has one central `users` table for authentication.

The `users` table represents the user's identity and authentication state.

Conceptually:

```text
users
├── id
├── email
├── password
├── user_type
├── email_verified_at
├── status
├── profile_completed
├── created_at
└── updated_at
```

`profile_completed` records whether the user has completed the profile their user type requires (section 12).

The exact schema is defined in:

```text
/database/schema.sql
```

Do not duplicate the database definition in this document.

---

# 2. User Type Profiles

Each user type has its own profile table.

Example:

```text
users
  │
  ├── admin_profiles
  │
  └── inductee_profiles
```

The profile table is linked to the corresponding user.

Example:

```text
admin_profiles
- user_id
- profile-specific fields

inductee_profiles
- user_id
- first_name
- last_name
- profile-specific fields
```

Additional user types should have their own profile table when required.

```text
users
├── admin_profiles
├── inductee_profiles
└── [other]_profiles
```

### Why profiles are separate

Authentication data and user-type data have different responsibilities.

`users` handles:

- Identity
- Email
- Password
- User type
- Account status
- Email verification
- Authentication state

Profile tables handle:

- User-type-specific information
- User-type-specific requirements
- Application-specific profile data

This prevents the central `users` table from becoming a large collection of nullable fields for every possible user type.

---

# 3. Admin User

The Administrator is a **Core user type**.

Administrators are not subject to application-specific profile requirements that apply to other user types.

For example, if an Inductee must provide:

```text
First Name
Last Name
```

that requirement applies to the Inductee profile, not to the Administrator.

The authentication system must not require application-specific fields from Administrators.

Administrators can therefore have a minimal profile appropriate for administration.

---

# 4. Administrator Access

The Administrator operates as the application's highest-privilege user.

Conceptually:

```text
Administrator
    ↓
Full application access
    ↓
Manage
Monitor
Configure
Review
```

Administrators can access and manage the application's Core and Application functionality, subject to explicit security restrictions that must apply to all users.

"God mode" means broad application authority; it does **not** mean bypassing fundamental security controls.

For example, administrators must still be subject to:

- Authentication
- Session security
- CSRF protection
- Server-side authorization
- Password security
- Secure file handling
- Audit requirements where applicable

Do not create separate security mechanisms for administrators unless a real requirement exists.

---

# 5. Registration

Registration creates a user account.

The registration process should:

1. Validate the submitted email
2. Validate the password
3. Determine the permitted user type
4. Create the central user record
5. Create the corresponding profile record when required
6. Generate an email verification token
7. Send the verification email
8. Sign in or redirect according to the application's registration flow

Application-specific profile requirements belong to the corresponding profile, and are collected after login through profile completion (section 12), not at registration.

In this application, public registration asks only for an email and a password:

```text
Inductee Registration (email, password)
        ↓
Create users record (profile_completed = 0)
        ↓
Verify email → Log in
        ↓
Complete profile → create inductee_profiles record
        ↓
Inductions available
```

Accounts created by an administrator follow the same path: the administrator enters only `users` fields, and the inductee completes their own profile at their first login.

### Accounts created by an administrator

An administrator-created account is treated as verified. On Add User, the administrator either:

- **Emails a setup link** (the default): the user gets an "account setup" email with a link to choose their own password. The administrator never sets or sees it, and no password is ever emailed.
- **Sets a password themselves** and hands it over in person. Nothing is emailed.

```text
Add User (email a setup link)
        ↓
Create users record with a random password nobody knows
        ↓
Password reset token that lasts 7 days (AuthService::SETUP_LINK_DAYS)
        ↓
Account setup email → /reset-password.php?token=…
        ↓
User chooses a password → Log in
```

- **No extra account state.** Until the link is used, no one knows the password, so the account can't be used. A "pending" status isn't needed (section 11).
- **It is a password reset link** (section 9) with a longer expiry, since the user isn't expecting it. If it expires, "Forgot your password?" sends a new link.
- **Only active accounts** can be sent one. Any other user couldn't log in with it.
- **Resending:** Edit User has **Send Setup Email** for a lost or expired link, or for an existing account that never had its own password. The current password keeps working until the link is used.
- **Failed email:** the account is still created. The administrator is taken to Edit User to send it again.

An Administrator should not be forced through Inductee requirements simply because both are users.

---

# 6. Email Verification

New accounts that require email verification must verify ownership of their email address before accessing functionality that requires a verified account.

The verification flow is:

```text
Register
   ↓
Create Account
   ↓
Generate Verification Token
   ↓
Send Verification Email
   ↓
User Opens Verification Link
   ↓
Validate Token
   ↓
Mark Email Verified
   ↓
Continue
```

Verification tokens must be:

- Cryptographically secure
- Time-limited
- Single-use
- Invalidated after successful verification

The application must not treat a user as verified merely because a verification URL was opened without a valid token.

Setting a password from a valid reset or account setup link also marks the email verified. The link was sent to that address, so using it proves the user owns it.

---

# 7. Login

Login authenticates an existing user.

The login flow is:

```text
Email
+
Password
   ↓
Find User
   ↓
Verify Password
   ↓
Check Account Status
   ↓
Check Required Authentication State
   ↓
Create Session
   ↓
Redirect to Appropriate Area
```

Passwords must be verified using:

```php
password_verify()
```

Passwords must never be compared as plain text.

After successful authentication, regenerate the session ID to prevent session fixation.

---

# 8. Authentication Redirects

After login, the user is redirected according to their user type and application access.

Example:

```text
Admin
  → /admin/

Inductee
  → /inductee/
```

These URLs are organizational entry points.

They are **not** security boundaries.

### Returning to the requested page (`redirect_to`)

A guest who opens a protected page (for example a link in a notification email) is sent to the login page and, after logging in, back to that page:

```text
/admin/users/index.php (not logged in)
        ↓
/login.php?redirect_to=%2Fadmin%2Fusers%2Findex.php
        ↓
Log in
        ↓
/admin/users/index.php
```

- **Where it is set:** `Auth::requireLogin()`, so every protected page gets it and links need no changes.
- **Only paths on this site:** `safe_redirect_path()` accepts a root-relative path only. Absolute and protocol-relative URLs, backslashes and control characters are rejected, so a crafted link can't redirect to another site.
- **Only the user's own area:** `Auth::intendedUrl()` follows `redirect_to` only when it is under the user's area (`/admin/` or `/inductee/`). Anything else goes to `Auth::homeUrl()`, the entry point above.
- **GET only:** a form submission (POST) is never recorded, since a redirect can't repeat it.
- **Kept through failed attempts:** the login form carries it in a hidden field, and a failed login returns to `/login.php?redirect_to=…`.
- **Already logged in:** `/login.php?redirect_to=…` sends a logged-in user straight there.

Profile completion (section 12) uses the same `redirect_to` to return the user to the page that needed a completed profile.

Every protected request must still perform server-side authentication and authorization checks.

A user must never gain access merely by manually entering another user's URL.

---

# 9. Forgot Password

Password recovery allows a user to regain access without exposing their existing password.

The flow is:

```text
Forgot Password
      ↓
Enter Email
      ↓
Generate Reset Token
      ↓
Send Reset Email
      ↓
User Opens Link
      ↓
Validate Token
      ↓
Set New Password
      ↓
Invalidate Token
      ↓
Login
```

The application should not reveal whether an email address exists in the system.

For example, the request should return a generic response such as:

```text
If an account exists for that email address,
a password reset link has been sent.
```

Reset tokens must be:

- Cryptographically secure
- Time-limited
- Single-use
- Stored securely
- Invalidated after use

The existing password must never be sent by email.

---

# 10. Password Reset Security

When a password is successfully changed:

- Hash the new password using `password_hash()`
- Invalidate the reset token
- Invalidate other active password-reset tokens for that account
- Consider invalidating existing authenticated sessions where appropriate

Never store passwords or reset tokens in plain text when a secure hashed representation is appropriate.

Reset tokens (including account setup links, section 5) are stored as a SHA-256 hash in `users.password_reset_token`. Only the email holds the token itself, so a copy of the database can't be used to set anyone's password. `UserRepository` hashes the token when storing it and when looking it up.

The same page, `/reset-password.php` ("Set Your Password"), serves both reset and setup links.

---

# 11. Account Status

Authentication should respect the user's account status.

Possible states depend on the final schema, but the system should support the concept of an account being unable to authenticate even when the password is correct.

Examples include:

```text
Active
Inactive
Suspended
```

Do not invent additional account states unless they are actually required.

Account status is different from email verification.

For example:

```text
Account Status: Active
Email: Unverified
```

is a valid state during registration.

---

# 12. Profile Completion

Profile completion is separate from authentication.

A user may successfully authenticate while still having an incomplete profile.

For example:

```text
Login
  ↓
Authenticated
  ↓
Profile incomplete
  ↓
Complete required profile
  ↓
Application access
```

Profile completion requirements are defined by the user's profile type.

Example:

```text
Inductee
→ first_name required
→ last_name required
→ other application requirements
```

The Core authentication system should provide the authentication state but should not contain these application-specific requirements.

Administrators are exempt from application-specific profile requirements unless an explicit Core requirement exists.

### How it is implemented

- **State (Core):** `users.profile_completed` (`TINYINT(1)`, default `0`). New accounts start incomplete. Administrator accounts are created complete, since they have no requirements. Accounts imported from the legacy system were all marked complete (`database/migrations/2026-09-25-users-profile-completed.sql`).
- **Gate (Core):** `Auth::requireCompletedProfile($profileUrl, $message)` sends a user with an incomplete profile to their profile page with a message, the same way `Auth::requireRole()` guards a role. For a GET request it adds `redirect_to` with the requested page (section 8). `Auth::profileCompleted()` reads the state.
- **Requirements (application):** `App\Inductee\InducteeProfileService::REQUIRED_FIELDS`: first and last name, company, employment type, contact number, and emergency contact name and phone. Job position is optional. A profile save needs all of them, so a successful save marks the profile complete.
- **Where it applies:** starting or completing an induction and taking an exam (`inductee/inductions/show.php`, `complete.php`, `inductee/exams/take.php`). Viewing the dashboard, compliance records and certificates never needs it.
- **Telling the user:** while the profile is incomplete, the dashboard shows a notification that can't be dismissed, linking to the profile page. Completing the profile for the first time returns the inductee to the page they were stopped at (`redirect_to`, e.g. an email link to an induction), or to the dashboard when there is none.
- **Administration:** on Edit User, an administrator can untick "Profile completed" so an inductee reviews their profile at the next visit. They can only tick it when the saved profile already has every required field.

---

# 13. Authentication vs Authorization

Authentication answers:

> Who is this user?

Authorization answers:

> Is this user allowed to perform this action?

These are separate concerns.

```text
Authentication
    ↓
User identified
    ↓
Authorization
    ↓
Access allowed or denied
```

Do not rely on hiding links or buttons for security.

Authorization must always be checked server-side.

---

# 14. Sessions

Authenticated users use secure server-side sessions.

Sessions must:

- Use secure cookies where HTTPS is available
- Use `HttpOnly`
- Use appropriate `SameSite` protection
- Regenerate the session ID after login
- Be destroyed on logout
- Expire according to the application's session policy

Do not store sensitive authentication credentials directly in browser storage.

---

# 15. Logout

Logout should:

1. Destroy the authenticated session
2. Remove the authentication state
3. Redirect the user to an appropriate public page or login page

Logout should not require the user to manually clear browser data.

---

# 16. User-Type Separation

User types must remain explicit.

Do not determine a user's type from the existence of arbitrary profile fields.

Use the user's authenticated identity and defined user type.

Example:

```text
users.user_type = admin
        ↓
admin_profiles
        ↓
/admin/
```

```text
users.user_type = inductee
        ↓
inductee_profiles
        ↓
/inductee/
```

A user's profile data must not be used as a substitute for authorization.

---

# 17. Core Boundary

Authentication belongs to Core.

Core authentication should know about:

```text
User
Email
Password
Verification
Password Recovery
Session
User Type
Account Status
```

Core authentication should **not** contain application-specific rules such as:

```text
Inductee requirements
Application workflows
Application completion rules
Application records
Application-specific eligibility
Application-specific compliance
```

Those belong to the Application layer.

---

# 18. Implementation Rules

Keep authentication practical and centralized.

Use shared authentication services rather than duplicating login, password, session, or verification logic across user types.

Avoid creating separate authentication systems such as:

```text
AdminAuth
InducteeAuth
ManagerAuth
```

unless there is a genuine technical requirement.

User-type-specific controllers may handle different workflows after authentication, but authentication itself should remain shared.

The basic flow is:

```text
Request
   ↓
Authentication
   ↓
Authenticated User
   ↓
Authorization
   ↓
User-Type/Application Workflow
```

Authentication should remain small, predictable, and independent from application-specific business logic.

---

# 19. Test Accounts

End-to-end testing, by a developer or an AI assistant, uses temporary test accounts, never real ones. Create and delete them with the CLI script:

```text
php database/test-users.php create admin|inductee [--incomplete]
php database/test-users.php list
php database/test-users.php delete <email>|--all
```

`create` makes an active, email-verified account and prints its email and a random password. An inductee gets a completed profile unless `--incomplete` is passed, for testing profile completion (section 12). No emails are sent.

Rules:

- **Use the script, not real accounts.** Don't log in as a real user or change a real account to set up a test (reset its password, untick "Profile completed"). Don't register through `/register.php` just to get an account, because registration emails the administrators.
- **Test accounts are marked.** Their email is `e2e-<type>-<8 hex>@test.invalid`. The `.invalid` domain can never receive email, and `delete` refuses any account without the marker, so the script can't remove a real account.
- **Delete them when the test is done**, in the same session. Deleting also removes the exam attempts and compliance records the test created. `list` shows leftovers, and `delete --all` removes them.
- **Keep passwords out of the project.** Use the printed password for that test only. Don't write it into code, docs or notes.
- **Watch for emails to real people.** Completing an induction as a test inductee sends the Induction Completed notification to the real administrators when instant notifications are on. Ask first unless that is what is being tested.
- **Local and development databases only.** On a live site, create test accounts only with the site owner's explicit approval.
