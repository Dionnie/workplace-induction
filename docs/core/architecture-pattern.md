# Architecture Pattern

## Purpose

This document defines the application's general development and architectural pattern.

It intentionally does not define specific business features.

Business features should be added later using this pattern.

The architecture should remain:

- Simple
- Feature-oriented
- Easy to understand
- Easy to maintain
- Easy to extend
- Suitable for a simple PHP application
- Suitable for AI-assisted development
- Free from unnecessary framework-style abstraction

Do not introduce architecture for hypothetical future requirements.

---

# 1. Core Pattern

The application uses:

- Feature slices
- Lightweight MVC
- Role-aware controllers where workflows differ
- Shared services where business logic is shared
- Shared repositories where data access is shared
- Role-specific views where user experiences differ
- Lightweight hooks for secondary side effects
- A database schema blueprint

The general flow is:

```text
Request
   ↓
Controller
   ↓
Authorization
   ↓
Service / Action
   ↓
Repository / Database
   ↓
View
   ↓
Response
```

Not every feature needs every layer.

Create a layer only when it provides a real benefit.

---

# 2. Feature Slices

Organize the application primarily by **business feature**.

Example:

```text
app/
├── Core/
│
├── FeatureA/
├── FeatureB/
├── FeatureC/
└── ...
```

The actual feature names will be determined later.

A feature may contain:

```text
Feature/
├── Controllers/
├── Services/
├── Repositories/
├── Views/
└── hooks.php
```

These directories are optional.

Do not create empty layers simply because the pattern allows them.

---

# 3. Core vs Features

The application has two broad areas:

```text
Core
Features
```

### Core

Contains functionality used across the application.

Examples may include:

- Authentication
- Authorization
- Database connection
- Sessions
- CSRF protection
- Validation utilities
- Routing
- Hooks
- Security utilities
- Shared helpers

Core should remain small.

Do not move feature-specific business logic into Core simply because multiple features currently use it.

### Features

Contain application-specific business functionality.

Each feature should own its own:

- Controllers
- Business logic
- Data access
- Views
- Feature-specific behavior

---

# 4. Lightweight MVC

MVC is used as a practical separation of responsibilities, not as a rigid framework.

## Controller

Handles the request.

Responsible for:

- Reading request data
- Request-level validation
- Authorization
- Calling services/actions
- Selecting views
- Redirecting
- Returning responses

Controllers should remain relatively thin.

Do not put substantial business logic or large SQL queries inside controllers.

---

## Service / Action

Contains application and business logic.

Responsible for:

- Business rules
- Business validation
- Coordinating operations
- Calling repositories
- Performing multi-step operations
- Returning meaningful results

Services should describe **business operations**, not UI behavior.

Example:

```php
$service->create($data);
$service->update($id, $data);
$service->publish($id);
$service->process($id);
```

Actual method names will depend on the feature.

---

## Repository

Handles database persistence and retrieval when a repository layer is useful.

Responsible for:

- Queries
- Inserts
- Updates
- Deletes where appropriate
- Database-specific data access

Example:

```php
$repository->find($id);
$repository->findAll();
$repository->create($data);
$repository->update($id, $data);
```

Repositories should generally not contain:

- UI logic
- HTML
- Redirects
- Authorization decisions
- Presentation concerns

---

## View

Responsible for presentation.

Views should:

- Display supplied data
- Render forms
- Render tables
- Render messages
- Provide UI controls appropriate to the current workflow

Views should not contain substantial business logic.

---

# 5. Role-Specific Workflows

Different roles may interact with the same feature in substantially different ways.

This does not mean the feature must be duplicated.

For example:

```text
Feature
├── Controllers/
│   ├── RoleAController.php
│   └── RoleBController.php
│
├── Services/
│   └── FeatureService.php
│
├── Repositories/
│   └── FeatureRepository.php
│
└── Views/
    ├── role-a/
    └── role-b/
```

This is appropriate when Role A and Role B have genuinely different workflows.

---

# 6. Do Not Duplicate Business Logic by Role

Avoid automatically creating:

```text
RoleAService
RoleBService
RoleARepository
RoleBRepository
```

when the underlying business rules are shared.

Prefer:

```text
Role A Controller
       ↓
Shared Service
       ↓
Shared Repository


Role B Controller
       ↓
Shared Service
       ↓
Shared Repository
```

Roles should primarily affect:

- Authorization
- Request/workflow entry points
- Presentation
- Available actions

They should not automatically create separate business systems.

---

# 7. Role-Specific Controllers

Separate controllers are allowed when workflows differ significantly.

For example:

```text
Role A
  ↓
RoleAController
  ↓
Service


Role B
  ↓
RoleBController
  ↓
Service
```

This is preferable to putting large amounts of role branching inside one controller:

```php
if ($role === 'role_a') {
    // large workflow
} elseif ($role === 'role_b') {
    // another large workflow
}
```

Use separate controllers when doing so makes the workflows easier to understand.

---

# 8. Role-Specific Views

The same feature may have different views for different roles.

Example:

```text
Views/
├── role-a/
│   └── view.php
└── role-b/
    └── view.php
```

This is encouraged when the experiences are substantially different.

Do not force completely different interfaces into one template filled with role conditionals.

However, if two roles have essentially the same interface, share the view.

Prefer reuse when it keeps the code simpler.

---

# 9. Authorization

Authorization is separate from presentation.

A user must not gain access merely because:

- A button is hidden
- A navigation item is hidden
- A URL is difficult to discover
- JavaScript prevents an action
- A particular view is unavailable

Protected operations must perform server-side authorization.

Example:

```php
require_permission('feature.action');
```

or an equivalent centralized authorization helper.

The exact authorization implementation is defined separately.

---

# 10. URLs and Features

URLs may be organized around user areas or features.

Role-specific entry points may exist:

```text
/admin/
```

```text
/some-role/
```

Shared features may exist independently:

```text
/feature/
```

Do not duplicate an entire feature merely because multiple roles can access it.

URL structure is an organizational concern.

Authorization remains the security mechanism.

---

# 11. Shared Business Logic

If multiple workflows perform the same business operation, the underlying operation should normally be shared.

For example:

```text
Controller A
       ↓
       Service
       ↓
Controller B
       ↓
```

Both controllers may call:

```php
$service->performOperation(...);
```

Do not create duplicate implementations simply because two roles use the operation.

---

# 12. Role-Specific Business Operations

A role-specific method is acceptable when the operation itself represents a genuinely different business rule or workflow.

For example:

```php
getAvailableForUser($userId);
```

may be valid if availability is determined by user-specific business rules.

The method should exist because the **operation is meaningful**, not merely because a particular role called it.

Avoid artificial naming such as:

```php
adminGetSomething();
adminUpdateSomething();

userGetSomething();
userUpdateSomething();
```

when the underlying operation is actually the same.

---

# 13. Hooks

The application may use a lightweight hook system for secondary side effects.

Example:

```php
$result = $service->performOperation($data);

do_action('operation_completed', $result);
```

Hooks may be used for:

- Email
- Notifications
- Logging
- Auditing
- Optional integrations
- Other secondary reactions

The hook system should remain small and understandable.

---

# 14. Hooks Are Not the Primary Business Workflow

Do not hide important business operations inside hooks.

Avoid:

```text
Request
 ↓
Hook
 ↓
Unknown listener
 ↓
Important business operation
 ↓
Another hook
 ↓
Another listener
```

Prefer:

```text
Request
 ↓
Controller
 ↓
Service
 ↓
Primary business operation
 ↓
Hook
 ↓
Secondary side effect
```

The primary business workflow must remain explicit and traceable.

A developer should be able to understand the main operation without searching through every registered hook.

---

# 15. Hook Side Effects

Hooks are most appropriate when the primary operation has already succeeded and another part of the application should react.

Example:

```text
Primary Operation
       ↓
Database Success
       ↓
Event / Hook
       ├── Email
       ├── Notification
       └── Log
```

Secondary side effects should generally not invalidate a successful primary operation.

For example:

```text
Database operation succeeds
       ↓
Email fails
       ↓
Primary operation remains successful
       ↓
Email failure is logged
```

If a secondary action is actually required for the primary operation to be valid, it should normally be part of the explicit service workflow rather than an optional hook.

---

# 16. Feature-to-Feature Communication

Features should communicate through explicit services/actions or well-defined application interfaces.

Avoid direct manipulation of another feature's internal data.

Prefer:

```text
Feature A
   ↓
Feature B Service
   ↓
Feature B Repository
```

instead of:

```text
Feature A
   ↓
directly modifies Feature B database tables
```

A feature should generally own its own business rules.

---

# 17. Database Schema Blueprint

The application must maintain an authoritative database schema blueprint.

Recommended structure:

```text
database/
├── schema.sql
└── seeds.sql
```

### `schema.sql`

Contains the actual database structure:

- Tables
- Columns
- Data types
- Indexes
- Constraints
- Foreign keys where appropriate

The schema must be sufficient to recreate the application's database structure from an empty database.

### `seeds.sql`

Contains optional initial/default data.

Do not put environment-specific secrets in seed files.

---

# 18. Database Documentation

Database documentation and SQL serve different purposes.

```text
docs/database.md
database/schema.sql
```

### `docs/database.md`

Explains:

- What the data represents
- Relationships
- Business rules
- Lifecycle rules
- Important constraints
- Why the structure exists

### `database/schema.sql`

Defines the actual SQL implementation.

Both should remain synchronized.

Any intentional database structure change must update the relevant documentation and schema.

---

# 19. No Premature Migration Framework

The application does not require a full migration framework unless deployment requirements justify one.

Initially:

```text
database/schema.sql
database/seeds.sql
```

are sufficient for rebuilding a fresh database.

If the application later requires controlled database upgrades across existing installations, migrations may be introduced at that time.

Do not build a migration framework speculatively.

---

# 20. No Generic CRUD Architecture

Do not create a generic CRUD engine simply because several features have CRUD-like operations.

Features should use their own meaningful operations.

For example:

```text
create
update
delete
publish
archive
process
approve
renew
revoke
```

depending on the actual business requirement.

Do not force every operation into:

```text
create()
read()
update()
delete()
```

when that does not represent the actual business behavior.

---

# 21. No Premature Abstractions

Do not introduce abstractions solely because they are common in larger applications.

Avoid creating these without an actual requirement:

```text
BaseController
BaseService
BaseRepository
GenericRepository
GenericCrudService
RepositoryInterface
ServiceInterface
Factory
Provider
Manager
EventBus
CommandBus
```

Simple, direct code is preferred.

Some repetition is acceptable when it makes the application easier to understand.

---

# 22. AI Development Rules

When implementing a feature, the AI must:

1. Read `AGENTS.md`.
2. Read this architecture pattern.
3. Read the relevant feature documentation.
4. Inspect existing code before creating new patterns.
5. Reuse existing application conventions.
6. Determine which architectural layers are actually necessary.
7. Implement only the requested scope.
8. Avoid creating speculative abstractions.
9. Avoid duplicating business logic.
10. Keep primary workflows explicit.
11. Use hooks only where secondary side effects are actually needed.
12. Update database documentation/schema when database structure changes.
13. Test the affected workflow.
14. Report what changed.
15. STOP.

Do not perform unrelated refactoring.

Do not redesign the architecture during an ordinary feature implementation.

---

# 23. Decision Rules

When deciding where code belongs, use these rules:

### Is it HTTP/request handling?

→ Controller

### Is it business logic?

→ Service / Action

### Is it database access?

→ Repository / Database layer

### Is it presentation?

→ View

### Is it authorization?

→ Central authorization layer

### Is it a secondary reaction to something that already happened?

→ Hook

### Is it shared infrastructure?

→ Core

### Is it specific to one business feature?

→ That feature

### Is it only being added for a hypothetical future requirement?

→ Do not add it yet.

---

# 24. Core Principle

The architecture follows this principle:

> **Separate responsibilities without creating unnecessary layers.**

And:

> **Different user experiences do not require duplicated business logic.**

A feature may have:

- Multiple controllers
- Multiple views
- One shared service
- One shared repository

when different roles require different workflows.

The architecture should remain understandable by reading the feature from top to bottom.

---

# 25. Simplified Mental Model

The entire pattern can be understood as:

```text
                    FEATURE
                       │
              ┌────────┴────────┐
              ↓                 ↓
        Different users     Different users
              ↓                 ↓
         Controllers       Controllers
              │                 │
              └────────┬────────┘
                       ↓
                  Shared Logic
                       ↓
                    Service
                       ↓
                  Repository
                       ↓
                   Database

                       │
                       ↓
                    Views
                 /          \
          Role-specific    Shared
              views         views

                       │
                       ↓
               Optional Hooks
                       ↓
          Secondary Side Effects
```
