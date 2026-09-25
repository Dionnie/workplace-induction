# Public Pages

Rules for the pages a visitor sees without logging in. This is an internal application, not a marketing website: public pages explain what it is and let people in.

---

## 1. The Public Pages

| Page | File | Purpose |
| --- | --- | --- |
| Landing page | `index.php` | What the application is, what you can do, Log In / Register |
| Log In | `login.php` | `docs/core/users.md` §5 |
| Register | `register.php` | Only when `registration_enabled` is true in `config/app.php` |
| Forgot / Set Your Password | `forgot-password.php`, `reset-password.php` | `docs/core/users.md` §7 |
| Verify Email | `verify-email.php` | `docs/core/users.md` §4 |
| Certificate Verification | `certificate.php?token=…` | Anyone holding a certificate's link or QR code checks it is valid (`docs/application/compliance.md` §6) |

Everything else requires login. These pages never show protected information; the certificate page shows only what is printed on the certificate, and only for a valid token.

Auth pages use the guest layout (`views/partials/guest-header.php`). The landing page has its own compact header and footer.

---

## 2. The Landing Page

It answers, within a few seconds: what is this, what can I do here, and how do I get in.

```text
Header           Logo / company name                      Log In  Register
Introduction     Company name, tagline, description, [Log In] [Register]
What you can do  Complete Inductions · Review Compliance · Access Certificates
Footer           © year Company name
```

- Company name and logo come from Settings → General (`docs/core/settings.md` §1). Tagline and description come from `config/app.php`.
- If registration is off, there is no Register button; say how to get access instead ("Contact your administrator").
- Only describe features that exist. Don't advertise something before it is built.
- Only show links that go somewhere real. No About, Pricing, Blog or empty footer navigation.

---

## 3. Content Rules

Plain, factual, short:

| Write | Not |
| --- | --- |
| Complete your assigned induction requirements. | Experience a smarter way to achieve workplace compliance. |
| View your current compliance and certificates. | Stay ahead with powerful compliance management. |
| Contact your administrator if you need access. | Our dedicated support team is here to help you succeed. |
| Log In, Register | Get Started Today, Discover More, Join the Revolution |

No marketing metrics ("10,000+ users"), testimonials, client logos, feature-card grids or promotional calls to action.

---

## 4. Look and White-Labelling

- Same visual language as the rest of the app: `views/partials/head.php` (so the Appearance theme applies), Bootstrap, the design system (`docs/rules/design.md`). No separate public identity.
- Compact, centred, one column (`.page-narrow`). Works from phone width up.
- Nothing company-specific is hard-coded. Names, logo and wording come from settings or `config/app.php`, so another deployment only changes those.
