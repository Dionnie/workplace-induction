# Design

The rules and reasoning behind the application's UI and UX. Read this, and use the design system, before building or changing any page.

The interface should be simple, compact, professional, consistent, and easy to scan and operate. **Bootstrap 5.3** is the only UI framework. Don't add another framework or component library without an explicit requirement.

## How the UI Documents Fit Together

| Document | Owns |
| --- | --- |
| [`docs/rules/design-system.html`](design-system.html) | **The authority for UI markup.** Tokens, the class vocabulary, component patterns and page patterns, rendered live with copyable code. |
| `docs/rules/design.md` (this file) | The rules and reasoning behind the design system. |
| `docs/rules/terminology.md` | The words used in labels, headings and messages. |
| `assets/css/app.css` | The implementation. Every class it defines is listed in the design system's Class Reference. |
| `views/partials/` | The shared layouts: `head.php`, `app-navbar.php`, `account-menu.php`, `flash.php`, `scripts.php`, and the admin, inductee and guest headers and footers. |
| `views/partials/admin-menu.php` | The one list of admin sections, grouped Application / Core. It feeds the admin sidebar and the dashboard shortcuts. |
| `status_badge()` in `app/Core/helpers.php` | The one map from status to badge colour. |
| Settings → Appearance (`admin/settings/update-appearance.php`, `App\Core\Theme`) | The brand colour tokens, chosen by administrators at runtime (`docs/core/settings.md` §2). |

Before building or changing a page, open the design system on the running site (`/docs/rules/design-system.html`) and use its patterns. If a page and the design system disagree, the page is wrong.

If this file and the design system disagree, the design system wins for markup and class names. Fix whichever is out of date in the same change.

The Content Blocks reading canvas and the two Studio editors have their own layout rules (`docs/application/content-blocks.md` §4–6, `docs/application/exams.md` §4). They still use this system's tokens, buttons and states.

The design may evolve when there is a real technical or product reason. When it does, change `app.css`, the design system and this file together, and check the result with every Appearance theme.

---

# 1. General Design Principles

Prioritize usability over decoration.

The interface should help users:

1. Understand where they are. (Page title, active navigation item, breadcrumb.)
2. Understand what they can do. (One clear main action.)
3. Complete the task quickly.
4. Understand the result of their action. (Flash message after every change.)
5. Recover easily from mistakes. (Confirmation before destructive actions; values kept after a validation error.)

Prefer familiar UI patterns over creative ones.

Do not add visual elements simply because there is available space.

---

# 2. Bootstrap First

Use Bootstrap 5 components and utilities whenever they provide a suitable solution: layout, grid, spacing, forms, buttons, tables, alerts, badges, navigation, breadcrumbs, dropdowns, pagination and modals.

Do not recreate Bootstrap components with custom CSS.

Custom CSS is appropriate only when:

- A pattern repeats across pages and Bootstrap cannot express it (e.g. `.page-header`, `.card-table`).
- The application needs a specific visual treatment (e.g. the theme, the Content Blocks canvas).
- Bootstrap does not provide the required behavior.

When you add a class:

1. Put it in the right section of `assets/css/app.css`.
2. Add it to the design system's Class Reference and document the pattern there.

Do not write page-level `<style>` blocks or `style=""` attributes. The exception is a value only known at runtime, such as a theme swatch colour read from the database.

Do not override Bootstrap globally when a local, component-level rule is sufficient. The exceptions are deliberate and documented in `app.css`: the Bootstrap bridge (§3 there) and heading weight (§4 there).

---

# 3. Color System

The application uses a restrained palette: one **primary** color family, one **accent**, **warm neutrals**, and Bootstrap's **semantic** colors.

Colors are semantic tokens (CSS custom properties) defined in `assets/css/app.css`. The token list and live swatches are in the design system (Colour).

## Brand Colors Are Themeable

This is a white-label application. Administrators choose the brand colors in **Settings → Appearance**: one of the presets in `App\Core\Theme::PRESETS`, or custom Primary and Accent colors. `admin/settings/update-appearance.php` saves the choice.

The default theme is **Teal** (deep teal with a warm terracotta accent). `app.css` defines those defaults, and `theme_style_tag()`, output by `views/partials/head.php` after `app.css`, overrides them on every page.

For a custom theme, primary 800 and 900 are derived automatically by darkening the chosen 700.

The **Bootstrap bridge** in `app.css` points Bootstrap's own variables (`--bs-primary`, link colors, focus rings, and the button, list-group, dropdown and pagination variables) at the tokens. As a result, `btn-primary`, `btn-outline-primary`, `text-primary`, `text-bg-primary`, `border-primary`, links and breadcrumbs all follow the chosen theme.

Rules that follow from this:

- **Never hard-code a brand hex value.** In CSS, use `var(--color-primary-700)` etc. For tints, use `rgba(var(--color-primary-rgb), 0.12)`, never `rgba(15, 93, 95, 0.12)`. In markup, use Bootstrap's primary classes.
- Before using a Bootstrap component that is not bridged yet (switches, ranges, progress bars, nav pills, accordions), bridge it in `app.css`.
- Every page gets its `<head>` from `views/partials/head.php`, which outputs the theme.
- The theme is for the app's own screens only. What leaves the app is neutral, because an administrator's colour choice (a very dark primary, say) can't be checked against every logo and text colour there:
  - **Emails** (`views/emails/layout.php`, one layout for every email): a white card with a subtle grey border and soft shadow on a light grey page. Inside it, the logo (when one is set) and the company name on white over a thin grey rule, never a coloured header band, then the message in dark text; a small grey footer below the card. Links are dark and underlined; long links wrap. The column is fluid up to 600px, so it reads on a phone. Most emails are plain text that the layout turns into HTML; one that needs tables (the admin report, `views/emails/admin-report.php`) also sets its own `$bodyHtml` in old-school email-safe markup: tables only, inline styles, the font on every cell, a solid dark button, and dates written out ("Thursday 24 September 2026", "25 Sep 2026" with the time under it).
  - **Printed documents**: the certificate card (`docs/rules/design-system.html#certificate`).
- Primary and accent must stay dark enough for white text (contrast of at least 4.5:1). The presets meet this, and custom colors are validated on save.
- Semantic colors (success, warning, danger, info) and neutrals are **not** themeable.

### Primary

| Token | Use |
| --- | --- |
| Primary 900 | Navbar, dark surfaces |
| Primary 800 | Hover |
| Primary 700 | Primary actions, links, active and selected states |

Use the primary color for what the user should act on: main actions, links, active navigation, selected items, and application branding. It remains the dominant application color.

### Accent

Accent 500 is secondary brand emphasis (warm terracotta by default).

Use it sparingly for small secondary emphasis, such as the Lecture badge in the Studio editor, or the slanted block in the brand band along a slide's top and bottom edge (`.cb-slide-band`, primary with an accent block: branding, the one decorative use of the brand colors). Do not use it everywhere, and never as a status color.

### Warm Supporting Color

Warm 500 (`#EA9491`) is a decorative highlight (e.g. the teleport-scroll pulse). It is **not** a replacement for the semantic danger color.

---

# 4. Neutral Colors

Use the warm neutrals for backgrounds, surfaces, borders and supporting text:

| Token | Use |
| --- | --- |
| `--color-background` | Page background (warm off-white) |
| `--color-surface` | Cards, tables, inputs, modals (white) |
| `--color-surface-subtle` | Panels inside a surface, table header rows |
| `--color-border` | Borders |
| `--color-text` | Body text |
| `--color-text-muted` | Secondary text (`text-muted`, `form-text`). Meets 4.5:1 on both backgrounds. |

Content sits on white surfaces over the warm page background.

Do not create additional near-identical gray or beige values for individual components.

---

# 5. Semantic Colors

Brand colors and semantic colors are separate concepts. A brand color never means success, warning or error.

| Meaning | Bootstrap variant | Examples |
| --- | --- | --- |
| Positive, completed, valid | `success` | Active, Compliant, Passed, Saved |
| Attention required (not failure) | `warning` | Expiring soon, Pending, Unsaved changes |
| Negative, failed, destructive | `danger` | Expired, Failed, Revoked, Suspended, Delete |
| Neutral information | `info` | Instructions, "No matches found" |
| Inactive, neutral state | `secondary` | Inactive, Superseded, Not Started |

Do not use the accent to represent danger simply because it is visually strong.

Do not use the warm pink tone to represent an error.

For colored **text** on white, use `text-success`, `text-danger` or `text-warning-emphasis`. Plain `text-warning` fails contrast.

Semantic meaning takes priority over brand styling.

---

# 6. Color Usage Rules

Use color to communicate meaning, hierarchy or interaction. Do not use color purely for decoration.

```text
Primary  → what should be acted on
Semantic → what state something is in
Neutral  → supporting information
```

Avoid:

```text
Random color per card
Random color per status
Different colors for the same action
Color used only to make a component "pop"
```

The same meaning must use the same color treatment throughout the application.

Chart bars are primary, so they follow the theme. Parts of a whole are a table with counts and shares, not a pie. The exception is the dashboard's Expiring by Quarter bars, which are danger red, the one place records that haven't expired yet use danger rather than warning. A chart's figures are always also given as text (`design-system.html#charts`).

---

# 7. Component States

Interactive components should have predictable states: default, hover, focus, active, disabled, loading, error and success. Only implement the states that are relevant to the component.

### Default

The normal state, using the standard palette without unnecessary emphasis.

### Hover

Hover gives subtle feedback that an element is interactive. It must preserve readability, keep the element's meaning, and not shift the layout. Primary controls darken to primary-800. Link cards gain a primary-tinted border and a slightly deeper shadow. Do not introduce unrelated colors for hover.

### Focus

Focus states must remain visible. The bridge gives form controls and buttons a primary-colored focus ring. Do not remove focus indicators without an accessible replacement.

### Active

Active means currently selected: the current navbar item, the current Settings section, a selected view mode. Use the primary color system (Bootstrap's `.active` plus `aria-current="page"` for navigation). Do not use success, warning or danger to mean "selected".

### Disabled

Disabled means the action is currently unavailable. Use Bootstrap's `disabled` attribute; controls should look unavailable without becoming unreadable. Authorization must still be enforced server-side even when a control is hidden or disabled.

### Read-only

A record with no edit form (Compliance Record, Exam Attempt) shows its values as `readonly` form fields rather than label/value text, with a View button beside any related record. They share the disabled fill but stay focusable, so values such as a certificate number can be copied. See "Read-only fields" in `design-system.html`.

### Loading

When an action is processing, give clear feedback and prevent duplicate submissions.

This is automatic for every POST form: `assets/js/app.js` disables the submit button that was used and adds a spinner. Opt a button out with `data-no-loading`. For in-page (fetch) requests, show the same spinner in the button, or a status badge such as the Studio's Saved / Unsaved changes.

Do not use a full-page loading overlay for small operations.

### Error and Success

Use Bootstrap's success and danger styling for the result of an action (flash messages) and for field validation. See sections 11–13.

---

# 8. Status Badges

Use compact badges for short status values, rendered with `status_badge()`:

```php
<?= status_badge($record['status']) ?>
<?= status_badge($state, 'Not Passed') ?>   // custom label, same color
```

`status_badge()` is the only place that maps a status to a color. Views never write `text-bg-*` for a status and never define their own status → color arrays. When you add a status, add it to `status_badge()` and to the design system's status table.

Non-status labels (a media category, a count) use a neutral tag, `badge text-bg-light border`, so they cannot be mistaken for a status.

Status terminology must remain consistent. Do not use different colors for the same status in different areas of the application.

---

# 9. Color Must Not Be the Only Indicator

Important states must not rely on color alone. The text must communicate the state even when color is unavailable ("Expired", not a red dot).

This applies particularly to status badges, validation messages, alerts, tables, charts and form errors.

---

# 10. Layout and Navigation

- Every page uses a layout partial (admin, inductee or guest). Never hand-write a `<head>`, navbar or footer.
- Every page starts with a `.page-header`: the page title, an optional one-line subtitle, and the page's actions. The one exception is the inductee's slide view of an induction, where the slide gets the whole screen: the induction's title sits at the top of the outline drawer, with an About modal for the description (`docs/application/content-blocks.md` §5).
- Pages below a section's index (create, edit, detail, exam) show a breadcrumb in the page header. It replaces ad-hoc "Back" buttons.
- Form, profile and detail pages are capped with `.page-narrow`.
- Both areas have a top bar with the brand and the account menu (My Profile, Log Out). The active item comes from the view's `$currentPage`.
- **Admin** sections live in a sidebar, grouped into **Application** (the induction features: Inductions, Exams, Exam Attempts, Compliance) and **Core** (platform functions any deployment has: Users, Media Library, Tools, Settings), the same split as `docs/application/` and `docs/core/`. A vertical list scales to any number of sections; below `lg` it becomes a drawer.
- `views/partials/admin-menu.php` is the only list of admin sections. It feeds both the sidebar and the dashboard shortcuts, so a new admin page is one entry there. Never hand-list sections elsewhere.
- **Inductee** sections (only two) stay as links in the top navbar, which is quicker to use on a phone.
- Every page must work from phone width up without horizontal page scrolling. Use Bootstrap's grid: side-by-side fields use `col-sm` so they stack on phones. In admin pages, split into columns at `xl`, because the sidebar takes 240px from `lg` up.

---

# 11. Forms

Forms should be straightforward and predictable.

Rules:

- Labels match the established database/business terminology.
- Use predictable field names and appropriate input types, with `autocomplete` where it helps.
- Group related fields: side by side in a `row g-3`, or in a `fieldset` with a `legend`.
- Mark every **required** field with a red asterisk (`.required` on its label, or on the `legend` of a required group) and give the control the `required` attribute, so screen readers announce it; the asterisk itself is hidden from them. A field without an asterisk is optional; do not also write "(optional)".
- "Required" follows the server-side validation: the form is rejected without it. A field whose blank value has a meaning ("Blank uses the Company Name", "Leave blank to keep the current password") is optional. Fields labelled only by `aria-label` (filters, one-field inline forms) carry no asterisk.
- The red asterisk is the one use of the danger color that is not a negative state. It is kept because it is a universally understood convention.
- Short help goes in a `form-text` line under the field. When a form has so many fields that those lines become a wall of text (every Settings tab), put the help in a tooltip on an info button after the label instead (`.field-help`, `docs/rules/design-system.html#forms`), with the control's `aria-describedby` pointing at the button. Help that holds a link or code a user needs to click or copy stays as visible text, since a tooltip can't be clicked into.
- A placeholder is never a label or an example. On a field whose blank value has a meaning, it shows what a blank field will use (the Company Name, the Primary Email); when there is no such value, leave the placeholder out.
- Validation happens server-side. Show errors with `is-invalid` and `invalid-feedback` next to the field (`invalid-feedback d-block` when the message cannot sit directly after the control). Show form-level errors as an `alert alert-danger` above the fields.
- Preserve submitted values after validation errors (`old()`).
- Put buttons in `.form-actions`: the main action first, then Cancel.
- Avoid unnecessary fields.

Do not make forms unnecessarily long. If a form becomes large, divide it into cards or fieldsets rather than introducing a multi-step wizard.

---

# 12. Feedback Messages

- **After an action**, the controller sets a flash message (`flash('success', …)` or `flash('error', …)`) and redirects. The layout shows it as a dismissible alert.
- **A state on the page** (e.g. "Your compliance has expired") is a page alert: not dismissible, in the color that matches its meaning, stating the next step when there is one.
- **Supporting notes** inside a card are `text-muted small` text, not alerts.

---

# 13. Destructive Actions

Destructive actions must never look like everyday actions.

- **Lists only navigate.** Table rows and list items offer Edit (editable records) or View (read-only records), never Delete or Revoke.
- **Destructive actions live inside the record**, in a **Danger Zone** card at the bottom of its page. Each action states what it does, including what happens to related records. Records without an edit page get a read-only detail page to hold their Danger Zone (Compliance Record, Exam Attempt).
- Collections without per-item pages delete through a deliberate step: select files, then "Delete Selected" (Media Library), or open a category's edit state to find "Delete category".
- An action that isn't allowed stays visible but disabled, with the reason given (e.g. you cannot delete your own account). The server enforces the same rule.
- After a successful delete, return to the list. If the action is refused, or after a revoke, return to the record so the message appears in context.

Every destructive action also asks for confirmation. Use the lightest confirmation that fits:

- A native `confirm()` to delete or revoke a single row with no options.
- A modal when the user needs an explanation or a choice (e.g. cascade delete).
- A two-step page when the effect needs reviewing first (Search & Replace's dry run).

Wording: "{Verb} {object}? {Consequence}. This cannot be undone." The confirm button repeats the verb and object (e.g. "Delete Induction").

---

# 14. Labels and Terminology

Use the terms in `docs/rules/terminology.md` in every label, heading, button and message, and add a term there before introducing a new one. A concept keeps its name on every screen. Labels say what the field or action is (Edit, View, Delete, Save), never something vague (Manage, Process, Go).

---

# 15. Buttons

| Role | Treatment |
| --- | --- |
| Main action | `btn-primary`. One per form or card; never two primary buttons side by side. |
| Secondary action, Cancel, row navigation | `btn-outline-secondary` |
| Add an item inside a builder | `btn-outline-primary btn-sm` |
| Destructive trigger | `btn-outline-danger` (opens a confirmation) |
| Destructive confirmation | `btn-danger` |
| Tertiary, inline with text | `btn-link` |

Use `btn-sm` in page headers, filter bars, table rows and side panels, and the default size for form and modal buttons.

A page should have one obvious main action. On the inductee dashboard, rows that still need doing get a primary button (Start, Renew, Retry), and completed rows get an outline "View".

Do not use solid `btn-secondary`.

---

# 16. Links vs Buttons

**Links** navigate: View Details, Edit User, Go to Dashboard.

**Buttons** act: Save, Delete, Send, Verify, Generate.

A call to action that navigates (Add Induction, Start, Cancel) may be styled as a button. Otherwise, do not use buttons simply to navigate. A card whose purpose is navigation is a `.link-card`, not a card with a button in it.

---

# 17. Tables

Administrative data should use practical tables.

```text
┌────────────┬────────────┬──────────┬─────────┐
│ Name       │ Status     │ Created  │         │
├────────────┼────────────┼──────────┼─────────┤
│ Example    │ Active     │ Date     │ Edit    │
└────────────┴────────────┴──────────┴─────────┘
```

Tables should:

- Sit in a `.card-table` card, inside `.table-responsive` so wide tables scroll within the card instead of squeezing columns.
- Have clear, concise column headings. The actions column has a visually hidden "Actions" heading.
- Use consistent alignment: numbers right-aligned, row actions right-aligned on one line.
- Keep row actions to navigation: Edit or View. Destructive actions belong in the record's Danger Zone (section 13).
- Avoid unnecessary columns.
- Show dates as `YYYY-MM-DD` (and `YYYY-MM-DD HH:MM` with a time).
- Provide a useful empty state: one full-width row saying what is missing.
- Have a filter bar above them when the list can grow. Every filter control has an `aria-label`, and "Clear" only appears while a filter is applied.
- Support pagination when appropriate.

---

# 18. Accessibility Basics

- One `h1` per page (`.page-title`). Heading levels follow the page structure; size comes from classes.
- Every form control has a label (visible, or `aria-label` in filter bars).
- Icon-only buttons have an `aria-label` and a `title`. Decorative icons have `aria-hidden="true"`.
- Modals are labelled by their title (`aria-labelledby`).
- Text meets 4.5:1 contrast. The tokens and bridge are chosen for this; do not lighten text colors.
