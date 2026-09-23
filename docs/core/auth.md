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
├── created_at
└── updated_at
```

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

Application-specific profile requirements belong to the corresponding profile.

For example:

```text
Inductee Registration
        ↓
Create users record
        ↓
Create inductee_profiles record
        ↓
Require required Inductee fields
```

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
