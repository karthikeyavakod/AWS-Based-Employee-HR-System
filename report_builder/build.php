<?php
// build.php - Main assembler for Employee_Manager_and_HR_System_Project_Report.docx

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/DocxBuilder.php';

echo "Initializing DocxBuilder with Times New Roman formatting...\n";
$b = new DocxBuilder();

echo "Loading Section: Frontmatter (Title, Certificate, Declaration, TOC, Figures, Tables, Abstract)...\n";
require_once __DIR__ . '/section_frontmatter.php';

echo "Loading Section: Chapters 1, 2, and 3 (Introduction, Company Profile, Literature Survey)...\n";
require_once __DIR__ . '/section_intro_company_lit.php';

echo "Loading Section: Chapters 4, 5, and 6 (Problem Statement, Feasibility, Tools & Technologies)...\n";
require_once __DIR__ . '/section_problem_feasibility_tools.php';

echo "Loading Section: Chapters 7 and 8 (Hardware/Software Specs, Software Requirements Specification)...\n";
require_once __DIR__ . '/section_requirements_srs.php';

echo "Loading Section: Chapters 9 and 10 (System Design, UML Diagrams, ERD, Implementation & Screens)...\n";
require_once __DIR__ . '/section_design_implementation.php';

echo "Loading Section: Chapters 11, 12, 13, 14, 15, and 16 (Testing, Training, Maintenance, Conclusion, Enhancements, References)...\n";
require_once __DIR__ . '/section_testing_maintenance_end.php';

$outputPath = dirname(__DIR__) . '/Employee_Manager_and_HR_System_Project_Report.docx';
echo "Building Word document archive at: $outputPath ...\n";
$b->build($outputPath);

if (file_exists($outputPath)) {
    $sizeKb = round(filesize($outputPath) / 1024, 2);
    echo "SUCCESS: Employee_Manager_and_HR_System_Project_Report.docx generated successfully! Size: {$sizeKb} KB\n";
} else {
    echo "ERROR: Output file was not created.\n";
}
