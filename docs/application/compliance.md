# Compliance

**Application.** A compliance record is the authoritative, issued proof that an inductee completed an induction, valid until its expiry date. Its printable form is the **certificate**, which anyone can verify.

---

## 1. The Record (`compliance_records`)

| Field | Meaning |
| --- | --- |
| `user_id`, `induction_id` | Whose compliance, for which induction |
| `exam_attempt_id` | The passed attempt that earned it; `NULL` for an induction without an exam, or if that attempt was deleted |
| `certificate_number` | Human-readable, unique: `CERT-<year>-<id, 6 digits>`, e.g. `CERT-2026-000104` |
| `verification_token` | 64 random hex characters, unique. The secret in the public verification link. |
| `issue_date`, `expiry_date` | Issue date + the induction's validity months |
| `status` | §2 |
| `renewed_from_id` | The record this one renewed (§4) |
| `expiry_reminder_sent_at` | When the expiry reminder went out, so it is sent once (`docs/core/settings.md` §4) |
| `legacy_id` | Set only on records imported from the previous induction system (§10) |

Keep `certificate_number` and `verification_token` separate: the number identifies the credential and can be read out or printed; the token is unguessable and is the only way to look a record up publicly.

---

## 2. Statuses

Only these four:

| Status | Meaning | Becomes it when |
| --- | --- | --- |
| **active** | Currently valid | Issued |
| **expired** | Validity has ended | The day after `expiry_date` |
| **superseded** | Replaced by a renewal | A new record is issued for the same inductee and induction |
| **revoked** | Invalidated by an administrator | Revoked (§7) |

Expiry is applied lazily: `ComplianceRepository::expireLapsed()` marks lapsed active records as expired at the start of every compliance read (lists, dashboards, certificate lookups), so no scheduled job is needed for it.

---

## 3. Issuing (`ComplianceService::issue()`)

Called only when an induction is completed (`inductions.md` §2):

1. If the inductee's latest record for this induction is active or expired, mark it **superseded**.
2. Create the new record: active, issued today, expiring after the induction's validity months, with a new verification token and `renewed_from_id` pointing at the previous record.
3. Set the certificate number from the new id.

Nothing else creates compliance records. Other features call this service; they never insert rows themselves (`docs/rules/architecture.md` §6).

---

## 4. Renewal

Renewal **always creates a new record**. The previous one is never overwritten; it is superseded and the new one links back to it:

```text
#1001  2026 → 2027  superseded
  ↓ renewed_from_id
#2045  2027 → 2028  superseded
  ↓ renewed_from_id
#3192  2028 → 2029  active
```

That chain is the inductee's permanent compliance history. An inductee renews by completing the induction again after it expires (the dashboard's **Renew**). While a record is active there is nothing to complete.

---

## 5. Current Compliance

The question is "does this inductee **currently** hold valid compliance for this induction?", not "do they have a record?".

The answer comes from the **latest** record for that inductee and induction (`ComplianceService::latestPerInductionForUser()`): they are compliant when it is `active`. Because lapsed records are expired first (§2), active means `expiry_date` is today or later. The inductee dashboard turns this into Compliant / Expired / Not Started / Not Passed (`inductions.md` §4).

---

## 6. Certificates and Verification

- **Inductee certificate page** (`/inductee/certificates/show.php?id=…`): the certificate card, with **Print**. An inductee can only open their own (`findOwnedByUser()`).
- **Public verification page** (`/certificate.php?token=…`): anyone with the link or the QR code sees a verdict first (**Valid certificate**, **Expired**, **Revoked**, or **Replaced** by a renewal), then the card. An unknown token finds nothing.
- **The certificate card** (`views/partials/certificate-card.php`) shows the holder's name, the induction, the certificate number, issue and expiry dates, and a QR code linking to the verification page. It is a printed document, so it is white with dark text and never uses the theme colours (`docs/rules/design-system.html#certificate`).

---

## 7. Revoking

Admin → Compliance → a record → Danger Zone → **Revoke Compliance Record** (asks first).

- Only an **active** record can be revoked.
- The record stays in the history with status revoked, and its certificate shows as revoked when verified.
- The induction shows as Not Started again for the inductee, who can complete it again to get a new record.

---

## 8. Deleting

**Delete Compliance Record**, also in the Danger Zone, removes the record permanently. It is an administrative override for mistakes, and it bypasses the permanent history that revoking keeps; prefer revoking. A record that renewed from it keeps its own data and loses the link.

Deleting a user or an induction with cascade also deletes their compliance records (`docs/core/users.md` §10, `inductions.md` §3). Deleting an exam or an exam attempt keeps compliance records and only clears their attempt link.

---

## 9. Where Compliance Appears

| Page | Shows |
| --- | --- |
| Admin → **Compliance** (`/admin/compliance/`) | Every record: inductee, induction, certificate number, issued, expires, status. Search, induction and status filters. Rows link to View. |
| Admin → Compliance → **View** | The record as read-only fields with View buttons for the inductee, induction, exam attempt and the record it renewed; **View Certificate** (the public page); Danger Zone |
| Admin **dashboard** (`/admin/`) | Active inductees, active inductions, records expiring in the next 30 days and expired records; the charts and Active Inductees table below; a Compliance by Induction table (compliant, expired, rate); the next 10 records to expire |
| Inductee → **Compliance** (`/inductee/compliance/`) | All their records, every status, each linking to its certificate |
| Inductee **dashboard** | Their state per induction (§5) |
| Emails | Completion (with the certificate link), expiry reminder, administrator report (`docs/core/settings.md` §4) |

The dashboard's charts and Active Inductees table (markup: `docs/rules/design-system.html#charts`, `#tables`):

- **Compliance Records Issued**: bars for the quarters of the last 16 that had records issued (quarters with none are left out), counting every record by its issue date, whatever its status now (a renewal is a new record, and a revoked one was still issued). Issue dates never change, so past quarters stay the same.
- **Expiring by Quarter**: bars for this quarter (from today) and the next 3, counting active records by their expiry date.
- **Active Inductees**: a table of active inductees by the employment type or the company on their profile (a switch picks which), with each group's count and share, largest first. Companies are matched ignoring case and surrounding spaces; past 9, the smallest fold into "Other companies". Active inductees with no value are given as a line below the table.

---

## 10. Imported Records

Records imported from the previous induction system (`database/import-legacy-contacts.php`) carry the old session id in `legacy_id` and keep their original, backdated issue dates. They behave like any other record.

---

## 11. Files

| File | Role |
| --- | --- |
| `app/Compliance/ComplianceService.php` | Issue, lists, lookups, revoke, delete |
| `app/Compliance/ComplianceRepository.php` | SQL, including `expireLapsed()` and the queries for reminders and reports |
| `app/Admin/Services/DashboardService.php`, `app/Admin/Repositories/DashboardRepository.php` | Admin dashboard figures |
| `assets/js/dashboard-charts.js` | Admin dashboard charts |
| `admin/compliance/`, `views/admin/compliance/` | Admin list, View, Revoke, Delete |
| `inductee/compliance/`, `inductee/certificates/`, `views/inductee/…` | Inductee records and certificate |
| `certificate.php`, `views/public/certificate.php` | Public verification |
| `views/partials/certificate-card.php` | The certificate card |
