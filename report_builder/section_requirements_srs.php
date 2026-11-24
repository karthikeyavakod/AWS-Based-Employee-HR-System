<?php
// section_requirements_srs.php - Chapters 7 and 8

// -------------------------------------------------------------
// CHAPTER 7: HARDWARE AND SOFTWARE REQUIREMENTS
// -------------------------------------------------------------
$b->addHeading1("CHAPTER 7: HARDWARE AND SOFTWARE REQUIREMENTS");
$b->addParagraph("The operational reliability, execution speed, and security of the Smart HR Connect platform are governed by the underlying hardware infrastructure and software execution environments across both development and cloud production stages. The specific hardware and software prerequisites are defined below.");

$b->addHeading2("7.1 Hardware Infrastructure Specifications");
$b->addParagraph("The hardware requirements are categorized into three distinct operational domains: Development Engineering Workstations, AWS Cloud Production Server Infrastructure, and End-User Client Devices.");

$b->addBullet("Processor: Intel Core i5 / Core i7 (8th Gen or newer) or AMD Ryzen 5 / 7 multi-core processor (minimum 4 physical cores, 8 threads).", "Development Workstation CPU:");
$b->addBullet("Memory (RAM): 16 GB DDR4/DDR5 high-speed RAM (minimum 8 GB) to comfortably run localized Apache/MySQL servers, IDEs, and asset compilers simultaneously.", "Development Workstation RAM:");
$b->addBullet("Storage Drive: 512 GB Solid State Drive (NVMe SSD recommended) with at least 50 GB free disk space for local source code, database dumps, and package dependencies.", "Development Workstation Storage:");
$b->addBullet("Network Connectivity: Standard broadband connection (minimum 10 Mbps) for repository synchronization and cloud package fetching.", "Development Network:");

$b->addBullet("Cloud Instance Type: Amazon Web Services (AWS) EC2 t2.micro or t3.micro instance (eligible for AWS Free Tier).", "Cloud Production Server Instance:");
$b->addBullet("Virtual CPU (vCPU): 1 to 2 vCPUs based on AWS Nitro or Xen hypervisor architecture.", "Cloud Server vCPU:");
$b->addBullet("Server Memory (RAM): 1 GB to 2 GB dedicated RAM with configured Linux swap space for peak compilation workloads.", "Cloud Server Memory:");
$b->addBullet("Persistent Storage: 20 GB to 30 GB Amazon Elastic Block Store (EBS) General Purpose SSD (gp3) volume with 3,000 baseline IOPS.", "Cloud Server Storage (EBS):");
$b->addBullet("Object Storage: Amazon S3 standard bucket for encrypted payslip archives and employee document uploads.", "Cloud Object Storage (S3):");

$b->addBullet("Device Types: Standard desktop computer, laptop, Chromebook, or tablet.", "End-User Client Hardware:");
$b->addBullet("Client Memory & Display: Minimum 2 GB RAM; 1024x768 minimum display resolution (1920x1080 Full HD recommended).", "Client Display & Memory:");

$b->addHeading2("7.2 Software and Runtime Environment Specifications");
$b->addParagraph("The software environment encompasses the operating systems, compilers, interpreters, database management systems, and web daemons necessary to execute Smart HR Connect seamlessly.");

$b->addBullet("Development OS: Microsoft Windows 10 / Windows 11 (64-bit) with PowerShell 7 / Windows Terminal; or Ubuntu Linux 22.04 LTS.", "Development Operating System:");
$b->addBullet("Production OS: Amazon Linux 2023 (AL2023) 64-bit x86_64, hardened with standard SELinux/firewalld configurations.", "Production Operating System:");
$b->addBullet("Programming Language & Runtime: PHP Version 8.2 or newer (including PHP 8.5 on development XAMPP stacks). Required PHP extensions: PDO, pdo_mysql, pdo_sqlite, mbstring, openssl, xml, ctype, fileinfo, curl, tokenizer, and zip.", "Backend Language Runtime:");
$b->addBullet("Web Daemons: Apache HTTP Server Version 2.4+ with mod_rewrite, mod_ssl, and mod_proxy_fcgi enabled; and PHP-FPM (FastCGI Process Manager).", "Web Server Daemon:");
$b->addBullet("Database Engines: MySQL Server Version 8.0+ / MariaDB 10.4+ in production; SQLite 3.x supported for localized rapid development and automated PHPUnit testing.", "Relational Database Engine:");
$b->addBullet("Package & Asset Managers: Composer 2.7+ for PHP backend dependencies; Node.js Version 18+ and NPM for frontend JavaScript/CSS asset compilation.", "Dependency Management:");
$b->addBullet("PDF Generation Engine: barryvdh/laravel-dompdf (Version 3.1.2) for compiling HTML payslip views and audit reports into downloadable PDF documents.", "PDF Rendering Library:");
$b->addBullet("Client Browsers: Google Chrome 110+, Microsoft Edge 110+, Mozilla Firefox 110+, Apple Safari 16+, or any modern Chromium-compliant browser.", "Web Browser Compatibility:");
$b->addPageBreak();

// -------------------------------------------------------------
// CHAPTER 8: SOFTWARE REQUIREMENTS SPECIFICATION (SRS)
// -------------------------------------------------------------
$b->addHeading1("CHAPTER 8: SOFTWARE REQUIREMENTS SPECIFICATION (SRS)");
$b->addParagraph("The Software Requirements Specification (SRS) establishes the formal functional and non-functional criteria governing the architectural design, algorithmic implementation, and operational validation of Smart HR Connect.");

$b->addHeading2("8.1 Stakeholder Profiles and Role Matrix");
$b->addParagraph("Smart HR Connect supports four primary organizational stakeholder roles, each governed by explicit, non-overlapping permission boundaries:");
$b->addBullet("The supreme system administrator possessing global authority. Super Admins bypass standard route permission gates to oversee tenant configuration, assign administrative roles, manage master department records, inspect system health, and review immutable security audit logs.", "Super Admin (System Governance):");
$b->addBullet("The primary operational administrative actor. HR Managers possess comprehensive permissions to onboard new personnel, manage job designations, oversee daily attendance rosters, approve attendance corrections, allocate leave quotas, review leave requests, execute monthly payroll runs, and export operational reports.", "HR Manager (Operations Lead):");
$b->addBullet("Middle-tier operational supervisor. Department Managers possess visibility into their designated departmental staff, inspect team attendance calendars, review team leave applications, and execute first-tier approvals or rejections with obligatory justification notes.", "Department Manager (Team Supervisor):");
$b->addBullet("The end-user workforce personnel. Employees access the secure Employee Self-Service (ESS) portal to clock in/out daily, request attendance corrections, submit leave applications, monitor remaining leave balances, inspect digital payslips, and update personal account credentials.", "Employee (Workforce Member):");

$b->addHeading2("8.2 Exhaustive Functional Module Breakdown");
$b->addParagraph("Smart HR Connect is architectured across eight discrete, tightly integrated functional modules:");

$b->addHeading3("Module 1: Authentication and Access Control (RBAC)");
$b->addParagraph("Governs user identity verification and privilege boundaries. Features custom session authentication without reliance upon bloat starter kits. Public registration is omitted; user accounts are provisioned exclusively by authorized administrators. Incorporates Bcrypt password hashing, session fixation regeneration, brute-force login throttling, mandatory initial password change flows, and HTTP route permission middleware evaluating module.action permissions.");

$b->addHeading3("Module 2: Organization and Hierarchy Administration");
$b->addParagraph("Manages core corporate structures. Administrators create and maintain Departments (with unique codes and descriptions) and Designations (job titles linked directly to parent departments). Supports the assignment of designated Department Managers to supervise departmental personnel cohorts.");

$b->addHeading3("Module 3: Employee Record Administration");
$b->addParagraph("Serves as the enterprise personnel directory. Captures complete demographic data, including employee codes, full names, contact telephone numbers, dates of birth, dates of joining, gender, departmental assignments, job designations, and employment statuses (Active, Inactive, Terminated). Automatically provisions and links authenticated user credentials to corresponding employee records.");

$b->addHeading3("Module 4: Attendance Management and Time Tracking");
$b->addParagraph("Provides a web-based clock-in and clock-out console. Enforces a single daily check-in validation rule preventing duplicate records on the same calendar date. Automatically calculates total worked duration in minutes upon check-out. Incorporates an Attendance Correction request workflow enabling employees to rectify missed timestamps subject to HR manager approval.");

$b->addHeading3("Module 5: Leave Management and Approval Engine");
$b->addParagraph("Governs workforce time-off requests. Maintains leave types (Casual Leave, Sick Leave, Earned Leave) and tracks dynamic employee leave quotas (default days, used days, remaining balance). Includes date overlap detection to prevent duplicate leave bookings across identical date ranges. Supports multi-level manager review with approved/rejected status state transitions.");

$b->addHeading3("Module 6: Payroll Management and Deterministic Disbursal");
$b->addParagraph("Automates employee monthly remuneration calculations. Formulates salary structures comprising basic salary, house rent allowances (HRA), medical allowances, and special allowances, balanced against statutory deductions including Provident Fund (PF), Professional Tax (PT), and income tax withholding (TDS). Executes atomic payroll runs for designated calendar pay periods, preventing duplicate batch commits and generating itemized digital payslips.");

$b->addHeading3("Module 7: Employee Self-Service (ESS) Portal");
$b->addParagraph("Provides individual employees with a personalized, intuitive workspace. Employees view attendance calendars, submit time-off requests, monitor leave approval statuses, review historical attendance logs, inspect itemized digital payslips, and review company-wide announcements.");

$b->addHeading3("Module 8: Reporting, Search, and Compliance Audit Logs");
$b->addParagraph("Delivers enterprise governance tools. Features read-only, filtered departmental reports spanning workforce demographics, attendance summaries, leave utilization, and payroll disbursements. Maintains an immutable audit trail capturing actor user IDs, affected entity models, mutation events, and timestamps, with administrative export support in CSV and PDF formats.");

$b->addHeading2("8.3 Functional Requirements Specifications (FRS)");
$b->addParagraph("The specific, testable functional requirements of the system are formally enumerated below:");
$b->addBullet("The system shall authenticate registered users using email and password credentials, rejecting inactive accounts and terminating sessions upon logout.", "FRS-01 (Authentication):");
$b->addBullet("The system shall enforce Role-Based Access Control on all protected administrative and employee routes via HTTP middleware.", "FRS-02 (Authorization):");
$b->addBullet("The system shall provide administrative interfaces to create, update, deactivate, and view departments and job designations.", "FRS-03 (Organization Management):");
$b->addBullet("The system shall enable HR Managers to onboard employees, validate unique employee codes, and associate user accounts.", "FRS-04 (Employee Onboarding):");
$b->addBullet("The system shall record daily employee attendance timestamps (check-in and check-out) and compute worked duration.", "FRS-05 (Attendance Recording):");
$b->addBullet("The system shall reject duplicate check-in attempts submitted by the same employee on the same calendar date.", "FRS-06 (Duplicate Attendance Prevention):");
$b->addBullet("The system shall enable employees to submit attendance correction requests with documented justifications.", "FRS-07 (Attendance Correction):");
$b->addBullet("The system shall track leave balances across multiple leave types and decrement available quotas upon leave approval.", "FRS-08 (Leave Entitlement Sync):");
$b->addBullet("The system shall validate leave date ranges and prevent the submission of overlapping leave applications.", "FRS-09 (Leave Collision Prevention):");
$b->addBullet("The system shall allow authorized Department Managers and HR Managers to approve or reject pending leave requests.", "FRS-10 (Leave Review Workflow):");
$b->addBullet("The system shall execute atomic monthly payroll calculations, applying earnings and statutory deductions.", "FRS-11 (Payroll Batch Execution):");
$b->addBullet("The system shall prevent duplicate payroll runs from being committed for the same employee within the same pay period.", "FRS-12 (Duplicate Payroll Prevention):");
$b->addBullet("The system shall generate itemized payslip summaries accessible by the employee via the self-service portal.", "FRS-13 (Payslip Generation):");
$b->addBullet("The system shall provide read-only, date-filtered analytical reports for workforce, attendance, leave, and payroll metrics.", "FRS-14 (Management Reporting):");
$b->addBullet("The system shall record sensitive administrative mutations in an immutable audit table and support CSV/PDF export.", "FRS-15 (Compliance Audit Trails):");

$b->addHeading2("8.4 Non-Functional Requirements and Software Quality Attributes");
$b->addParagraph("Non-functional requirements specify the operational quality criteria, performance benchmarks, and security constraints governing Smart HR Connect:");

$b->addBullet("Page rendering and server response latency shall not exceed 500 milliseconds under standard concurrency. Database queries shall execute within 50 milliseconds via optimized indexing.", "Performance Benchmark:");
$b->addBullet("The system shall support automated daily database dumps to Amazon S3 and enforce transactional database rollback upon runtime exceptions to prevent partial writes.", "Safety & Disaster Recovery:");
$b->addBullet("Passwords must be encrypted using Bcrypt with a work factor of 12. All forms must enforce CSRF token validation. All user inputs must be sanitized against SQL Injection (via Eloquent parameterization) and XSS (via Blade entity escaping).", "Security & Cryptographic Standards:");
$b->addBullet("The cloud-hosted application on AWS EC2 shall maintain an operational service availability of 99.9% uptime during business hours.", "Availability Attribute:");
$b->addBullet("The system shall reject malformed input payloads gracefully, displaying clear validation messages without exposing server stack traces or database schema details.", "Reliability & Error Handling:");
$b->addBullet("The user interface shall adhere to responsive design principles, rendering consistently across desktop monitors, laptops, and tablets.", "Usability & Accessibility:");
$b->addBullet("The codebase shall adhere to strict PSR-12 coding standards with decoupled Service classes, facilitating effortless feature extensions and maintenance.", "Maintainability & Code Hygiene:");
$b->addBullet("The application shall run seamlessly on standard LAMP/LEMP stacks across both Windows (development) and Amazon Linux (cloud production) without source modification.", "Portability Attribute:");
$b->addPageBreak();
