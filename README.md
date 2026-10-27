# Smart HR Connect

Smart HR Connect is an HR management application built with Laravel 12. It provides permission-controlled administration for employee records and core HR workflows.

## Technology

- PHP 8.2+
- Laravel 12
- MySQL
- Bootstrap 5
- PHPUnit

## Current Features

- Custom authentication with active-account checks
- Role and permission management (Super Admin, HR Manager, Department Manager, Employee)
- Permission-protected administration
- Employee, department, and designation management
- Admin-controlled employee account setup by expiring password-reset link
- Role assignment during onboarding with Super Admin excluded from the employee form
- Account activation/deactivation and inactive-user login protection
- Employee self-service profile, attendance, leave balance/history, and paid-payslip overview
- Employee self-service daily check-in/check-out with duplicate prevention and work-minute calculation
- Attendance management with correction requests
- Employee-scoped leave requests with approval/rejection and balance deduction
- Payroll records and net salary calculation with approval workflow
- Dashboard summaries and paginated read-only reports
- Company announcements for all employees
- Department manager assignment with team-scoped authorization
- Audit logs for sensitive administrative actions
- Automated feature tests for core administration and authorization

## Current Gaps

The following capabilities are not currently present or are limited and need additional implementation and verification:

- Downloadable payslips (PDF export)
- Report CSV/Excel exports
- Configurable late/half-day business rules for attendance
- Department-wise attendance summaries

The employee portal only exposes the signed-in employee's related records. Employee profile editing is limited to phone and address. Leave balance records are initialized from a leave type's configured default days when the first request is approved. Do not use shared demo credentials or generated sample accounts in production.

## Setup

1. Install PHP and Composer dependencies.
2. Create `.env` from `.env.example` and configure the database.
3. Generate an application key and run migrations with seed data.
4. Start the development server.

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

For PowerShell, `Copy-Item .env.example .env` can be used instead of `copy`.

The local development seeder creates a Super Admin account. Configure and change its credentials before using any shared or production environment.

## Testing

Run the automated test suite with:

```bash
php artisan test
```

## Useful Commands

```bash
php artisan route:list -v --except-vendor
php artisan about
php artisan test
```

## Production Notes

- Set `APP_DEBUG=false` and use a unique, secure application key.
- Configure mail delivery and database credentials through environment variables.
- Change seeded administrator credentials before deployment.
- Do not commit `.env` or other secrets.
- Back up the database before applying migrations to a database with real employee data.

## Documentation

- [Database ERD](docs/database/erd.md)
- [Authentication & Authorization](docs/authentication-authorization.md)
- [Testing & Quality Review](docs/testing-quality.md)
