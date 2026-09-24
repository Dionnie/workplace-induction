# Data Protection & Database Changes

## Purpose

Define how the application and database must be handled once real data exists.

During early development, the database may contain only disposable test data, making destructive changes acceptable when useful.

Once real data exists, the database must no longer be treated as disposable.

The goal is **not to freeze the database structure**.

The goal is to protect meaningful existing data while allowing the application and database architecture to evolve.

---

# 1. Core Principle

> **Protect real data, not the current schema.**

The database structure may change when there is a legitimate technical or product reason.

Examples of acceptable structural changes include:

- UUID → incremental ID
- Rename a column
- Split a table
- Merge tables
- Add columns
- Remove obsolete columns
- Change relationships
- Normalize data
- Simplify an over-engineered structure
- Change indexes
- Change constraints
- Introduce new supporting tables

These changes are allowed when existing meaningful data can be preserved or intentionally migrated.

The question is not:

> "Can we change the database?"

The question is:

> "Can we make this change without unnecessarily destroying or changing existing data?"

---

# 2. Real Data Is Protected

Once real data exists, treat meaningful historical and operational information as protected.

This includes, depending on the application:

- User accounts
- User profiles
- Transactions
- Records of completed actions
- Historical records
- Status history
- Dates and timestamps
- Relationships between records
- Reference numbers
- External identifiers
- Audit information
- Uploaded or associated files
- Other information that represents something that actually happened

If a record represents a real event, transaction, action, or historical state, assume it is important unless explicitly determined otherwise.

---

# 3. Development Mode vs Real Data Mode

The project effectively has two states.

## Development / Test Data

When the database contains only disposable test data:

```text
Destructive changes may be acceptable.
```

Examples:

```text
DROP TABLE
TRUNCATE TABLE
Rebuild database
Reset test records
Recreate schema
```

This allows fast development and experimentation.

## Real Data

Once the database contains real data:

```text
Destructive database resets are prohibited by default.
```

Do not use:

```text
DROP TABLE
TRUNCATE TABLE
DELETE FROM table
```

as a convenient way to apply normal application changes.

A developer may still completely rebuild a **separate local development database** containing only disposable test data.

---

# 4. Schema and Data Are Different

The schema describes how information is stored.

The data represents what actually exists or happened.

These are separate concerns.

For example, adding:

```text
users.status
```

changes the schema.

It does not justify deleting existing users.

Likewise, changing:

```text
users.id = UUID
```

to:

```text
users.id = AUTO_INCREMENT INT
```

may be a perfectly valid architectural improvement.

The identifier may change.

The underlying user data should not be unnecessarily lost.

---

# 5. Schema Changes Must Be Intentional

Before changing a database containing real data, determine:

1. What is changing?
2. Why is it changing?
3. What existing data is affected?
4. What relationships depend on the affected data?
5. Can the existing data be migrated?
6. What historical information must remain unchanged?
7. What happens if the migration fails?
8. Has the migration been tested on a copy?
9. Is a backup available?

Do not modify the real database first and figure out the migration afterward.

---

# 6. Prefer Migrations Over Resets

When the schema changes, prefer:

```text
Current Schema
      ↓
Migration
      ↓
New Schema
```

over:

```text
Current Schema
      ↓
DROP / RESET
      ↓
New Schema
```

A migration should explicitly transform existing data where necessary.

The purpose of a migration is to move an existing database safely from one valid structure to another.

---

# 7. Example: UUID → Incremental ID

Changing identifier architecture is allowed.

For example:

```text
Before:
id = UUID
```

may become:

```text
After:
id = AUTO_INCREMENT
```

This does not mean existing records must be deleted and recreated.

A migration can:

1. Create the new identifier structure.
2. Assign new IDs to existing records.
3. Preserve the mapping between old and new identifiers.
4. Update affected foreign keys.
5. Verify relationships.
6. Remove the obsolete identifier only after verification.

Conceptually:

```text
Existing Record
UUID: abc-123
Data: existing information
        ↓
Migration
        ↓
Existing Record
ID: 42
Data: same information
```

The identifier changed.

The record did not.

---

# 8. Preserve Relationships

Database changes must not accidentally break relationships between existing records.

If:

```text
Record A
   ↓
Record B
   ↓
Record C
```

exists before a migration, the same logical relationships should exist afterward unless intentionally changed.

After a structural migration, verify:

- Record ownership
- Foreign-key relationships
- Parent/child relationships
- Historical relationships
- Reference mappings
- Cross-table associations

Changing a primary key or table structure requires particular care because other records may depend on it.

---

# 9. Preserve Historical Information

Historical information should not be changed simply because the database structure has changed.

This includes:

- Creation dates
- Update dates where historically meaningful
- Completion dates
- Transaction dates
- Issue dates
- Expiry dates
- Reference numbers
- Historical statuses
- Original values
- Other recorded timestamps

For example, if a record was created on:

```text
2026-03-15
```

a migration performed months later must not replace that date with the migration date.

A migration should preserve the historical meaning of the record.

---

# 10. Do Not Recreate Historical Records Unnecessarily

Avoid migrations that do:

```text
Read old record
      ↓
Delete old record
      ↓
Create new record
```

when this causes unnecessary changes to:

- Primary keys
- Creation dates
- Historical relationships
- Reference numbers
- Audit information
- Other historical attributes

Prefer transforming existing records when practical.

Recreation may be acceptable when technically necessary, but the migration must explicitly preserve all required historical information and relationships.

---

# 11. Backups Before Real-Data Changes

Before applying a significant structural change to a real-data database:

```text
Backup
  ↓
Test Migration
  ↓
Verify Result
  ↓
Apply to Real Database
  ↓
Verify Again
```

A usable backup must exist before performing a migration that could affect existing data.

For significant changes, retain the pre-migration backup until the new database has been verified.

---

# 12. Test Migrations on a Copy

A migration should be tested against a copy of the database before being applied to the real database.

The test database should contain realistic data patterns, including:

- Existing records
- Older records
- Related records
- Null values
- Optional values
- Existing relationships
- Different statuses
- Edge cases

The purpose is to discover data-loss or relationship problems before they affect the real database.

---

# 13. Verify Before and After

For significant migrations, compare important data before and after.

Examples:

```text
Record counts
User counts
Relationship counts
Historical record counts
```

Also verify:

```text
Important dates
Reference numbers
Relationships
Foreign keys
Statuses
Existing values
```

A migration successfully completing does not automatically mean the data is correct.

The resulting database must still represent the same information.

---

# 14. Do Not Protect Bad Architecture

Data protection does not mean preserving every existing technical decision forever.

The application may be improved when a better structure becomes clear.

It is acceptable to:

```text
Change identifiers
Rename fields
Split tables
Merge tables
Change relationships
Normalize data
Remove obsolete fields
Simplify over-engineered structures
```

provided the existing meaningful data is handled deliberately.

Do not allow fear of touching the database to permanently preserve a poor architecture.

---

# 15. Destructive Changes Require Explicit Intent

Any change capable of destroying real data is considered high-risk.

Examples include:

```text
DROP TABLE
TRUNCATE TABLE
DELETE without a narrowly defined condition
Removing a column containing meaningful data
Changing a column in a way that truncates values
Replacing existing records
Overwriting production data
```

These actions must never be introduced casually.

If a destructive operation is genuinely required, it must be:

1. Explicitly identified.
2. Intentionally approved.
3. Backed up where appropriate.
4. Tested first.
5. Executed deliberately.

---

# 16. AI / Vibe Coding Rule

AI-generated code must assume that the database contains real data unless the developer explicitly states otherwise.

The AI must not automatically:

- Drop tables
- Recreate tables
- Truncate tables
- Reset the database
- Delete existing records
- Replace real data with seed data
- Rewrite historical dates
- Recreate historical records unnecessarily
- Change identifiers without considering dependent relationships

When a schema change is required, the AI should first reason through:

```text
Current Structure
        ↓
Required Structure
        ↓
Affected Existing Data
        ↓
Required Migration
        ↓
Data Preservation Strategy
        ↓
Verification
```

The developer may explicitly approve destructive operations when working with disposable test data.

---

# 17. Unknown Data Must Be Treated as Real

If the AI cannot determine whether a database contains real data, it must treat the data as **protected**.

Safe assumption:

```text
Unknown Database
      ↓
Treat as Real Data
```

Do not assume:

```text
"This is probably just development data."
```

The developer must explicitly establish when destructive operations are safe.

---

# 18. Schema Source of Truth

The canonical database schema is:

```text
/database/schema.sql
```

The schema defines the **desired current structure**.

It does not authorize destructive replacement of an existing database.

When real data exists:

```text
schema.sql
      ↓
Desired Structure

migration
      ↓
Safe Path From Existing Structure
      ↓
Desired Structure
```

The schema defines where the database should end up.

The migration defines how existing data safely gets there.

---

# 19. Migration Philosophy

The project does not need unnecessary enterprise-level migration complexity.

The appropriate approach depends on the project stage.

### Early development

```text
Fast schema iteration
+
Disposable test data
=
Rebuild when practical
```

### Real data

```text
Controlled schema changes
+
Data-preserving migrations
+
Backups
+
Verification
=
Safe database evolution
```

The goal is practical protection, not bureaucracy.

---

# 20. Definition of a Safe Change

A database change is considered safe when:

- The structural change has a clear reason.
- Existing meaningful data has been identified.
- Historical information is preserved.
- Relationships remain valid.
- A backup exists where appropriate.
- The migration has been tested.
- The resulting data has been verified.
- Destructive operations are not being used merely for convenience.

The database is allowed to evolve.

**The architecture is not sacred.**

**The data is not disposable.**
