# Terminology

Use these terms exactly, in the UI, in code names and in docs. Don't substitute alternatives. A concept keeps the same name on every screen.

## Application

| Concept | Term | Don't use |
| --- | --- | --- |
| Person completing an induction | **Inductee** | Employee, Applicant, Trainee, Student |
| Learning requirement | **Induction** | Course, Training, Program |
| Assessment | **Exam** | Test, Quiz |
| One submission of an exam | **Exam Attempt** | Exam Record, Exam Submission |
| Issued compliance credential | **Compliance Record** | Certification Record, Completion Record |
| Printable proof of a compliance record | **Certificate** | Badge, License, Certification |
| Completing an induction again after expiry | **Renewal** | Re-certification, Recertification |
| An inductee's valid compliance for an induction | **Current Compliance** | Active Certification, Valid Certification |
| An induction's learning material | **Content Blocks** | Lessons, Modules, Sections |
| One screen of induction content | **Slide**: a **Section slide** (first level) or a **Lecture slide** (inside a section) | Page, Screen, Step, Lesson, Module |
| An exam's questions | **Exam Blocks** | Question Blocks |
| One item inside content or an exam | **Block** (a text block, an image block, a question) | Widget, Element, Component |
| The full-screen editor for content or exam blocks | **Studio** | Builder, Designer |

"Section" and "Lecture" name the two slide levels only: say "this induction has 6 sections and 40 lectures". There are exactly two levels.

"Question" is fine in running text ("Question 3 of 20"); **Exam Blocks** is the name of the data and the editor.

## Core

| Concept | Term | Don't use |
| --- | --- | --- |
| Someone with a login | **User** | Member, Account holder |
| A user's kind | **User type**: Administrator (`admin`) or Inductee (`inductee`) | Role, Group |
| Whether a user can log in | **Status**: Active, Inactive, Suspended | Enabled, Locked |
| A user's own details page | **My Profile** | Account settings |
| An administrator using an inductee's account, and returning | **Switch Account**, **Switch Back** | Impersonate, Log in as, Masquerade |
| Uploaded images and their groups | **Media Library**, **Category** | Gallery, Folder, Album |
| Records that are permanently removed | **Delete** | Remove, Erase, Purge |
| Records invalidated but kept | **Revoke** (compliance records only) | Cancel, Void |

## Statuses

Status words are fixed, and each has one badge colour (`status_badge()`, `docs/rules/design.md` §8):

| Record | Statuses |
| --- | --- |
| User | Active, Inactive, Suspended |
| Induction, Exam | Active, Inactive |
| Exam Attempt | Passed, Failed ("Not Passed" to the inductee) |
| Compliance Record | Active, Expired, Superseded, Revoked |
| An induction on the inductee's dashboard | Not Started, Compliant, Expired, Not Passed |

## Labels

Labels say what the field or action is. Prefer Edit, View, Create, Delete, Save, Download, Verify, Renew, Revoke. Avoid vague labels: Manage, Process, Handle, Details, Go.
