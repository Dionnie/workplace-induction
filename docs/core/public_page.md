# Public Page

## Purpose

The Public Page is the main page visitors see before they log in.

It explains **what the application is, what it is used for, and how to access it**.

This is an **internal application**, not a marketing website.

The page should be:

- Simple
- Direct
- Informational
- Professional
- Easy to understand
- Quick to navigate

Do not treat the Public Page like a marketing landing page.

---

# 1. Primary Goal

The Public Page should answer these basic questions quickly:

1. What is this application?
2. What is it used for?
3. Who is it for?
4. What can I do here?
5. How do I access the application?

The visitor should not need to scroll through a long marketing page to understand the application.

---

# 2. What It Is Not

Do not design the Public Page as:

- A marketing website
- A sales landing page
- A promotional campaign
- A corporate brochure
- A product showcase
- A long feature page

Avoid marketing language such as:

```text
The ultimate solution
Transform your workforce
Industry-leading platform
Powerful next-generation technology
Unlock your potential
Built for success
```

Use plain language instead.

---

# 3. Recommended Page Structure

Keep the page compact.

Recommended structure:

```text
Header
    ↓
Application Introduction
    ↓
What You Can Do
    ↓
How Access Works
    ↓
Login / Register
    ↓
Footer
```

Not every application needs every section.

Only include sections that provide useful information.

---

# 4. Header

The header should identify the application clearly.

Typical structure:

```text
[Application Logo / Name]

                         Log In
```

If registration is available:

```text
[Application Logo / Name]

                    Log In   Register
```

Keep the header simple.

Do not add a large marketing navigation menu.

The public page generally does not need navigation such as:

```text
About
Services
Features
Pricing
Blog
Testimonials
```

unless the application genuinely requires those pages.

---

# 5. Application Introduction

The first section should immediately explain the application.

Recommended structure:

```text
[Application Name]

Induction and compliance management

Complete required inductions, review your compliance,
and access your certificates in one place.

[Log In] [Register]
```

The exact wording depends on the application's actual purpose.

Do not use exaggerated headlines.

The heading should describe the application rather than sell it.

---

# 6. What You Can Do

A short informational section may explain the main functions.

Example:

```text
What you can do

Complete Inductions
Complete assigned induction requirements online.

Review Compliance
View your current compliance and previous records.

Access Certificates
Download or print your certificate when required.
```

Keep this section short.

Do not create a large grid of feature cards.

Only describe functions that actually exist in the application.

---

# 7. User Access

Explain who should use the application when this is useful.

Example:

```text
Who can use this application?

Inductees
Complete assigned inductions and access compliance records.

Induction Managers
Manage induction content and monitor completion.

Compliance Inductors
Review and manage compliance records.

Administrators
Manage and monitor the application.
```

Only show roles that are actually enabled for the deployment.

Do not expose internal permissions or technical role names unless users need to understand them.

---

# 8. Login

The primary purpose of the public page is to guide existing users into the application.

Use a clear login action:

```text
Already have an account?

[Log In]
```

The login action should be easy to find without making it visually dominant over the rest of the page.

---

# 9. Registration

If public registration is enabled, provide a clear registration path.

Example:

```text
Need an account?

[Register]
```

If registration is invitation-only or disabled, do not show a registration button.

Instead, provide the appropriate instruction.

Example:

```text
Need access?

Please contact your administrator.
```

The Public Page must reflect the actual application's access rules.

---

# 10. Authentication Boundary

The Public Page is outside the authenticated application.

It must not expose protected information.

Visitors who are not authenticated may access:

```text
Public Page
Login
Registration
Forgot Password
Email Verification
```

Protected application areas require authentication.

Example:

```text
Public
│
├── Public Page
├── Login
├── Register
├── Forgot Password
└── Verify Email
        │
        ▼
   Authentication
        │
        ▼
Protected Application
```

Do not rely on the Public Page or URL structure as a security boundary.

All protected requests must perform server-side authentication and authorization checks.

---

# 11. Content Rules

Public page content should be factual.

Prefer:

```text
Complete your assigned induction requirements.
```

instead of:

```text
Experience a smarter way to achieve workplace compliance.
```

Prefer:

```text
View your current compliance and certificates.
```

instead of:

```text
Stay ahead with powerful compliance management.
```

Prefer:

```text
Contact your administrator if you need access.
```

instead of:

```text
Our dedicated support team is here to help you succeed.
```

Use the shortest wording that communicates the information clearly.

---

# 12. No Marketing Metrics

Do not display marketing-style statistics such as:

```text
10,000+
Users
```

```text
99.9%
Success Rate
```

```text
500+
Companies
```

unless the application genuinely requires these figures for an informational reason.

The Public Page is not intended to build sales credibility.

---

# 13. No Testimonials

Do not include:

- Testimonials
- Customer quotes
- Reviews
- Client logos
- Case studies

unless the application specifically requires them.

These belong to a marketing website rather than a typical internal application.

---

# 14. No Promotional Calls to Action

Avoid promotional CTAs such as:

```text
Get Started Today
Discover More
Learn Why We're Different
Transform Your Business
Join the Revolution
```

Use direct actions instead:

```text
Log In
Register
Contact Administrator
```

---

# 15. Visual Design

The Public Page should use the same visual language as the authenticated application.

Use the application's established:

- Colors
- Typography
- Buttons
- Spacing
- Icons
- Logo
- Bootstrap components

Do not create a completely different visual identity for the public page.

The visitor should immediately recognize that the public page belongs to the same application.

---

# 16. Layout

Prefer a compact centered layout.

Example:

```text
┌──────────────────────────────────────────────┐
│ Logo / Application Name              Log In  │
├──────────────────────────────────────────────┤
│                                              │
│        Application Name                      │
│        Short description                     │
│                                              │
│        [Log In] [Register]                   │
│                                              │
├──────────────────────────────────────────────┤
│ What You Can Do                              │
│                                              │
│ Complete Inductions                          │
│ Review Compliance                            │
│ Access Certificates                          │
│                                              │
├──────────────────────────────────────────────┤
│ Need access? Contact your administrator.     │
├──────────────────────────────────────────────┤
│ Footer                                       │
└──────────────────────────────────────────────┘
```

This is a structural guide, not a requirement to reproduce the exact layout.

---

# 17. Responsive Behavior

The Public Page must work on desktop and mobile.

Prioritize:

- Application identification
- Introduction
- Login
- Registration
- Important instructions

Do not create a separate mobile experience.

Use Bootstrap's responsive grid and spacing utilities.

---

# 18. Footer

The footer should remain minimal.

Possible content:

```text
© 2026 Application Name
```

or:

```text
Application Name
Privacy
Contact
```

Only include links that actually exist.

Do not fill the footer with unnecessary navigation.

---

# 19. Public Page vs Application Pages

The Public Page and authenticated application should have a clear boundary.

### Public Page

```text
Introduction
Access information
Login
Registration
Authentication support
```

### Authenticated Application

```text
Dashboard
Inductions
Exams
Exam Attempts
Compliance Records
Certificates
Administration
```

The Public Page explains access to the application.

The authenticated areas perform the actual work.

---

# 20. Content Accuracy

The Public Page must describe functionality that actually exists.

Do not advertise or explain features that have not been implemented.

For example, do not display:

```text
Track your induction history
```

if users cannot currently view induction history.

Do not display:

```text
Download your certificate
```

if certificates are not yet available.

The page should always reflect the current application.

---

# 21. White-Label Considerations

The Public Page must be suitable for different company deployments.

Avoid hard-coded company-specific marketing language in reusable application components.

Application-specific values should come from configuration where appropriate:

```text
Application Name
Logo
Description
Contact Information
```

The underlying Public Page structure should remain reusable.

---

# 22. Core Rule

The Public Page exists to **explain and provide access to the application**, not to sell it.

> Keep it short, factual, and useful.

A visitor should understand what the application does and how to access it within a few seconds.
