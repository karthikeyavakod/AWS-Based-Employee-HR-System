# HRMS Laravel - Database ERD

## Purpose

This document describes the Entity Relationship Diagram (ERD) for the HRMS Laravel project.

## ERD Scope

The ERD covers the core HRMS business modules:

- Authentication & Authorization
- Organization Structure
- Attendance Management
- Leave Management
- Payroll Management

Laravel internal infrastructure tables such as `migrations`, `cache`, and `jobs` are not included in the main business ERD.

## Architecture Notes

- `users` table handles authentication identity.
- `employees` table handles HR/business identity.
- Role and permission management uses pivot tables.
- Attendance, leave, and payroll data are separated into transactional tables.
- Payroll uses an itemized payslip structure for production-style salary breakdown.

## Text-based ERD

### Authentication & Authorization

```text
users many-to-many roles through role_user
roles many-to-many permissions through permission_role
```

### Organization Structure

```text
users 1 to 0/1 employees
departments 1 to many designations
departments 1 to many employees
designations 1 to many employees
```

### Attendance Management

```text
employees 1 to many attendances
```

### Leave Management

```text
employees 1 to many leave_requests
leave_types 1 to many leave_requests
employees 1 to many leave_balances
leave_types 1 to many leave_balances
```

### Payroll Management

```text
employees 1 to many employee_salary_structures
salary_components 1 to many employee_salary_structures
payroll_runs 1 to many payslips
employees 1 to many payslips
payslips 1 to many payslip_items
salary_components 1 to many payslip_items
```

## Mermaid ERD

```mermaid
erDiagram
    USERS {
        bigint id PK
        string name
        string email
        string password
        string status
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    ROLES {
        bigint id PK
        string name
        string slug
        string description
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    PERMISSIONS {
        bigint id PK
        string name
        string slug
        string description
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    ROLE_USER {
        bigint id PK
        bigint user_id FK
        bigint role_id FK
        timestamp created_at
        timestamp updated_at
    }

    PERMISSION_ROLE {
        bigint id PK
        bigint role_id FK
        bigint permission_id FK
        timestamp created_at
        timestamp updated_at
    }

    DEPARTMENTS {
        bigint id PK
        string name
        string code
        string description
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    DESIGNATIONS {
        bigint id PK
        bigint department_id FK
        string title
        string code
        string description
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    EMPLOYEES {
        bigint id PK
        bigint user_id FK
        bigint department_id FK
        bigint designation_id FK
        string employee_code
        string phone
        string gender
        date date_of_birth
        date joining_date
        string employment_status
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    ATTENDANCES {
        bigint id PK
        bigint employee_id FK
        date attendance_date
        time check_in
        time check_out
        string status
        text remarks
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    LEAVE_TYPES {
        bigint id PK
        string name
        string code
        int default_days
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    LEAVE_REQUESTS {
        bigint id PK
        bigint employee_id FK
        bigint leave_type_id FK
        date start_date
        date end_date
        decimal total_days
        string status
        text reason
        bigint approved_by
        timestamp approved_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    LEAVE_BALANCES {
        bigint id PK
        bigint employee_id FK
        bigint leave_type_id FK
        int year
        decimal total_days
        decimal used_days
        decimal remaining_days
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    SALARY_COMPONENTS {
        bigint id PK
        string name
        string code
        string type
        boolean is_taxable
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    EMPLOYEE_SALARY_STRUCTURES {
        bigint id PK
        bigint employee_id FK
        bigint salary_component_id FK
        decimal amount
        date effective_from
        date effective_to
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    PAYROLL_RUNS {
        bigint id PK
        int payroll_month
        int payroll_year
        date payment_date
        string status
        timestamp processed_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    PAYSLIPS {
        bigint id PK
        bigint payroll_run_id FK
        bigint employee_id FK
        decimal gross_salary
        decimal total_deductions
        decimal net_salary
        string status
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    PAYSLIP_ITEMS {
        bigint id PK
        bigint payslip_id FK
        bigint salary_component_id FK
        decimal amount
        string type
        timestamp created_at
        timestamp updated_at
    }

    USERS ||--o{ ROLE_USER : "assigned roles"
    ROLES ||--o{ ROLE_USER : "assigned users"
    ROLES ||--o{ PERMISSION_ROLE : "has permissions"
    PERMISSIONS ||--o{ PERMISSION_ROLE : "belongs to roles"
    USERS ||--o| EMPLOYEES : "has employee profile"
    DEPARTMENTS ||--o{ DESIGNATIONS : "has designations"
    DEPARTMENTS ||--o{ EMPLOYEES : "has employees"
    DESIGNATIONS ||--o{ EMPLOYEES : "assigned to employees"
    EMPLOYEES ||--o{ ATTENDANCES : "has attendance records"
    EMPLOYEES ||--o{ LEAVE_REQUESTS : "submits leave requests"
    LEAVE_TYPES ||--o{ LEAVE_REQUESTS : "used in leave requests"
    EMPLOYEES ||--o{ LEAVE_BALANCES : "has leave balances"
    LEAVE_TYPES ||--o{ LEAVE_BALANCES : "has leave balances"
    EMPLOYEES ||--o{ EMPLOYEE_SALARY_STRUCTURES : "has salary structures"
    SALARY_COMPONENTS ||--o{ EMPLOYEE_SALARY_STRUCTURES : "used in salary structures"
    PAYROLL_RUNS ||--o{ PAYSLIPS : "generates payslips"
    EMPLOYEES ||--o{ PAYSLIPS : "receives payslips"
    PAYSLIPS ||--o{ PAYSLIP_ITEMS : "has payslip items"
    SALARY_COMPONENTS ||--o{ PAYSLIP_ITEMS : "used in payslip items"
```

