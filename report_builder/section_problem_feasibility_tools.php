<?php
// section_problem_feasibility_tools.php - Chapters 4, 5, and 6

// -------------------------------------------------------------
// CHAPTER 4: PROBLEM STATEMENT
// -------------------------------------------------------------
$b->addHeading1("CHAPTER 4: PROBLEM STATEMENT");

$b->addHeading2("4.1 Analysis of the Existing System and Operational Bottlenecks");
$b->addParagraph("In numerous corporate organizations, human resource workflows remain dependent upon legacy practices characterized by decentralized electronic spreadsheets (e.g., Microsoft Excel or Google Sheets), localized desktop databases, paper attendance registers, and ad-hoc email communications. While these conventional methods served early business operations, they become critically unmanageable as organizational headcounts expand, departments diversify, and regulatory compliance mandates intensify.");
$b->addParagraph("Under the legacy operational model, when an employee reports for duty, attendance is recorded manually on a physical ledger or typed into a department spreadsheet. At the conclusion of each month, administrative personnel must manually reconcile these records, calculate total hours worked, identify late check-ins, deduct approved leaves, compute overtime, and cross-reference leave slips submitted via disparate email chains. Subsequently, payroll clerks manually calculate gross salary, apply tax deductions, and formulate individual salary slips.");
$b->addParagraph("Disadvantages of the Existing System:");
$b->addBullet("High Vulnerability to Computational and Human Errors: Manual calculation of complex salary structures, variable allowances, and statutory tax slabs frequently results in mathematical discrepancies, leading to erroneous salary disbursements and subsequent employee disputes.", "Human Error Susceptibility:");
$b->addBullet("Uncontrolled Time Theft and Attendance Inaccuracies: Physical registers and unvalidated digital spreadsheets enable \"buddy punching\" and retroactive record falsification. There is no automated mechanism to prevent multiple conflicting check-ins or compute worked duration programmatically.", "Attendance Fabrication:");
$b->addBullet("Severe Administrative Latency in Leave Approvals: Leave applications submitted via paper forms or loose emails frequently languish without timely manager review. Employees possess no real-time visibility into their remaining leave entitlements, resulting in repeated administrative inquiries.", "Operational Latency:");
$b->addBullet("Inadequate Access Control and Data Privacy Risks: Spreadsheets stored on local network drives lack fine-grained, cell-level or role-level access controls. Unauthorized personnel can view confidential executive remuneration details, violating employee data privacy rights.", "Data Security Vulnerabilities:");
$b->addBullet("Absence of Verifiable Audit Trails: When discrepancies arise regarding payroll modifications, employee promotions, or leave balance adjustments, legacy systems provide no tamper-evident audit logs indicating which user executed the change, when it occurred, or what previous state was altered.", "Audit Trail Deficiencies:");
$b->addBullet("Operational Inefficiencies and High Administrative Overhead: HR personnel spend an estimated 35% to 45% of their working hours on routine administrative reconciliation rather than strategic human capital development.", "Excessive Overhead Costs:");

$b->addHeading2("4.2 The Proposed Smart HR Connect Enterprise System and Advantages");
$b->addParagraph("To comprehensively eliminate the vulnerabilities and bottlenecks of legacy workflows, this dissertation presents Smart HR Connect, an integrated, cloud-native Human Resource Management System engineered on modern PHP 8.2+ and the Laravel 12 framework. Smart HR Connect establishes an authoritative, centralized relational database architecture hosted elastically on Amazon Web Services (AWS), enforcing a rigorous Role-Based Access Control (RBAC) security paradigm.");
$b->addParagraph("The proposed system automates every phase of the employee lifecycle through dedicated, decoupled service layers. Employees interact with a self-service dashboard to check in/out, monitor daily attendance, submit leave applications, and view digital itemized payslips. Department Managers evaluate leave requests for their respective teams through real-time review interfaces. HR Executives oversee organizational onboarding, department designations, attendance reconciliation, and execute deterministic monthly payroll runs with a single command. Super Administrators govern system health, user roles, permission matrices, and inspect immutable audit logs.");
$b->addParagraph("Key Advantages of the Proposed System:");
$b->addBullet("Fine-Grained Role-Based Access Control (RBAC): User permissions are enforced at the HTTP middleware boundary using a module.action convention. Super Admin, HR Manager, Department Manager, and Employee tiers ensure absolute privilege segregation, preventing horizontal and vertical access violations.", "Granular Security Governance:");
$b->addBullet("Real-Time Automated Attendance Tracking: Incorporates programmatic check-in and check-out validation with automated duration calculation. Duplicate same-day check-in attempts are blocked at the transactional database level.", "Automated Time-Tracking Integrity:");
$b->addBullet("Streamlined Leave Management Engine: Provides self-service leave requests with automated date overlap detection, real-time quota deduction upon approval, and digital notification of approval/rejection decisions.", "Dynamic Leave Workflow:");
$b->addBullet("Deterministic Itemized Payroll Engine: Computes basic pay, allowances (HRA, DA, Medical), and statutory deductions (Provident Fund, Professional Tax, TDS) with absolute mathematical precision. Prevents duplicate pay runs for the same period.", "Accurate Payroll Disbursal:");
$b->addBullet("Employee Self-Service (ESS) Optimization: Reduces repetitive administrative overhead by granting employees continuous, transparent access to their personal attendance calendars, leave balances, and downloadable payslips.", "Operational Self-Sufficiency:");
$b->addBullet("Immutable Enterprise Audit Logging: Automatically records all sensitive system mutations (user updates, role changes, payroll executions, login/logout events) with actor IDs and timestamp metadata, exportable in CSV and PDF formats.", "Regulatory Compliance Assurance:");
$b->addBullet("Cloud Scalability and Cost Efficiency: Deployed on Amazon Linux 2023 on Amazon EC2 with Apache/PHP-FPM and private Amazon S3 object storage, delivering high availability and rapid response times within AWS Free Tier expenditure thresholds.", "Elastic Cloud Architecture:");
$b->addPageBreak();

// -------------------------------------------------------------
// CHAPTER 5: FEASIBILITY STUDY
// -------------------------------------------------------------
$b->addHeading1("CHAPTER 5: FEASIBILITY STUDY");
$b->addParagraph("Prior to embarking upon full-scale software architecture and engineering, a comprehensive feasibility study was executed to evaluate the operational viability, economic prudence, technical practicality, and legal-social acceptance of the proposed Smart HR Connect platform. The findings across all dimensions are detailed below.");

$b->addHeading2("5.1 Economical Feasibility and Cost-Benefit Matrix");
$b->addParagraph("The economic feasibility analysis assesses whether the financial investment required to develop, deploy, and maintain Smart HR Connect is justified by the tangible and intangible cost savings, efficiency gains, and risk reductions realized by the organization.");
$b->addBullet("Software Licensing Costs: The entire core technology stack—including PHP 8.2+, the Laravel 12 framework, MySQL 8.0 / MariaDB, the Apache HTTP Server, Bootstrap 5.3, Vite 6, and Amazon Linux 2023—is open-source and free of commercial licensing fees. This eliminates recurring per-seat software licensing expenditures.", "Zero Software Licensing Fees:");
$b->addBullet("Cloud Hosting Expenditure: By optimizing the platform architecture to execute efficiently on an Amazon EC2 t2.micro / t3.micro instance with a 30 GB EBS volume and Amazon S3 private storage, the initial deployment operates entirely within the AWS Free Tier allowances for the first 12 months. Subsequent operational expenditures (OpEx) for commercial production scaling are estimated at a modest $15 to $35 USD per month.", "Optimized Infrastructure OpEx:");
$b->addBullet("Labor Cost Savings and Return on Investment (ROI): Automating attendance reconciliation, leave approvals, and payroll computation eliminates an estimated 40 to 60 administrative hours per month for HR personnel. The resulting labor reallocation to core strategic business initiatives yields a projected Return on Investment (ROI) of over 280% within the first year of enterprise adoption.", "High Operational ROI:");

$b->addHeading2("5.2 Technical Feasibility and Cloud Stack Analysis");
$b->addParagraph("The technical feasibility study evaluates whether the required system functionalities can be reliably implemented utilizing currently available software frameworks, hardware resources, and engineering competencies.");
$b->addBullet("Framework Stability and Architectural Maturity: PHP 8.2+ and Laravel 12 represent an exceptionally mature, well-documented enterprise ecosystem equipped with robust object-relational mapping (Eloquent ORM), automated schema migrations, standardized Form Request validation, and comprehensive security middleware.", "Maturity of Technology Stack:");
$b->addBullet("Hardware and Runtime Compatibility: The system requires minimal server resources to deliver exceptional throughput. On an Amazon Linux 2023 EC2 instance with 1 vCPU and 1 GB of RAM, Apache HTTP Server with PHP-FPM FastCGI and OPcache comfortably handles hundreds of concurrent user requests with sub-50ms latency.", "Low Hardware Footprint:");
$b->addBullet("Client Environment Accessibility: The user interface is engineered with responsive HTML5, CSS3, Bootstrap 5.3, and modern JavaScript. Consequently, end-users require only a standard web browser (Chrome, Edge, Firefox, Safari) on any desktop, laptop, or tablet without needing localized client installation.", "Universal Client Accessibility:");
$b->addBullet("Extensibility and Database Scalability: The relational database schema is designed with strict third normal form (3NF) principles and indexed foreign keys. It supports seamless transitions from localized development databases to Amazon RDS (Relational Database Service) MySQL clusters as enterprise data volumes scale.", "Database Scalability:");

$b->addHeading2("5.3 Social, Legal, and Operational Feasibility");
$b->addParagraph("Social and operational feasibility evaluates the degree to which the software aligns with user habits, organizational workflows, labor regulations, and data privacy legal mandates.");
$b->addBullet("User Ergonomics and Operational Adoption: The user interface features an intuitive, clean, and accessible design adhering to modern ergonomic standards. Employee self-service functions (clocking in, requesting leave, viewing payslips) require no technical training, ensuring rapid, friction-free employee adoption.", "Ergonomic User Interface:");
$b->addBullet("Regulatory and Legal Compliance: Smart HR Connect incorporates statutory payroll formulas conforming to statutory labor standards, including Provident Fund (PF) ceilings, Professional Tax brackets, and progressive Tax Deducted at Source (TDS) schedules. Digital payslips serve as legally recognized remuneration documentation.", "Labor Law Compliance:");
$b->addBullet("Data Privacy and Security Governance: The system enforces rigorous compliance with international data protection principles (such as GDPR and the Indian Digital Personal Data Protection Act). Employee demographic data, medical leave reasons, and salary records are strictly restricted to authorized HR executives and the individual employee, eliminating unauthorized workplace disclosure.", "Strict Data Privacy:");
$b->addPageBreak();

// -------------------------------------------------------------
// CHAPTER 6: TOOLS AND TECHNOLOGIES USED
// -------------------------------------------------------------
$b->addHeading1("CHAPTER 6: TOOLS AND TECHNOLOGIES USED");
$b->addParagraph("The engineering architecture of Smart HR Connect relies upon a modern, robust, and industry-standard technology stack spanning backend runtimes, web frameworks, relational databases, cloud hosting platforms, frontend libraries, and network protocols. The technical characteristics of each component are detailed below.");

$b->addHeading2("6.1 Modern PHP 8.2+ and Laravel 12 Enterprise Architecture");
$b->addParagraph("Smart HR Connect is developed on PHP 8.2+, an advanced, high-performance object-oriented programming language. PHP 8.2 introduces revolutionary performance optimizations, including the Just-In-Time (JIT) compiler, typed class constants, readonly classes, disjunctive normal form (DNF) types, nullsafe operators, and comprehensive memory management enhancements.");
$b->addParagraph("The application leverages Laravel 12, the flagship enterprise Model-View-Controller (MVC) framework for PHP. Laravel provides a sophisticated architectural foundation characterized by:");
$b->addBullet("Decouples request orchestration in Controllers from domain business logic residing in reusable Service classes (such as AttendanceService, LeaveService, and PayrollService).", "Layered Service-Controller Architecture:");
$b->addBullet("Provides an expressive ActiveRecord Object-Relational Mapper that enforces data integrity, encapsulates model relationships (one-to-one, one-to-many, many-to-many pivot tables), and protects against SQL injection vulnerabilities via automatic query parameterization.", "Eloquent ORM:");
$b->addBullet("Lightweight, high-speed templating engine that compiles views into optimized native PHP bytecode, supporting reusable component hierarchies, layout inheritance, and automatic HTML entity escaping for Cross-Site Scripting (XSS) defense.", "Blade Templating Engine:");
$b->addBullet("Encapsulates incoming HTTP request data validation and authorization logic into dedicated Form Request classes before reaching controller actions, ensuring invalid payloads are rejected at the application boundary.", "Form Request Validation:");
$b->addBullet("Intercepts incoming HTTP requests to enforce session authentication, CSRF token verification, active-account status filters, and custom Role-Based Access Control (RBAC) permission gates.", "HTTP Route Middleware:");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_laravel_arch.png", "Fig 3.3.1: Laravel 12 MVC & HTTP Request Lifecycle Architecture");

$b->addHeading2("6.2 MySQL 8.0 / MariaDB Relational Database Engine");
$b->addParagraph("The persistence tier of Smart HR Connect is powered by MySQL 8.0 / MariaDB, an enterprise-class, ACID-compliant relational database management system (RDBMS). Key relational capabilities utilized include:");
$b->addBullet("Guarantees that multi-step operations—such as approving a leave request and simultaneously decrementing employee leave quotas, or generating a payroll run and inserting individual itemized payslips—either commit completely or roll back entirely upon failure, preventing data corruption.", "ACID Transactional Integrity:");
$b->addBullet("All transactional tables (employees, attendances, leave_requests, payslips) enforce strict referential integrity through foreign key constraints, cascading updates, and restricted deletions.", "Foreign Key Referential Integrity:");
$b->addBullet("High-frequency query columns (such as employee_id, attendance_date, and pay_period) are optimized using B-Tree indexing, ensuring sub-10ms query execution across large dataset volumes.", "B-Tree Query Indexing:");

$b->addHeading2("6.3 Apache HTTP Server, FastCGI, and PHP-FPM Web Architecture");
$b->addParagraph("In production deployment, HTTP/HTTPS web requests are processed by the Apache HTTP Server (Version 2.4+) operating in conjunction with PHP-FPM (FastCGI Process Manager). Apache serves as the reverse proxy and front-facing web daemon, managing incoming network sockets, enforcing TLS/SSL cryptographic handshakes, and executing URL rewrites via mod_rewrite to route all web traffic through Laravel's public/index.php front controller.");
$b->addParagraph("PHP-FPM manages a dynamic pool of worker processes dedicated to executing compiled PHP opcodes. This decoupled architecture isolates web server execution from PHP processing, maximizing concurrent request throughput and preventing memory leaks.");

$b->addHeading2("6.4 Amazon Web Services (AWS) Cloud Ecosystem");
$b->addParagraph("Smart HR Connect is architectured for cloud deployment on Amazon Web Services (AWS), providing high availability, elastic scalability, and infrastructure security. The cloud architecture comprises:");
$b->addBullet("Hosts the web application runtime, Apache web daemon, and PHP-FPM processes on an Amazon Linux 2023 EC2 virtual machine, delivering high computational performance with minimal operating system overhead.", "Amazon EC2 (Elastic Compute Cloud):");
$b->addBullet("Serves as a private, highly durable object repository for archiving employee digital documentation and generated payslip files. Configured with \"Block All Public Access\" and encrypted at rest.", "Amazon S3 (Simple Storage Service):");
$b->addBullet("Supplies temporary, rotating security credentials to the EC2 instance via an attached instance profile (SmartHrEc2S3Role), eliminating the dangerous practice of hardcoding static AWS access keys within application source code.", "AWS IAM (Identity & Access Management):");
$b->addBullet("Enforces a stateful virtual firewall controlling network ingress and egress. Restricts inbound traffic strictly to HTTP (Port 80), HTTPS (Port 443), and administrative SSH (Port 22 restricted to authorized admin IPs), while completely blocking external internet access to internal database ports.", "AWS Security Groups & VPC:");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_cloud_arch.png", "Fig 4.1.1: AWS Cloud Deployment Architecture (Smart HR Connect on EC2 & S3)");

$b->addHeading2("6.5 Responsive Frontend Stack (Bootstrap 5.3, Vite 6, Tailwind CSS)");
$b->addParagraph("The presentation tier of Smart HR Connect combines modern CSS frameworks to deliver an enterprise-grade, accessible, and responsive user experience:");
$b->addBullet("Provides the foundational responsive grid, layout utilities, modals, dropdowns, navigation sidebars, and styled form controls, accompanied by Bootstrap Icons for visual affordances.", "Bootstrap 5.3.3 & Icons:");
$b->addBullet("High-speed frontend build tool that pre-compiles and bundles JavaScript and CSS assets, optimizing load speeds and asset caching.", "Vite 6 Asset Bundler:");
$b->addBullet("Utilized for rapid styling of specialized cards, status badges, and dynamic KPI metric indicators across administrative and employee dashboards.", "Tailwind CSS 4 Utility Engine:");

$b->addHeading2("6.6 Network Communication Stack and Client-Server Protocols");
$b->addParagraph("Smart HR Connect operates across the standard four-layer TCP/IP protocol suite and maps directly to the seven-layer OSI reference model. The complete communication pipeline is illustrated in Fig 3.3.4 and detailed as follows:");
$b->addBullet("Client browsers initiate secure HTTP/HTTPS requests utilizing TLS 1.3 encryption. Data payloads are serialized via Form URL-encoding or JSON.", "Application Layer (OSI Layers 5-7):");
$b->addBullet("Transmission Control Protocol (TCP) establishes reliable, connection-oriented virtual circuits between the client browser and the AWS EC2 instance via a 3-way handshake (SYN, SYN-ACK, ACK), ensuring packet sequencing and error correction.", "Transport Layer (OSI Layer 4):");
$b->addBullet("Internet Protocol (IPv4/IPv6) encapsulates TCP segments into datagrams, managing packet routing across intermediate internet gateways to the Amazon Web Services Virtual Private Cloud (VPC).", "Internet Layer (OSI Layer 3):");
$b->addBullet("Governs physical and virtual data link framing across Ethernet interfaces and AWS Nitro hypervisor virtualized network adapters.", "Network Access Layer (OSI Layers 1-2):");

$b->addImage("c:/xampp/htdocs/AWS-Based-Employee-HR-System-main/docs/diagrams/fig_tcp_ip.png", "Fig 3.3.4: Network Protocol Architecture (Smart HR Connect)");
$b->addPageBreak();
