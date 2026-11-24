<?php
// section_frontmatter.php - Title Page, Certificate, Declaration, Acknowledgement, TOC, Figures, Tables, Abstract

// -------------------------------------------------------------
// TITLE PAGE
// -------------------------------------------------------------
$b->addTitle("SMART HR CONNECT");
$b->addSubtitle("AN ENTERPRISE-GRADE CLOUD-BASED EMPLOYEE MANAGEMENT AND HUMAN RESOURCES INFORMATION SYSTEM");

$b->addParagraph("A Project Report Submitted in Partial Fulfillment of the Requirements for the Award of the Degree of", "center", false, true, 180);
$b->addParagraph("MASTER OF COMPUTER APPLICATIONS", "center", true, false, 60);
$b->addParagraph("IN", "center", false, false, 60);
$b->addParagraph("COMPUTER SCIENCE & INFORMATION TECHNOLOGY", "center", true, false, 360);

$b->addParagraph("Submitted by:", "center", true, false, 60);
$b->addParagraph("SOMASHEKAR N S", "center", true, false, 40);
$b->addParagraph("(University Seat Number: 1CE24MC045)", "center", false, true, 40);
$b->addParagraph("Department of Master of Computer Applications", "center", false, false, 40);
$b->addParagraph("City Engineering College, Bengaluru – 560061", "center", false, false, 300);

$b->addParagraph("Under the Esteemed Guidance of:", "center", true, false, 60);
$b->addTable(
    ["Internal Guide", "External Project Mentor"],
    [
        ["Dr. H. R. Ramesh, M.Tech, Ph.D.", "Mr. Raghavendra G."],
        ["Professor & Head of Department", "Chief Executive Officer & Founder"],
        ["Department of MCA", "Web blitz Software Pvt. Ltd."],
        ["City Engineering College, Bengaluru", "Bengaluru, Karnataka, India"]
    ],
    null,
    [4680, 4680]
);

$b->addParagraph("ACADEMIC YEAR 2025 – 2026", "center", true, false, 180);
$b->addPageBreak();

// -------------------------------------------------------------
// CERTIFICATE OF ORIGINALITY & BONAFIDE WORK
// -------------------------------------------------------------
$b->addHeading1("CERTIFICATE OF BONAFIDE WORK");
$b->addParagraph("This is to formally certify that the technical project report entitled \"SMART HR CONNECT: AN ENTERPRISE-GRADE CLOUD-BASED EMPLOYEE MANAGEMENT AND HUMAN RESOURCES INFORMATION SYSTEM\" is a bonafide record of independent project engineering work carried out by SOMASHEKAR N S (USN: 1CE24MC045), a student of the Department of Master of Computer Applications, City Engineering College, Bengaluru, affiliated with Visvesvaraya Technological University (VTU), Belagavi, in partial fulfillment of the requirements for the award of the post-graduate degree of Master of Computer Applications during the academic curriculum year 2025–2026.");
$b->addParagraph("It is further certified that the conceptual designs, architectural blueprints, software source code, algorithmic formulations, relational database schemas, empirical feature validations, and documentation presented in this dissertation represent original work. The investigations embodied in this project report have not been submitted, in whole or in part, to any other institution, university, or examination board for the award of any degree, diploma, or academic fellowship.");
$b->addParagraph("The project has been reviewed, examined, and approved for submission following rigorous internal assessment and industrial alignment benchmarks with our collaborative enterprise partner, Web blitz Software Pvt. Ltd., Bengaluru.");

$b->addTable(
    ["Internal Project Guide", "Head of the Department", "Principal / Dean"],
    [
        ["\n\n___________________________\nDr. H. R. Ramesh\nProfessor, Dept. of MCA\nCity Engineering College",
         "\n\n___________________________\nDr. S. K. Nandeesh\nProfessor & HOD, Dept. of MCA\nCity Engineering College",
         "\n\n___________________________\nDr. K. S. Sridhar\nPrincipal, City Engineering College\nBengaluru – 560061"]
    ],
    null,
    [3120, 3120, 3120]
);
$b->addPageBreak();

// -------------------------------------------------------------
// DECLARATION
// -------------------------------------------------------------
$b->addHeading1("DECLARATION");
$b->addParagraph("I, SOMASHEKAR N S, hereby declare that the technical dissertation entitled \"SMART HR CONNECT: AN ENTERPRISE-GRADE CLOUD-BASED EMPLOYEE MANAGEMENT AND HUMAN RESOURCES INFORMATION SYSTEM\" submitted to City Engineering College, Bengaluru, in partial fulfillment of the academic requirements for the degree of Master of Computer Applications, is an authentic record of the genuine project work completed by me under the supervisory guidance of Dr. H. R. Ramesh, Professor, Department of MCA, and industrial direction provided by Web blitz Software Pvt. Ltd., Bengaluru.");
$b->addParagraph("I solemnly confirm that the material presented in this technical report reflects original engineering efforts and has not previously formed the basis for the award of any academic degree, diploma, associate fellowship, or other similar recognition in this or any other academic institution. All external citations, intellectual algorithms, software libraries, and research papers referenced throughout this document have been explicitly credited according to standard IEEE academic attribution protocols.");
$b->addParagraph("I further declare that the content of this dissertation has been rigorously scrutinized for plagiarism and complies fully with institutional academic integrity mandates, maintaining a similarity index well below the strict threshold of ten percent (10%).");

$b->addParagraph("Date: 23rd November 2026\nPlace: Bengaluru", "left", false, false, 180);
$b->addParagraph("SOMASHEKAR N S\n(USN: 1CE24MC045)", "right", true, false, 180);
$b->addPageBreak();

// -------------------------------------------------------------
// ACKNOWLEDGEMENT
// -------------------------------------------------------------
$b->addHeading1("ACKNOWLEDGEMENT");
$b->addParagraph("The successful realization and comprehensive documentation of this enterprise software project would not have been possible without the invaluable guidance, constructive critique, and continuous encouragement bestowed by numerous individuals and organizational mentors.");
$b->addParagraph("First and foremost, I express my deepest sense of gratitude and profound respect to my internal project guide, Dr. H. R. Ramesh, Professor, Department of MCA, City Engineering College, Bengaluru, for his constant academic guidance, meticulous supervision, insightful technical recommendations, and untiring support throughout the conceptualization, system modeling, and implementation phases of this cloud-based HRMS system.");
$b->addParagraph("I extend my heartfelt thanks to Dr. S. K. Nandeesh, Head of the Department of Master of Computer Applications, for his visionary leadership, academic facilitation, and for fostering an environment conducive to high-caliber research and software engineering excellence.");
$b->addParagraph("I express my sincere indebtedness to Dr. K. S. Sridhar, Principal, City Engineering College, for providing state-of-the-art computational infrastructure, network laboratory facilities, and institutional support necessary for the seamless execution of this enterprise initiative.");
$b->addParagraph("I am deeply privileged to acknowledge the exceptional industrial mentorship and operational insights provided by Mr. Raghavendra G., Chief Executive Officer and Founder of Web blitz Software Pvt. Ltd., Bengaluru. His practical industry perspectives on corporate workforce management, real-world payroll compliance paradigms, and cloud infrastructure scalability were instrumental in shaping Smart HR Connect into a commercial-grade enterprise solution.");
$b->addParagraph("Lastly, I am forever indebted to my beloved parents, family members, and peers for their unconditional affection, patience, moral fortitude, and persistent encouragement throughout the tenure of my academic journey.");
$b->addParagraph("SOMASHEKAR N S", "right", true, false, 180);
$b->addPageBreak();

// -------------------------------------------------------------
// TABLE OF CONTENTS
// -------------------------------------------------------------
$b->addHeading1("TABLE OF CONTENTS");
$tocRows = [
    ["1.0", "ABSTRACT & EXECUTIVE SUMMARY", "1"],
    ["2.0", "CHAPTER 1: INTRODUCTION", "2"],
    ["", "    1.1 Project Overview and Industrial Context", "2"],
    ["", "    1.2 Project Objectives and Strategic Motivations", "4"],
    ["", "    1.3 Project Scope and Boundaries", "5"],
    ["3.0", "CHAPTER 2: COMPANY PROFILE", "7"],
    ["", "    2.1 About Web blitz Software Pvt. Ltd.", "7"],
    ["", "    2.2 Organizational Mission and Core Values", "8"],
    ["", "    2.3 Strategic Vision and Long-Term Roadmap", "9"],
    ["4.0", "CHAPTER 3: LITERATURE SURVEY", "10"],
    ["", "    3.1 Review of State-of-the-Art Research Papers (Papers 1 to 8)", "10"],
    ["5.0", "CHAPTER 4: PROBLEM STATEMENT", "16"],
    ["", "    4.1 Analysis of the Existing System and Operational Bottlenecks", "16"],
    ["", "    4.2 The Proposed Smart HR Connect Enterprise System and Advantages", "18"],
    ["6.0", "CHAPTER 5: FEASIBILITY STUDY", "21"],
    ["", "    5.1 Economical Feasibility and Cost-Benefit Matrix", "21"],
    ["", "    5.2 Technical Feasibility and Cloud Stack Analysis", "23"],
    ["", "    5.3 Social, Legal, and Operational Feasibility", "25"],
    ["7.0", "CHAPTER 6: TOOLS AND TECHNOLOGIES USED", "27"],
    ["", "    6.1 Modern PHP 8.2+ and Laravel 12 Enterprise Architecture", "27"],
    ["", "    6.2 MySQL 8.0 / MariaDB Relational Database Engine", "30"],
    ["", "    6.3 Apache HTTP Server, FastCGI, and PHP-FPM Web Architecture", "32"],
    ["", "    6.4 Amazon Web Services (AWS) Cloud Ecosystem (EC2, S3, IAM, VPC)", "34"],
    ["", "    6.5 Responsive Frontend Stack (Bootstrap 5.3, Vite 6, Tailwind CSS)", "37"],
    ["", "    6.6 Network Communication Stack and Client-Server Protocols", "39"],
    ["8.0", "CHAPTER 7: HARDWARE AND SOFTWARE REQUIREMENTS", "42"],
    ["", "    7.1 Hardware Infrastructure Specifications", "42"],
    ["", "    7.2 Software and Runtime Environment Specifications", "43"],
    ["9.0", "CHAPTER 8: SOFTWARE REQUIREMENTS SPECIFICATION (SRS)", "45"],
    ["", "    8.1 Stakeholder Profiles and Role Matrix", "45"],
    ["", "    8.2 Exhaustive Functional Module Breakdown", "46"],
    ["", "    8.3 Functional Requirements Specifications (FRS-01 to FRS-15)", "51"],
    ["", "    8.4 Non-Functional Requirements and Software Quality Attributes", "54"],
    ["10.0", "CHAPTER 9: SYSTEM DESIGN AND MODELING", "58"],
    ["", "    9.1 System Architecture Perspective", "58"],
    ["", "    9.2 Data Flow Diagrams (DFD Level 0 Context and Level 1)", "60"],
    ["", "    9.3 Unified Modeling Language (UML) Diagrams", "62"],
    ["", "        9.3.1 UML Use Case Diagram", "62"],
    ["", "        9.3.2 UML Sequence Diagram (Leave Lifecycle)", "65"],
    ["", "        9.3.3 UML Collaboration / Component Diagram", "67"],
    ["", "        9.3.4 Activity Diagrams & Flow Charts (Employee, HR, Dept, Admin)", "69"],
    ["", "    9.4 Database Design and Entity Relationship Diagram (ERD)", "75"],
    ["", "    9.5 Data Dictionary and Schema Specifications", "77"],
    ["11.0", "CHAPTER 10: SYSTEM IMPLEMENTATION", "83"],
    ["", "    10.1 Layered Architecture and Design Patterns", "83"],
    ["", "    10.2 System Walkthrough and Screen Illustrations (Figures 6.1.1 to 6.1.6)", "85"],
    ["12.0", "CHAPTER 11: SOFTWARE TESTING AND QUALITY ASSURANCE", "92"],
    ["", "    11.1 Testing Methodologies and Quality Framework", "92"],
    ["", "    11.2 Input Validation and Security Boundary Checking", "94"],
    ["", "    11.3 Arrangement and Partitioning of Test Data", "96"],
    ["", "    11.4 Comprehensive Test Cases and Automated Results (Tables 7.1 to 7.6)", "98"],
    ["13.0", "CHAPTER 12: END USER TRAINING", "106"],
    ["14.0", "CHAPTER 13: MAINTENANCE AND SYSTEM EVOLUTION", "108"],
    ["15.0", "CHAPTER 14: CONCLUSION", "110"],
    ["16.0", "CHAPTER 15: FUTURE ENHANCEMENTS", "112"],
    ["17.0", "CHAPTER 16: REFERENCES (IEEE STANDARD)", "114"]
];
$b->addTable(["Section", "Topic Description", "Page"], $tocRows, null, [1200, 7160, 1000]);
$b->addPageBreak();

// -------------------------------------------------------------
// LIST OF FIGURES
// -------------------------------------------------------------
$b->addHeading1("LIST OF FIGURES");
$figRows = [
    ["Fig 3.3.1", "Laravel 12 MVC & HTTP Request Lifecycle Architecture", "29"],
    ["Fig 4.1.1", "AWS Cloud Deployment Architecture (Smart HR Connect on EC2 & S3)", "35"],
    ["Fig 3.3.4", "Network Protocol Architecture (TCP/IP vs OSI Stack in Cloud HRMS)", "40"],
    ["Fig 5.2.1", "Context Diagram / Data Flow Diagram (DFD Level 0 & Level 1)", "61"],
    ["Fig 5.3.1", "UML Use Case Diagram (Super Admin, HR, Department Manager, Employee)", "63"],
    ["Fig 5.4.1", "UML Sequence Diagram: Leave Request & Approval Lifecycle Workflow", "66"],
    ["Fig 5.5.1", "Collaboration & Component Architecture Diagram", "68"],
    ["Fig 5.6.1", "Flow Chart / Activity Diagram: Employee Self-Service Workflow", "70"],
    ["Fig 5.6.2", "Flow Chart / Activity Diagram: HR Administrator Operational Workflow", "71"],
    ["Fig 5.6.3", "Flow Chart / Activity Diagram: Department Manager Approval Workflow", "73"],
    ["Fig 5.6.4", "Flow Chart / Activity Diagram: Super Admin Governance & RBAC Workflow", "74"],
    ["Fig 5.7.1", "Entity Relationship Diagram (ERD) - Relational Schema Architecture", "76"],
    ["Fig 6.1.1", "System Authentication and Secure Access Portal Interface", "86"],
    ["Fig 6.1.2", "Enterprise HRMS Central Management and Operational Dashboard", "87"],
    ["Fig 6.1.3", "Employee Directory and Personnel Profile Administration Screen", "88"],
    ["Fig 6.1.4", "Daily Attendance Tracking, Real-Time Clock-In/Out Console", "89"],
    ["Fig 6.1.5", "Leave Application and Multi-Tier Manager Approval Interface", "90"],
    ["Fig 6.1.6", "Automated Monthly Payroll Run and Itemized Digital Payslip Console", "91"]
];
$b->addTable(["Figure No.", "Figure Caption / Title", "Page"], $figRows, null, [1500, 6860, 1000]);
$b->addPageBreak();

// -------------------------------------------------------------
// LIST OF TABLES
// -------------------------------------------------------------
$b->addHeading1("LIST OF TABLES");
$tblListRows = [
    ["Table 5.1", "Database Schema: users (Authentication Identity Table)", "77"],
    ["Table 5.2", "Database Schema: roles and permissions (RBAC Foundation Tables)", "78"],
    ["Table 5.3", "Database Schema: departments and designations (Org Hierarchy Tables)", "79"],
    ["Table 5.4", "Database Schema: employees (Personnel Record Table)", "79"],
    ["Table 5.5", "Database Schema: attendances (Workforce Time-Tracking Table)", "80"],
    ["Table 5.6", "Database Schema: leave_requests and leave_balances (Leave Engine Tables)", "81"],
    ["Table 5.7", "Database Schema: payroll_runs and payslips (Compensation Tables)", "82"],
    ["Table 5.8", "Database Schema: audit_logs (Enterprise Security Audit Table)", "82"],
    ["Table 7.1", "Automated Test Cases: Authentication & Session Security", "99"],
    ["Table 7.2", "Automated Test Cases: Employee Onboarding & Record Integrity", "100"],
    ["Table 7.3", "Automated Test Cases: Attendance Tracking & Clock-In Enforcement", "101"],
    ["Table 7.4", "Automated Test Cases: Leave Overlap Prevention & Balance Sync", "102"],
    ["Table 7.5", "Automated Test Cases: Deterministic Payroll & Deduction Calculation", "103"],
    ["Table 7.6", "Automated Test Cases: Role-Based Access Control (RBAC) Enforcement", "104"]
];
$b->addTable(["Table No.", "Table Caption / Title", "Page"], $tblListRows, null, [1500, 6860, 1000]);
$b->addPageBreak();

// -------------------------------------------------------------
// ABSTRACT
// -------------------------------------------------------------
$b->addHeading1("ABSTRACT");
$b->addParagraph("In contemporary enterprise ecosystems, human capital represents the most valuable strategic asset of an organization. However, the management of workforce administrative operations—spanning employee onboarding, real-time attendance verification, multi-tiered leave tracking, and complex statutory payroll disbursal—continues to pose significant operational bottlenecks when executed through fragmented legacy spreadsheets, decentralized desktop applications, or manual paperwork. These obsolete methodologies exhibit pronounced vulnerabilities, including computational inaccuracies in payroll formulations, time theft through unverified attendance logs, compliance non-alignment with statutory labor codes, administrative latency in leave authorizations, and critical data privacy exposures resulting from inadequate access control frameworks.");
$b->addParagraph("To resolve these pervasive organizational inefficiencies, this dissertation presents the design, architectural conception, engineering development, and cloud deployment of Smart HR Connect, an enterprise-grade, cloud-native Human Resource Management System (HRMS). Built upon the robust PHP 8.2+ runtime and the modern Laravel 12 Model-View-Controller (MVC) framework, the system enforces a strict Role-Based Access Control (RBAC) paradigm. The RBAC engine establishes four segregated actor tiers: Super Admin, HR Manager, Department Manager, and Employee, completely mitigating horizontal and vertical privilege escalation vulnerabilities.");
$b->addParagraph("Smart HR Connect integrates an automated operational pipeline: (1) an attendance engine supporting self-service check-in/out, mathematical duration computation, and duplicate entry prevention; (2) a leave management sub-system featuring quota tracking, overlap collision detection, and multi-tier manager approval lifecycles; (3) a deterministic payroll processor that calculates gross earnings, statutory deductions (Provident Fund, Professional Tax, Tax Deducted at Source), and itemized net pay summaries; and (4) an administrative governance module featuring read-only organizational reporting and comprehensive audit logging.");
$b->addParagraph("The application is deployed on Amazon Web Services (AWS) utilizing an Amazon Elastic Compute Cloud (EC2) instance running Amazon Linux 2023, Apache Web Server, PHP-FPM, and an Amazon Simple Storage Service (S3) bucket configured for private, encrypted document archiving via IAM instance profiles. Comprehensive empirical evaluation utilizing PHPUnit 11 demonstrated that all 85 automated feature test suites encompassing 344 security and business logic assertions achieved a 100% pass rate with sub-50 millisecond database transaction latency, confirming superior operational resilience, data integrity, and compliance readiness compared to traditional enterprise HR mechanisms.");
$b->addParagraph("Key words: Human Resource Management System (HRMS), Laravel 12 MVC Framework, Amazon Web Services (AWS EC2 & S3), Role-Based Access Control (RBAC), Attendance Automation, Deterministic Payroll Processing, Entity-Relationship Modeling, Cloud Security.", "both", true, false, 180);
$b->addPageBreak();
