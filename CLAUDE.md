# Project Instructions

## Project

Generic, white-label Flat-file PHP Induction System.

## Source of Truth

- Database structure: `/database/schema.sql`
- Core documentation: `/docs/core/`
- Application documentation: `/docs/application/`

## General Rule

- Prefer the simplest implementation that satisfies the requirement.
- Do not introduce architecture, abstractions, dependencies, UI patterns, or terminology unless they are necessary.

## Documentation Rules

- Do not automatically read every Markdown file.
- Read documentation based on the task:

## For Project Architecture Decision

- Consult: `docs\core\architecture-pattern.md`

## For Users, Authentication and Security

- Consult: `docs\core\auth.md`

## For Public / Landing Pages

- Consult: `docs\core\public_page.md`

## For UI/UX Design Decision

- Consult: `docs\core\ui-guidelines.md`.
- Comply to the authority for markup, classes and patterns: `docs\core\design-system.html`.
- If applicable, make sure it works along with `/admin/settings/index.php?tab=appearance`.
- UI/UX Design may evolve when justified, it may may change there is a legitimate technical or product reason.

## For making database schema changes, migrations, seed changes, or changes that affect existing records.

- Consult: `/docs/core/data-protection.md`.
- `/database/schema.sql` defines the current desired database structure. It does not authorize replacing an existing database with that structure.
- Database structure may evolve when justified. Prefer data-preserving migrations over destructive resets.

## For Induction related task, follow this

- Inductions : `/docs/application/inductions.md`
- Terminologies : `/docs/application/terminology.md`
- Content Blocks Editor : `docs\application\content_blocks_editor.md`
