# Testing

How to check a change works. There is no automated test suite: changes are verified by running the affected workflow in the browser, end to end.

---

## 1. What to Test

- Run the workflow you changed, as the user type that uses it, on the local site (`http://workplace-induction.test`).
- Check the paths around it: a validation error keeps the entered values, a refused action shows its message, a 404 for a missing record.
- For UI changes, check phone width as well as desktop (`docs/rules/design.md` §10), and a second Appearance theme if colours are involved.
- For a migration, run it on a copy first and compare before and after (`docs/rules/data-protection.md` §4–5).
- Don't stop at "I can't log in". Use a test account (below).

---

## 2. Test Accounts

End-to-end testing, by a developer or an AI assistant, uses temporary test accounts, never real ones:

```text
php database/test-users.php create admin|inductee [--incomplete]
php database/test-users.php list
php database/test-users.php delete <email>|--all
```

`create` makes an active, email-verified account and prints its email and a random password. An inductee gets a completed profile unless `--incomplete` is passed, for testing profile completion (`docs/core/users.md` §9). No emails are sent.

Rules:

- **Use the script, not real accounts.** Don't log in as a real user, or change a real account to set up a test (reset its password, untick "Profile completed"). Don't register through `/register.php` just to get an account: registration emails the administrators.
- **Test accounts are marked.** Their email is `e2e-<type>-<8 hex>@test.invalid`. The `.invalid` domain can never receive email, and `delete` refuses any account without the marker, so the script can't remove a real account.
- **Delete them when the test is done**, in the same session. Deleting also removes the exam attempts and compliance records the test created. `list` shows leftovers; `delete --all` removes them.
- **Keep passwords out of the project.** Use the printed password for that test only. Don't write it into code, docs or notes.
- **Watch for emails to real people.** Completing an induction as a test inductee emails the real administrators when instant notifications are on (`docs/core/settings.md` §4). Ask first unless that is what is being tested.
- **Local and development databases only.** On a live site, create test accounts only with the site owner's explicit approval.

Test accounts are disposable by definition, so creating and deleting them is allowed under `docs/rules/data-protection.md`.
