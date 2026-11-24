<?php
// section_design_implementation.php - Chapters 9 and 10

// -------------------------------------------------------------
// CHAPTER 9: SYSTEM DESIGN AND MODELING
// -------------------------------------------------------------
$b->addHeading1("CHAPTER 9: SYSTEM DESIGN AND MODELING");
$b->addParagraph("System design transforms the functional and non-functional requirements established in the SRS into precise architectural blueprints, component interactions, procedural data flows, and relational database schemas. The structural and behavioral designs of Smart HR Connect are detailed across the following sections.");

$b->addHeading2("9.1 System Architecture Perspective");
$b->addParagraph("Smart HR Connect employs a multi-tiered, decoupled client-server architecture organized into three primary conceptual layers: Presentation Layer, Application Business Layer, and Data Persistence Layer.");
$b->addBullet("Rendered server-side using Laravel Blade templates styled with Bootstrap 5.3.3 and Tailwind CSS. Client browsers execute standard HTTP/HTTPS requests to interact with responsive forms, dynamic data tables, and administrative dashboard cards.", "Presentation Layer (Client):");
$b->addBullet("Operates on Apache HTTP Server and PHP 8.2-FPM. Coordinates incoming HTTP traffic through routes, applies authentication and RBAC middleware, validates payloads via Form Requests, dispatches tasks to Controller endpoints, and delegates complex business operations to domain Service classes.", "Application & Business Service Layer:");
$b->addBullet("Comprises MySQL 8.0 / MariaDB relational database tables accessed via Eloquent ActiveRecord ORM, supplemented by private Amazon S3 object storage for binary files and PDF payslip archives.", "Data Persistence Layer:");

$b->addHeading2("9.2 Data Flow Diagrams (DFD)");
$b->addParagraph("The Data Flow Diagram (DFD) visually delineates how information enters the system, undergoes operational transformations across processing bubbles, interacts with data stores, and produces output artifacts for external entities.");
$b->addParagraph("Fig 5.2.1 illustrates the Context-Level (Level 0) and Level 1 Data Flow Diagram for Smart HR Connect. Four primary external entities—Employee, HR Manager, Department Manager, and Super Admin—interact with the central Smart HR Connect engine (Process 0.0), which reads and persists records to the centralized HRMS database store (D1).");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_dfd.png", "Fig 5.2.1: Context Diagram / Data Flow Diagram (DFD Level 0 & Level 1)");

$b->addHeading2("9.3 Unified Modeling Language (UML) Diagrams");
$b->addParagraph("Unified Modeling Language (UML) provides standardized graphical notations for specifying, visualizing, and documenting object-oriented software systems.");

$b->addHeading3("9.3.1 UML Use Case Diagram");
$b->addParagraph("The Use Case Diagram defines the functional boundaries of the system and illustrates the interactions between external actors and system use cases. As depicted in Fig 5.3.1, the Employee actor interacts with self-service use cases (Check-In/Out, Apply Leave, View Payslips). The HR Manager oversees employee onboarding, attendance management, and monthly payroll execution. The Department Manager reviews staff attendance and executes leave approvals. The Super Admin governs user roles, permission matrices, and security audit logs.");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_usecase.png", "Fig 5.3.1: UML Use Case Diagram (Smart HR Connect)");

$b->addHeading3("9.3.2 UML Sequence Diagram");
$b->addParagraph("The Sequence Diagram models the chronological message exchange between system objects during a specific business workflow. Fig 5.4.1 illustrates the complete Leave Request and Approval Lifecycle. The sequence traces an employee submitting a leave application, the validation of leave quotas and overlap collisions by LeaveService, database persistence of a pending record, notification to the Department Manager, and the subsequent approval transaction which decrements the employee's available leave balance.");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_sequence.png", "Fig 5.4.1: UML Sequence Diagram - Leave Request & Approval Lifecycle Workflow");

$b->addHeading3("9.3.3 UML Collaboration / Component Diagram");
$b->addParagraph("The Collaboration / Component Diagram (Fig 5.5.1) illustrates structural relationships and object collaborations between software modules. It demonstrates how the Client Presentation Layer interacts with the HTTP Kernel and Security Middleware, which delegates to Application Services, while Eloquent ORM interfaces with the MySQL Database and Amazon S3 Storage Layer.");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_collaboration.png", "Fig 5.5.1: Collaboration & Component Architecture Diagram");

$b->addHeading3("9.3.4 Activity Diagrams & Flow Charts");
$b->addParagraph("Activity diagrams capture the procedural flow of control and decision logic across different organizational roles:");
$b->addBullet("Fig 5.6.1 depicts the Employee flow: authentication, dashboard navigation, clock-in, leave application, and payslip review.", "Employee Self-Service Workflow:");
$b->addBullet("Fig 5.6.2 depicts the HR Administrator flow: authentication, workforce onboarding, department configuration, attendance verification, payroll execution, and report export.", "HR Administrator Workflow:");
$b->addBullet("Fig 5.6.3 depicts the Department Manager flow: authentication, department staff roster review, shift attendance monitoring, and leave evaluation/approval.", "Department Manager Workflow:");
$b->addBullet("Fig 5.6.4 depicts the Super Admin flow: system health inspection, role/permission configuration, user activation, and compliance audit trail analysis.", "Super Admin Governance Workflow:");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_activity_employee.png", "Fig 5.6.1: Flow Chart / Activity Diagram: Employee Self-Service Workflow");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_activity_hr.png", "Fig 5.6.2: Flow Chart / Activity Diagram: HR Administrator Operational Workflow");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_activity_dept.png", "Fig 5.6.3: Flow Chart / Activity Diagram: Department Manager Approval Workflow");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_activity_admin.png", "Fig 5.6.4: Flow Chart / Activity Diagram: Super Admin Governance & RBAC Workflow");

$b->addHeading2("9.4 Database Design and Entity Relationship Diagram (ERD)");
$b->addParagraph("The database architecture of Smart HR Connect is designed in strict adherence to third normal form (3NF) relational principles, ensuring zero data redundancy, complete referential integrity, and high query performance. Fig 5.7.1 illustrates the complete Entity Relationship Diagram (ERD) mapping relationships across Users, Roles, Permissions, Employees, Departments, Designations, Attendances, Leave Requests, and Payroll Runs.");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_erd.png", "Fig 5.7.1: Entity Relationship Diagram (ERD) - Relational Schema Architecture");

$b->addHeading2("9.5 Data Dictionary and Schema Specifications");
$b->addParagraph("The physical database tables, column definitions, data types, constraints, and relational roles are documented in Tables 5.1 through 5.8.");

// Table 5.1: users
$b->addTable(
    ["Column Name", "Data Type", "Null / Def", "Constraint", "Description"],
    [
        ["id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique user identifier"],
        ["name", "VARCHAR(255)", "No", "None", "Full user name"],
        ["email", "VARCHAR(255)", "No", "Unique", "Unique user email address for login"],
        ["password", "VARCHAR(255)", "No", "None", "Bcrypt hashed password string"],
        ["status", "ENUM", "No / active", "None", "Account status ('active', 'inactive')"],
        ["email_verified_at", "TIMESTAMP", "Yes / NULL", "None", "Email verification timestamp"],
        ["created_at", "TIMESTAMP", "Yes / NULL", "None", "Record creation timestamp"],
        ["updated_at", "TIMESTAMP", "Yes / NULL", "None", "Record modification timestamp"]
    ],
    "Table 5.1: Database Schema: users (Authentication Identity Table)",
    [1800, 1800, 1400, 1600, 2760]
);

// Table 5.2: roles and permissions
$b->addTable(
    ["Column Name", "Data Type", "Null / Def", "Constraint", "Description"],
    [
        ["roles.id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique role identifier"],
        ["roles.name", "VARCHAR(255)", "No", "None", "Role display name (e.g., 'HR Manager')"],
        ["roles.slug", "VARCHAR(255)", "No", "Unique", "Unique role identifier slug"],
        ["permissions.id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique permission identifier"],
        ["permissions.name", "VARCHAR(255)", "No", "None", "Permission name (module.action)"],
        ["role_user", "PIVOT TABLE", "No", "Composite FK", "user_id -> users.id, role_id -> roles.id"],
        ["permission_role", "PIVOT TABLE", "No", "Composite FK", "role_id -> roles.id, perm_id -> perms.id"]
    ],
    "Table 5.2: Database Schema: roles and permissions (RBAC Foundation Tables)",
    [1800, 1800, 1400, 1600, 2760]
);

// Table 5.3: departments and designations
$b->addTable(
    ["Column Name", "Data Type", "Null / Def", "Constraint", "Description"],
    [
        ["dept.id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique department identifier"],
        ["dept.name", "VARCHAR(255)", "No", "None", "Department name (e.g., Engineering)"],
        ["dept.code", "VARCHAR(50)", "No", "Unique", "Department short code (e.g., 'ENG')"],
        ["dept.is_active", "BOOLEAN", "No / 1", "None", "Department active flag"],
        ["desig.id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique designation identifier"],
        ["desig.department_id", "BIGINT UNSIGNED", "No", "FK", "Foreign key references departments.id"],
        ["desig.title", "VARCHAR(255)", "No", "None", "Designation title (e.g., Senior Engineer)"],
        ["desig.code", "VARCHAR(50)", "No", "Unique", "Designation short code"]
    ],
    "Table 5.3: Database Schema: departments and designations (Org Hierarchy Tables)",
    [1800, 1800, 1400, 1600, 2760]
);

// Table 5.4: employees
$b->addTable(
    ["Column Name", "Data Type", "Null / Def", "Constraint", "Description"],
    [
        ["id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique employee primary key"],
        ["user_id", "BIGINT UNSIGNED", "Yes / NULL", "FK, Unique", "Foreign key references users.id"],
        ["department_id", "BIGINT UNSIGNED", "No", "FK", "Foreign key references departments.id"],
        ["designation_id", "BIGINT UNSIGNED", "No", "FK", "Foreign key references designations.id"],
        ["employee_code", "VARCHAR(50)", "No", "Unique", "Alphanumeric code (e.g., 'EMP001')"],
        ["phone", "VARCHAR(20)", "Yes / NULL", "None", "Employee contact phone number"],
        ["joining_date", "DATE", "No", "None", "Official date of employment commencement"],
        ["employment_status", "VARCHAR(50)", "No / active", "None", "Status: active, probation, terminated"]
    ],
    "Table 5.4: Database Schema: employees (Personnel Record Table)",
    [1800, 1800, 1400, 1600, 2760]
);

// Table 5.5: attendances
$b->addTable(
    ["Column Name", "Data Type", "Null / Def", "Constraint", "Description"],
    [
        ["id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique attendance record identifier"],
        ["employee_id", "BIGINT UNSIGNED", "No", "FK", "Foreign key references employees.id"],
        ["attendance_date", "DATE", "No", "Indexed", "Calendar date of attendance"],
        ["check_in", "TIME", "Yes / NULL", "None", "Check-in recorded timestamp"],
        ["check_out", "TIME", "Yes / NULL", "None", "Check-out recorded timestamp"],
        ["worked_minutes", "INTEGER", "Yes / 0", "None", "Computed total minutes worked"],
        ["status", "VARCHAR(50)", "No / present", "None", "Attendance status: present, late, half_day"],
        ["remarks", "TEXT", "Yes / NULL", "None", "Optional administrative notes / remarks"]
    ],
    "Table 5.5: Database Schema: attendances (Workforce Time-Tracking Table)",
    [1800, 1800, 1400, 1600, 2760]
);

// Table 5.6: leave_requests and leave_balances
$b->addTable(
    ["Column Name", "Data Type", "Null / Def", "Constraint", "Description"],
    [
        ["lr.id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique leave request identifier"],
        ["lr.employee_id", "BIGINT UNSIGNED", "No", "FK", "Foreign key references employees.id"],
        ["lr.leave_type_id", "BIGINT UNSIGNED", "No", "FK", "Foreign key references leave_types.id"],
        ["lr.start_date", "DATE", "No", "None", "First day of requested leave period"],
        ["lr.end_date", "DATE", "No", "None", "Last day of requested leave period"],
        ["lr.total_days", "DECIMAL(4,1)", "No", "None", "Total requested leave duration in days"],
        ["lr.status", "VARCHAR(50)", "No / pending", "None", "Status: pending, approved, rejected"],
        ["lr.approved_by", "BIGINT UNSIGNED", "Yes / NULL", "FK", "User ID of reviewing manager"],
        ["lb.used_days", "DECIMAL(4,1)", "No / 0.0", "None", "Total quota days utilized from balance"]
    ],
    "Table 5.6: Database Schema: leave_requests and leave_balances (Leave Engine Tables)",
    [1800, 1800, 1400, 1600, 2760]
);

// Table 5.7: payroll_runs and payslips
$b->addTable(
    ["Column Name", "Data Type", "Null / Def", "Constraint", "Description"],
    [
        ["pr.id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique payroll batch run identifier"],
        ["pr.pay_period", "VARCHAR(20)", "No", "Unique", "Monthly period string (e.g., '2026-11')"],
        ["pr.status", "VARCHAR(50)", "No / draft", "None", "Batch status: draft, approved, paid"],
        ["ps.id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique payslip record identifier"],
        ["ps.employee_id", "BIGINT UNSIGNED", "No", "FK", "Foreign key references employees.id"],
        ["ps.basic_salary", "DECIMAL(12,2)", "No", "None", "Base remuneration amount"],
        ["ps.allowances", "DECIMAL(12,2)", "No", "None", "Aggregated earnings & allowances"],
        ["ps.deductions", "DECIMAL(12,2)", "No", "None", "Aggregated statutory & other deductions"],
        ["ps.net_salary", "DECIMAL(12,2)", "No", "None", "Computed net remuneration (Basic+Allow-Ded)"]
    ],
    "Table 5.7: Database Schema: payroll_runs and payslips (Compensation Tables)",
    [1800, 1800, 1400, 1600, 2760]
);

// Table 5.8: audit_logs
$b->addTable(
    ["Column Name", "Data Type", "Null / Def", "Constraint", "Description"],
    [
        ["id", "BIGINT UNSIGNED", "No", "PK, Auto Inc", "Unique audit log record identifier"],
        ["user_id", "BIGINT UNSIGNED", "Yes / NULL", "FK", "User ID of actor performing the mutation"],
        ["action", "VARCHAR(255)", "No", "Indexed", "Action verb (e.g., 'created', 'updated', 'deleted')"],
        ["auditable_type", "VARCHAR(255)", "No", "Indexed", "Target Eloquent model class name"],
        ["auditable_id", "BIGINT UNSIGNED", "No", "Indexed", "Target entity model primary key ID"],
        ["old_values", "JSON", "Yes / NULL", "None", "JSON payload of pre-mutation attribute state"],
        ["new_values", "JSON", "Yes / NULL", "None", "JSON payload of post-mutation attribute state"],
        ["ip_address", "VARCHAR(45)", "Yes / NULL", "None", "Client IPv4 or IPv6 network address"],
        ["created_at", "TIMESTAMP", "No", "Indexed", "Immutable event occurrence timestamp"]
    ],
    "Table 5.8: Database Schema: audit_logs (Enterprise Security Audit Table)",
    [1800, 1800, 1400, 1600, 2760]
);
$b->addPageBreak();

// -------------------------------------------------------------
// CHAPTER 10: SYSTEM IMPLEMENTATION
// -------------------------------------------------------------
$b->addHeading1("CHAPTER 10: SYSTEM IMPLEMENTATION");

$b->addHeading2("10.1 Model Architecture and Engineering Design Patterns");
$b->addParagraph("The engineering implementation of Smart HR Connect follows a highly disciplined, modular architectural structure. To ensure long-term code maintainability, controllers are kept strictly thin, acting purely as HTTP coordinators. Validation and incoming authorization are offloaded entirely to Form Request classes, while core business rules reside within dedicated Service classes.");
$b->addBullet("AttendanceService encapsulates the single check-in validation algorithm, worked duration calculation, and attendance correction state updates.", "AttendanceService:");
$b->addBullet("LeaveService manages leave request quota validations, date overlap collision checks, and transactional leave balance deductions upon manager approval.", "LeaveService:");
$b->addBullet("PayrollService executes atomic payroll runs, computes itemized allowances and statutory tax deductions, and compiles individual employee payslips.", "PayrollService:");
$b->addBullet("AuditLogService intercepts model lifecycle events and records immutable audit log entries with pre- and post-mutation JSON state snapshots.", "AuditLogService:");

$b->addHeading2("10.2 System Walkthrough and Screen Illustrations");
$b->addParagraph("The visual implementation and user interface flows of Smart HR Connect are demonstrated across Figures 6.1.1 through 6.1.6.");

$b->addHeading3("10.2.1 System Authentication Interface");
$b->addParagraph("Fig 6.1.1 displays the secure entry portal of Smart HR Connect. Registered enterprise users authenticate utilizing email credentials and password. The interface is styled with clean Bootstrap components, providing instant validation feedback upon credential mismatches.");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/screenshots/hrms-login.png", "Fig 6.1.1: System Authentication and Secure Access Portal Interface");

$b->addHeading3("10.2.2 Enterprise Management Dashboard");
$b->addParagraph("Fig 6.1.2 illustrates the central administrative dashboard. High-level metric cards display total active workforce headcounts, daily present/late attendance metrics, pending leave approvals, and recent payroll batch statuses, accompanied by permission-aware quick action links.");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/screenshots/hrms-dashboard.png", "Fig 6.1.2: Enterprise HRMS Central Management and Operational Dashboard");

$b->addHeading3("10.2.3 Employee Records Administration");
$b->addParagraph("Fig 6.1.3 illustrates the comprehensive employee directory. Administrators inspect workforce demographics, search by employee code or department, and execute onboarding actions with linked system credential creation.");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_screen_employee.png", "Fig 6.1.3: Employee Directory and Personnel Profile Administration Screen");

$b->addHeading3("10.2.4 Daily Attendance Tracking Console");
$b->addParagraph("Fig 6.1.4 displays the attendance console. Employees execute real-time check-ins and check-outs, view daily shift timers, and inspect historical monthly attendance logs.");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_screen_attendance.png", "Fig 6.1.4: Daily Attendance Tracking, Real-Time Clock-In/Out Console");

$b->addHeading3("10.2.5 Leave Request and Approval Interface");
$b->addParagraph("Fig 6.1.5 illustrates the leave management module. Visual cards present available leave quotas, while the approval interface allows supervisors to review applications and execute approval or rejection actions.");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_screen_leave.png", "Fig 6.1.5: Leave Application and Multi-Tier Manager Approval Interface");

$b->addHeading3("10.2.6 Monthly Payroll Run and Itemized Payslip");
$b->addParagraph("Fig 6.1.6 demonstrates the monthly payroll generation interface and itemized digital payslip, detailing basic pay, allowances (HRA, Special), and statutory deductions (PF, PT, TDS) with computed net salary.");
$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_screen_payroll.png", "Fig 6.1.6: Automated Monthly Payroll Run and Itemized Digital Payslip Console");
$b->addPageBreak();
