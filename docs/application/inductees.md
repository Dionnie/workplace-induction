# Inductees

**Application.** Dedicated view of all workplace inductees, displaying their complete profile information from `inductee_profiles` alongside account status and compliance records.

---

## 1. Overview

While the **Users** section (`docs/core/users.md`) is a Core platform tool managing account credentials and authentication states for both administrators and inductees, the **Inductees** section is an Application-level tool dedicated exclusively to inductee profiles, workplace details, and compliance history.

```text
admin/inductees/index.php   List all inductees with full profile columns and filters
admin/inductees/show.php    Inductee detail: profile fields, account status, link to compliance records
```

---

## 2. Fields Displayed

The feature displays every column of `inductee_profiles` (`docs/core/users.md` §1) together with associated account state:

| Field | Source Table | Description |
| --- | --- | --- |
| `first_name`, `last_name` | `inductee_profiles` | Legal name displayed on certificates |
| `contact_number` | `inductee_profiles` | Phone number for on-site contact |
| `job_position` | `inductee_profiles` | Optional job role or trade title |
| `company` | `inductee_profiles` | Employer or contracting business |
| `employment_type` | `inductee_profiles` | One of the 9 defined employment types |
| `created_at` | `inductee_profiles` | Timestamp when profile was first created |
| `updated_at` | `inductee_profiles` | Timestamp when profile was last updated |
| `email` | `users` | Primary login email address |
| `status` | `users` | Account status (`active`, `inactive`, `suspended`) |
| `profile_completed` | `users` | Whether profile requirements have been met |
| `email_verified_at` | `users` | Verification timestamp |

---

## 3. Inductee Listing (`/admin/inductees/index.php`)

Displays all users where `user_type = 'inductee'` in a searchable, filterable table.

### Filtering, Search and Sorting

- **Search:** Matches against name (`first_name`, `last_name`, or full name), email, company, job position, and contact number.
- **Employment Type:** Dropdown filter matching the standard types (`Full-time`, `Part-time`, `Casual`, `Contractor`, `Sub-contractor`, `Apprentice`, `Trainee`, `Shift-worker`, `Other`).
- **Account Status:** Filter by `active`, `inactive`, or `suspended`.
- **Profile Status:** Filter by `complete` or `incomplete`.
- **Column Sorting:** All columns (Name, Email, Contact Number, Job Position, Company, Employment Type, Status, Profile, Created) can be sorted ascending or descending by clicking column headers (`.table-sort-link`). Active sorting preserves filter parameters.
- **Inner Scrollable Table:** Uses `.table-scrollable` with pinned sticky headers (`position: sticky`), containing large record sets and horizontal expansion within the card without scrolling the outer page. Columns use automatic sizing without an artificial max-width cap.

### Actions

- **View:** Opens the inductee detail page (`/admin/inductees/show.php?id={id}`).
- **Edit:** Links directly to the user account editor (`/admin/users/edit.php?id={id}`).
- **Add Inductee:** Links to the account creation form (`/admin/users/create.php`).

---

## 4. Inductee Detail Page (`/admin/inductees/show.php`)

Provides a comprehensive, read-only view of a single inductee:

1. **Profile Details Card:** All `inductee_profiles` fields, account verification, and creation/update timestamps.
2. **View Compliance Records Action:** Opens the main compliance records page (`/admin/compliance/index.php?search={email}&induction_id=&status=`) in a new tab (`target="_blank"`), pre-filtered by the inductee's email address to view all certificates, statuses, and history (`docs/application/compliance.md`). Available in the header actions and at the foot of the page.
3. **Admin Actions:** Direct buttons to edit the account in Core Users, or switch into the inductee's account if active.

---

## 5. Code Structure

- **Repository:** `App\Inductee\InducteeProfileRepository`
  - `allWithUsers(?string $search, ?string $employmentType, ?string $status, ?string $profileStatus): array`
  - `findWithUser(int $userId): ?array`
- **Service:** `App\Inductee\InducteeProfileService`
  - `listAll(?string $search, ?string $employmentType, ?string $status, ?string $profileStatus): array`
  - `getWithUser(int $userId): ?array`
- **Controllers:**
  - `admin/inductees/index.php`
  - `admin/inductees/show.php`
- **Views:**
  - `views/admin/inductees/index.php`
  - `views/admin/inductees/show.php`
- **Admin Navigation:** Registered under `'Application'` in `views/partials/admin-menu.php`.
