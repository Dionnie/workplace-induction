# UI Guidelines

## Purpose

These guidelines define the application's general UI and UX rules.

The interface should be:

- Simple
- Compact
- Professional
- Practical
- Consistent
- Easy to scan
- Easy to operate

The application uses **Bootstrap 5** as the primary UI framework.

Do not introduce another UI framework or component library without an explicit requirement.

---

# 1. General Design Principles

Prioritize usability over decoration.

The interface should help users:

1. Understand where they are.
2. Understand what they can do.
3. Complete the task quickly.
4. Understand the result of their action.
5. Recover easily from mistakes.

Prefer familiar UI patterns over creative ones.

Do not add visual elements simply because there is available space.

---

# 2. Bootstrap 5

Use Bootstrap 5 components and utilities whenever they provide a suitable solution.

Prefer:

```text
Bootstrap layout
Bootstrap grid
Bootstrap spacing utilities
Bootstrap forms
Bootstrap buttons
Bootstrap tables
Bootstrap alerts
Bootstrap badges
Bootstrap navigation
Bootstrap dropdowns
Bootstrap pagination
Bootstrap modals
```

Do not recreate Bootstrap components unnecessarily with custom CSS.

Custom CSS is appropriate when:

- The application requires a specific visual treatment.
- Bootstrap does not provide the required behavior.
- A reusable application-specific component is needed.

Keep custom CSS small and purposeful.

Do not override Bootstrap globally when a local component-level solution is sufficient.

---

# 3. Color System

The application uses a restrained palette built around **deep teal**, **warm terracotta**, and **warm neutral tones**.

Colors are defined as semantic tokens rather than being selected independently for each component.

## Brand Colors

```css
:root {
  --color-primary-900: #022a2b;
  --color-primary-800: #044647;
  --color-primary-700: #0f5d5f;

  --color-accent-500: #a54b17;

  --color-warm-500: #ea9491;
}
```

### Primary

The primary color family is deep teal.

```text
Primary 900  #022A2B
Primary 800  #044647
Primary 700  #0F5D5F
```

Use primary teal for:

- Primary actions
- Main navigation emphasis
- Links where appropriate
- Active navigation states
- Important interactive elements
- Application branding

The darkest primary values should generally be reserved for strong emphasis, headers, or dark surfaces.

### Accent

```text
Accent 500  #A54B17
```

The accent is a warm terracotta/brown tone.

Use it selectively for:

- Secondary brand emphasis
- Important visual accents
- Selected highlights
- Supporting calls to action where appropriate

Do not use the accent color everywhere.

The primary teal remains the dominant application color.

### Warm Supporting Color

```text
Warm 500  #EA9491
```

This is a supporting warm tone.

Use sparingly for:

- Visual accents
- Supporting illustrations
- Small highlights
- Non-critical decorative elements

It is **not** a replacement for the semantic danger color.

---

# 4. Neutral Colors

Use warm or restrained neutrals for backgrounds, surfaces, borders, and supporting text.

```css
:root {
  --color-background: #ffffff;
  --color-surface: #ffffff;
  --color-surface-subtle: #f8f6f3;

  --color-border: #dee2e6;

  --color-text: #212529;
  --color-text-muted: #6c757d;
}
```

These values may align with Bootstrap's existing neutral palette where practical.

Do not create additional near-identical gray or beige values for individual components.

---

# 5. Semantic Colors

Brand colors and semantic colors are separate concepts.

A brand color should not automatically mean success, warning, or error.

Use semantic colors for system states:

```text
Success
Warning
Danger
Info
Neutral
```

Bootstrap 5's contextual colors should be used where appropriate.

Example:

```text
Success → completed / valid / approved
Warning → attention required
Danger  → error / failed / destructive
Info    → neutral information
Neutral → inactive / secondary / normal
```

Do not use the terracotta accent to represent danger simply because it is visually strong.

Do not use the warm pink tone to represent an error.

Semantic meaning takes priority over brand styling.

---

# 6. Bootstrap Color Mapping

Use Bootstrap's semantic system for component states.

| Meaning             | Preferred Treatment             |
| ------------------- | ------------------------------- |
| Primary action      | Primary teal / `btn-primary`    |
| Secondary action    | `btn-secondary`                 |
| Success             | Bootstrap `success`             |
| Warning             | Bootstrap `warning`             |
| Error / destructive | Bootstrap `danger`              |
| Information         | Bootstrap `info`                |
| Neutral             | Bootstrap `secondary` / neutral |

Where Bootstrap's default primary does not match the application's brand, customize Bootstrap's primary token to the application's primary teal rather than creating separate button systems.

---

# 7. Color Usage Rules

Use color to communicate meaning, hierarchy, or interaction.

Do not use color purely for decoration.

Prefer:

```text
Primary → what should be acted on
Semantic → what state something is in
Neutral → supporting information
```

Avoid:

```text
Random color per card
Random color per status
Different colors for the same action
Color used only to make a component "pop"
```

The same meaning must use the same color treatment throughout the application.

---

# 8. Component States

Interactive components should have predictable states.

At minimum, consider:

```text
Default
Hover
Focus
Active
Disabled
Loading
Error
Success
```

Not every component requires every state.

Only implement states that are relevant to the component.

---

# 9. Default State

The default state is the normal interactive or informational state.

Components should use the standard application palette without unnecessary emphasis.

---

# 10. Hover State

Hover provides feedback that an element is interactive.

Hover should:

- Be subtle.
- Preserve readability.
- Not change the meaning of the element.
- Not cause layout shifts.

For primary controls, a darker primary teal may be used on hover.

Do not introduce unrelated colors for hover states.

---

# 11. Focus State

Focus indicates keyboard or accessibility navigation.

Focus states must remain visible.

Do not remove browser or Bootstrap focus indicators without providing an accessible replacement.

---

# 12. Active State

Active indicates that an element is currently selected or being interacted with.

Examples:

```text
Active navigation item
Selected tab
Pressed button
Selected filter
```

Use the primary color system for application navigation and interactive selection.

Do not use success, warning, or danger simply to indicate that something is currently selected.

---

# 13. Disabled State

Disabled indicates that an action is currently unavailable.

Use Bootstrap's disabled styling where appropriate.

Disabled controls should appear unavailable without becoming unreadable.

Authorization must still be enforced server-side even if a control is hidden or disabled in the UI.

---

# 14. Loading State

When an action is processing, provide clear feedback.

Example:

```text
[ Saving... ]
```

or:

```text
[ spinner ] Saving
```

Loading states should:

- Prevent accidental duplicate submissions where appropriate.
- Communicate that the request is still processing.
- Return to the correct state after completion.

Do not use a full-page loading overlay for small operations.

---

# 15. Success State

Use Bootstrap success styling for positive system states.

Examples:

```text
Saved
Active
Approved
Passed
Verified
Completed
Valid
```

Success means the underlying operation or state is positive.

Do not use the brand accent merely because something is important.

---

# 16. Warning State

Use Bootstrap warning styling when attention may be required.

Examples:

```text
Pending
Expiring Soon
Incomplete
Attention Required
Review Required
```

Warning does not mean failure.

---

# 17. Danger State

Use Bootstrap danger styling for:

```text
Delete
Failed
Invalid
Expired
Suspended
Error
Rejected
```

Danger should be reserved for genuine negative or destructive states.

Do not use danger styling merely to attract attention.

---

# 18. Info State

Use Bootstrap info styling for neutral informational messages.

Examples:

```text
Information
Instructions
Additional Details
System Information
```

Info should not imply success, warning, or failure.

---

# 19. Status Badges

Use compact badges for short status values.

Example:

```html
<span class="badge text-bg-success">Active</span>
```

```html
<span class="badge text-bg-warning">Pending</span>
```

```html
<span class="badge text-bg-danger">Failed</span>
```

```html
<span class="badge text-bg-secondary">Inactive</span>
```

Status terminology must remain consistent.

Do not use different colors for the same status in different areas of the application.

---

# 20. Color Must Not Be the Only Indicator

Important states must not rely on color alone.

For example:

```text
Active
Pending
Failed
```

The text must communicate the state even when color is unavailable.

This applies particularly to:

- Status badges
- Validation messages
- Alerts
- Tables
- Charts
- Form errors

---

# 21. Forms

Forms should be straightforward and predictable.

Rules:

- Labels must match established database/business terminology.
- Use predictable field names.
- Group related fields.
- Keep related fields together.
- Clearly identify required and optional fields.
- Use appropriate HTML input types.
- Provide useful validation messages.
- Preserve submitted values after validation errors where appropriate.
- Place the primary action clearly.
- Avoid unnecessary fields.

Example:

```text
Name
[________________________]

Email
[________________________]

Status
[ Active ▼ ]

[Save]
[Cancel]
```

Do not make forms unnecessarily long.

If a form becomes large, divide it into logical sections rather than introducing a multi-step wizard automatically.

---

# 22. Labels and Terminology

UI terminology must remain consistent throughout the application.

Database/business concepts should not randomly change names in the interface.

For example, if the established concept is:

```text
Induction
```

do not arbitrarily change it to:

```text
Program
Training
Course
```

in another screen.

Labels should clearly describe the underlying field or action.

Avoid vague labels such as:

```text
Manage
Process
Handle
Details
Go
```

when a more specific label is available.

Prefer:

```text
Edit
View
Create
Delete
Save
Download
Verify
Renew
```

Use the application's established terminology before introducing a new term.

---

# 23. Buttons

Use buttons consistently.

### Primary action

Use the primary teal treatment for the main action.

```html
<button class="btn btn-primary">Save</button>
```

### Secondary action

Use secondary buttons for supporting actions.

### Destructive action

Use Bootstrap danger styling for destructive actions.

Avoid presenting multiple competing primary buttons.

A page should generally have one obvious primary action.

---

# 24. Links vs Buttons

Use:

**Links** for navigation.

```text
View Details
Edit User
Go to Dashboard
```

Use:

**Buttons** for actions.

```text
Save
Delete
Send
Verify
Generate
```

Do not use buttons simply to navigate when a normal link is appropriate.

---

# 25. Tables

Administrative data should generally use practical tables.

Prefer:

```text
┌────────────┬────────────┬──────────┬─────────┐
│ Name       │ Status     │ Created  │ Actions │
├────────────┼────────────┼──────────┼─────────┤
│ Example    │ Active     │ Date     │ View    │
└────────────┴────────────┴──────────┴─────────┘
```

Tables should:

- Have clear column headings.
- Keep columns concise.
- Use consistent alignment.
- Keep actions predictable.
- Avoid unnecessary columns.
- Support pagination when appropriate.
- Provide useful empty states.
- Remain usable with realistic amounts of data.

Use Bootstrap table utilities.

For wide tables, allow horizontal scrolling rather than forcing columns into unreadable width
