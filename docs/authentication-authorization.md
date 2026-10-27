# Authentication & Authorization

## Overview

This project uses a custom Blade-based authentication flow and custom RBAC authorization built on Laravel's session-based web authentication. It does not use Laravel Breeze, Jetstream, Spatie Permission, React, Vue, Livewire, or any authentication starter kit.

The implementation is designed to stay close to Laravel conventions while keeping controllers thin, validation in Form Requests, and role business rules in a service class.

## Completed Phases

- Custom Authentication Flow
- RBAC Data Foundation
- Permission Middleware
- Authenticated Admin Layout
- Role & Permission Management
- Feature Tests

## Authentication Flow

The authentication flow is handled with Laravel's built-in session guard and custom Blade views.

- `GET /login` displays the login form for guest users.
- `POST /login` validates credentials, checks that the user has active status, attempts authentication, and regenerates the session after successful login.
- `POST /logout` logs out the authenticated user, invalidates the session, and regenerates the CSRF token.
- `GET /dashboard` is protected by both `auth` and `permission:dashboard.view`.

There is no public registration flow. Users are created by administrators, seeders, or future module-specific workflows.

Inactive users cannot log in because authentication requires the existing `users.status` column to be `active`.

## Default Super Admin

Default local development credentials:

- Email: `admin@example.com`
- Password: `Password@12345`
- Role: `Super Admin`

Security note: this credential is for local development only. It must be changed before production deployment.

## RBAC Architecture

The authorization model follows this relationship:

```text
User -> Roles -> Permissions
```

The RBAC data foundation uses these tables:

- `users`
- `roles`
- `permissions`
- `role_user`
- `permission_role`

Users receive permissions through their assigned roles. Permissions are checked through helper methods on the `User` model:

- `hasRole(string $role): bool`
- `hasAnyRole(array $roles): bool`
- `hasPermission(string $permission): bool`
- `isSuperAdmin(): bool`

`hasPermission()` returns `true` immediately for the Super Admin role. This gives Super Admin a full authorization bypass while keeping regular users permission-based.

## Default Roles

- `Super Admin`: Full system access and permission bypass.
- `HR Manager`: Operational HR access for employee, department, designation, attendance, leave, payroll, and reporting workflows.
- `Employee`: Limited self-service access, currently dashboard access and leave creation.

## Permission Naming Convention

Permissions use the `module.action` naming convention.

Examples:

- `dashboard.view`
- `employee.create`
- `payroll.delete`
- `report.view`

## Route Protection Pattern

Route protected by authentication:

```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');
```

Route protected by authentication and permission middleware:

```php
Route::get('/admin/roles', [RoleController::class, 'index'])
    ->middleware(['auth', 'permission:role.view']);
```

## Role & Permission Management

Roles are manageable from the admin UI. Administrators with the required permissions can create, edit, and delete roles.

Permissions are system-defined and read-only in the UI. They are created and maintained through seeders, then assigned to roles through the role permission assignment screen.

Important business rules:

- Permissions are assigned to roles.
- The Super Admin role cannot be deleted.
- Roles assigned to users cannot be deleted.
- Permission records are not manually created, edited, or deleted from the UI.

## Seeders

RBAC setup is handled by these seeders:

- `RoleSeeder`
- `PermissionSeeder`
- `SuperAdminSeeder`

`DatabaseSeeder` calls them in this order:

1. `RoleSeeder`
2. `PermissionSeeder`
3. `SuperAdminSeeder`

Run seeders without resetting the database:

```bash
php artisan db:seed
```

Rebuild the database from scratch and seed it:

```bash
php artisan migrate:fresh --seed
```

Warning: `php artisan migrate:fresh --seed` deletes existing database data before recreating tables and running seeders.

## Tests

Run the test suite:

```bash
php artisan test
```

Current authorization test coverage includes:

- Permission middleware behavior
- Role management access and business rules
- Permission management read-only behavior

## Security Decisions

- No public registration.
- Logout uses `POST` only.
- Forms are protected with CSRF tokens.
- Sidebar visibility is permission-based, not role-based.
- Permissions are system-defined and not manually created from the UI.
- Controllers are kept thin.
- Form Requests are used for validation and authorization checks.
- `RoleService` owns role business rules such as delete protection, slug handling, and permission syncing.

## Future Improvements

- User management UI
- Employee account creation from the Employee module
- Policies for model-specific authorization
- Audit logs
- Password reset
- Email verification if needed later
