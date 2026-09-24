# Project Instructions

## Project

Generic, white-label PHP Induction System.

## Architecture

- Flat-file PHP application.
- Bootstrap 5 for UI.
- Keep implementation practical and avoid unnecessary abstractions.

## Source of Truth

- Database structure: `/database/schema.sql`
- Core documentation: `/docs/core/`
- Application documentation: `/docs/application/`

## Documentation Rules

Do not automatically read every Markdown file.

Read documentation based on the task:

- Authentication → `/docs/core/authentication.md`
- Public page → `/docs/core/public-page.md`
- UI → `/docs/core/ui-guideline.md`
- Inductions → `/docs/application/inductions.md`
- Terminologies → `/docs/application/terminology.md`

## Data Protection

Read `/docs/core/data-protection.md` before making database schema changes, migrations, seed changes, or changes that affect existing records.

Once real data exists, treat the database as protected. Do not drop, truncate, reset, recreate, or overwrite existing data unless the developer explicitly confirms the database contains disposable test data or explicitly approves the destructive operation.

Database structure may evolve when justified. Prefer data-preserving migrations over destructive resets.

Protect historical data, relationships, identifiers, dates, and other meaningful existing records. Do not preserve a poor schema merely for the sake of avoiding change.

`/database/schema.sql` defines the current desired database structure. It does not authorize replacing an existing database with that structure.

## General Rule

Prefer the simplest implementation that satisfies the requirement.

Do not introduce architecture, abstractions, dependencies, UI patterns, or terminology unless they are necessary.
