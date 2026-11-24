Add-Type -AssemblyName System.Drawing

$diagramsDir = "c:\xampp\htdocs\AWS-Based-Employee-HR-System-main\docs\diagrams"

function New-Canvas ($w, $h) {
    $bmp = New-Object System.Drawing.Bitmap $w, $h
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::ClearTypeGridFit
    $g.Clear([System.Drawing.Color]::White)
    return @{ Bmp = $bmp; G = $g }
}

function Draw-WindowMockup ($g, $w, $h, $title) {
    # Window Frame
    $framePen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#CFD8DC"), 1.5)
    $g.DrawRectangle($framePen, 10, 10, ($w - 20), ($h - 20))
    
    # Titlebar
    $tbBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#263238"))
    $g.FillRectangle($tbBrush, 10, 10, ($w - 20), 32)
    
    # Title text
    $fontT = New-Object System.Drawing.Font("Arial", 9.5, [System.Drawing.FontStyle]::Bold)
    $whiteB = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
    $g.DrawString("Smart HR Connect - $title", $fontT, $whiteB, 25, 18)
    
    # Window control dots
    $redDot = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#EF5350"))
    $yelDot = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#FFEE58"))
    $grnDot = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#66BB6A"))
    $g.FillEllipse($redDot, ($w - 65), 20, 10, 10)
    $g.FillEllipse($yelDot, ($w - 50), 20, 10, 10)
    $g.FillEllipse($grnDot, ($w - 35), 20, 10, 10)
    
    # Sidebar
    $sbBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#ECEFF1"))
    $g.FillRectangle($sbBrush, 10, 42, 170, ($h - 52))
    
    $sbFont = New-Object System.Drawing.Font("Arial", 8.5, [System.Drawing.FontStyle]::Regular)
    $sbActiveFont = New-Object System.Drawing.Font("Arial", 8.5, [System.Drawing.FontStyle]::Bold)
    $sbDark = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#37474F"))
    $sbBlue = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#1A73E8"))
    
    $navItems = @("Dashboard", "Employees", "Attendance", "Leave Requests", "Payroll Runs", "Reports", "Roles & Perms", "Audit Logs")
    $ny = 60
    foreach ($item in $navItems) {
        if ($title -like "*$item*") {
            $hiBg = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#CFD8DC"))
            $g.FillRectangle($hiBg, 15, ($ny - 3), 160, 24)
            $g.DrawString("-> $item", $sbActiveFont, $sbBlue, 25, $ny)
        } else {
            $g.DrawString("   $item", $sbFont, $sbDark, 25, $ny)
        }
        $ny += 28
    }
}

# 1. Employee Directory Wireframe
$c = New-Canvas 860 480
Draw-WindowMockup $c.G 860 480 "Employees Directory"
$fontH = New-Object System.Drawing.Font("Arial", 11, [System.Drawing.FontStyle]::Bold)
$fontB = New-Object System.Drawing.Font("Arial", 8.5, [System.Drawing.FontStyle]::Regular)
$brushDark = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(40,40,40))

$c.G.DrawString("Employee Records Management", $fontH, $brushDark, 200, 55)

# Button Add Employee
$btnBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#1A73E8"))
$btnWhite = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
$c.G.FillRectangle($btnBrush, 700, 50, 130, 30)
$c.G.DrawString("+ Add Employee", $fontB, $btnWhite, 715, 58)

# Table Header
$tblBg = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#263238"))
$c.G.FillRectangle($tblBg, 200, 95, 630, 26)
$c.G.DrawString("Emp Code    Full Name           Department          Designation           Status      Actions", $fontB, $btnWhite, 210, 100)

$rows = @(
    @("EMP001", "Aarav Sharma", "Engineering", "Senior Software Eng", "Active"),
    @("EMP002", "Priya Nair", "Human Resources", "HR Generalist", "Active"),
    @("EMP003", "Rohan Mehta", "Finance", "Payroll Specialist", "Active"),
    @("EMP004", "Ananya Verma", "Marketing", "Content Strategist", "Active"),
    @("EMP005", "Vikram Patel", "Engineering", "DevOps Engineer", "Active")
)
$ry = 125
foreach ($r in $rows) {
    $rowBg = if (($ry / 28) % 2 -eq 0) { [System.Drawing.ColorTranslator]::FromHtml("#F5F5F5") } else { [System.Drawing.Color]::White }
    $rbBrush = New-Object System.Drawing.SolidBrush($rowBg)
    $c.G.FillRectangle($rbBrush, 200, $ry, 630, 26)
    
    $txt = "{0,-11} {1,-19} {2,-19} {3,-21} {4,-11} [View] [Edit]" -f $r[0], $r[1], $r[2], $r[3], $r[4]
    $c.G.DrawString($txt, $fontB, $brushDark, 210, ($ry + 5))
    $ry += 28
}
$c.Bmp.Save("$diagramsDir\fig_screen_employee.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose(); $c.Bmp.Dispose()

# 2. Attendance Tracking Wireframe
$c = New-Canvas 860 460
Draw-WindowMockup $c.G 860 460 "Attendance Tracking"
$c.G.DrawString("Daily Attendance Tracking & Clock-In", $fontH, $brushDark, 200, 55)

# Clock In Card
$cardBg = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#E8F0FE"))
$cardPen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#1A73E8"), 1.5)
$c.G.FillRectangle($cardBg, 200, 90, 300, 100)
$c.G.DrawRectangle($cardPen, 200, 90, 300, 100)
$c.G.DrawString("Today: Nov 23, 2026 | Shift: 09:00 - 18:00", $fontB, $brushDark, 210, 100)
$c.G.DrawString("Status: Present (Checked in at 09:02 AM)", $fontB, $brushDark, 210, 120)

$c.G.FillRectangle($btnBrush, 210, 145, 110, 30)
$c.G.DrawString("Check Out", $fontB, $btnWhite, 230, 153)

# Attendance Log Table
$c.G.DrawString("Recent Attendance Logs", $fontH, $brushDark, 200, 215)
$c.G.FillRectangle($tblBg, 200, 240, 630, 26)
$c.G.DrawString("Date            Check In       Check Out      Duration     Status      Remarks", $fontB, $btnWhite, 210, 246)

$attRows = @(
    @("2026-11-23", "09:02 AM", "--:-- --", "In Progress", "Present", "On Time"),
    @("2026-11-22", "08:58 AM", "06:05 PM", "9h 07m", "Present", "Regular Shift"),
    @("2026-11-21", "09:12 AM", "06:10 PM", "8h 58m", "Late", "Traffic delay verified"),
    @("2026-11-20", "09:00 AM", "06:00 PM", "9h 00m", "Present", "Regular Shift")
)
$ry = 270
foreach ($r in $attRows) {
    $rowBg = if (($ry / 28) % 2 -eq 0) { [System.Drawing.ColorTranslator]::FromHtml("#F5F5F5") } else { [System.Drawing.Color]::White }
    $rbBrush = New-Object System.Drawing.SolidBrush($rowBg)
    $c.G.FillRectangle($rbBrush, 200, $ry, 630, 26)
    $txt = "{0,-15} {1,-14} {2,-14} {3,-12} {4,-11} {5}" -f $r[0], $r[1], $r[2], $r[3], $r[4], $r[5]
    $c.G.DrawString($txt, $fontB, $brushDark, 210, ($ry + 5))
    $ry += 28
}
$c.Bmp.Save("$diagramsDir\fig_screen_attendance.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose(); $c.Bmp.Dispose()

# 3. Leave Request & Approval Wireframe
$c = New-Canvas 860 460
Draw-WindowMockup $c.G 860 460 "Leave Requests"
$c.G.DrawString("Leave Request & Approval Management", $fontH, $brushDark, 200, 55)

$c.G.FillRectangle($btnBrush, 700, 50, 130, 30)
$c.G.DrawString("+ Apply Leave", $fontB, $btnWhite, 725, 58)

# Leave Summary Cards
function Draw-StatCard ($g, $x, $y, $w, $h, $label, $val, $col) {
    $cBg = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#F8F9FA"))
    $cPen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml($col), 1.5)
    $g.FillRectangle($cBg, $x, $y, $w, $h)
    $g.DrawRectangle($cPen, $x, $y, $w, $h)
    $g.DrawString($label, $fontB, $brushDark, ($x + 10), ($y + 8))
    $fV = New-Object System.Drawing.Font("Arial", 14, [System.Drawing.FontStyle]::Bold)
    $vBr = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml($col))
    $g.DrawString($val, $fV, $vBr, ($x + 10), ($y + 26))
}

Draw-StatCard $c.G 200 90 140 60 "Casual Leave" "10 / 12 Days" "#1A73E8"
Draw-StatCard $c.G 360 90 140 60 "Sick Leave" "7 / 8 Days" "#1E8E3E"
Draw-StatCard $c.G 520 90 140 60 "Earned Leave" "15 / 15 Days" "#F9AB00"
Draw-StatCard $c.G 680 90 140 60 "Pending Approval" "1 Request" "#D93025"

# Requests Table
$c.G.FillRectangle($tblBg, 200, 170, 630, 26)
$c.G.DrawString("Emp Name         Leave Type   From Date    To Date      Days   Status      Action", $fontB, $btnWhite, 210, 176)

$lRows = @(
    @("Aarav Sharma", "Casual Leave", "2026-12-01", "2026-12-02", "2.0", "Approved", "--"),
    @("Priya Nair", "Sick Leave", "2026-11-24", "2026-11-25", "2.0", "Pending", "[Approve] [Reject]"),
    @("Rohan Mehta", "Casual Leave", "2026-11-10", "2026-11-11", "2.0", "Approved", "--"),
    @("Ananya Verma", "Earned Leave", "2026-10-15", "2026-10-18", "4.0", "Rejected", "Overlap detected")
)
$ry = 200
foreach ($r in $lRows) {
    $rowBg = if (($ry / 28) % 2 -eq 0) { [System.Drawing.ColorTranslator]::FromHtml("#F5F5F5") } else { [System.Drawing.Color]::White }
    $rbBrush = New-Object System.Drawing.SolidBrush($rowBg)
    $c.G.FillRectangle($rbBrush, 200, $ry, 630, 26)
    $txt = "{0,-16} {1,-12} {2,-12} {3,-12} {4,-6} {5,-11} {6}" -f $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6]
    $c.G.DrawString($txt, $fontB, $brushDark, 210, ($ry + 5))
    $ry += 28
}
$c.Bmp.Save("$diagramsDir\fig_screen_leave.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose(); $c.Bmp.Dispose()

# 4. Payroll Itemized Payslip Wireframe
$c = New-Canvas 860 480
Draw-WindowMockup $c.G 860 480 "Payroll Runs & Payslip"
$c.G.DrawString("Monthly Payroll Processing & Itemized Payslip", $fontH, $brushDark, 200, 55)

# Payslip Card Preview
$slipBg = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#FFFFFF"))
$slipPen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#455A64"), 1.5)
$c.G.FillRectangle($slipBg, 200, 90, 630, 360)
$c.G.DrawRectangle($slipPen, 200, 90, 630, 360)

# Header in slip
$sHdrBg = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#ECEFF1"))
$c.G.FillRectangle($sHdrBg, 200, 90, 630, 40)
$c.G.DrawString("PAYSLIP FOR NOVEMBER 2026 - CONFIDENTIAL", $fontH, $brushDark, 220, 102)

$c.G.DrawString("Employee Name: Aarav Sharma", $fontB, $brushDark, 220, 140)
$c.G.DrawString("Employee Code: EMP001", $fontB, $brushDark, 520, 140)
$c.G.DrawString("Department: Engineering", $fontB, $brushDark, 220, 160)
$c.G.DrawString("Designation: Senior Software Engineer", $fontB, $brushDark, 520, 160)
$c.G.DrawString("Pay Period: 01-Nov-2026 to 30-Nov-2026", $fontB, $brushDark, 220, 180)
$c.G.DrawString("Payment Status: PAID", $fontB, $brushDark, 520, 180)

# Earnings & Deductions Split
$c.G.FillRectangle($tblBg, 210, 210, 300, 24)
$c.G.DrawString("Earnings / Allowances              Amount", $fontB, $btnWhite, 220, 215)
$c.G.FillRectangle($tblBg, 520, 210, 300, 24)
$c.G.DrawString("Deductions                         Amount", $fontB, $btnWhite, 530, 215)

$c.G.DrawString("Basic Salary                    $ 4,500.00", $fontB, $brushDark, 220, 240)
$c.G.DrawString("House Rent Allowance (HRA)      $ 1,800.00", $fontB, $brushDark, 220, 260)
$c.G.DrawString("Special Allowance               $ 1,200.00", $fontB, $brushDark, 220, 280)
$c.G.DrawString("Medical Allowance               $   500.00", $fontB, $brushDark, 220, 300)

$c.G.DrawString("Provident Fund (PF)             $   540.00", $fontB, $brushDark, 530, 240)
$c.G.DrawString("Professional Tax (PT)           $   200.00", $fontB, $brushDark, 530, 260)
$c.G.DrawString("Income Tax (TDS)                $   660.00", $fontB, $brushDark, 530, 280)
$c.G.DrawString("Total Deductions                $ 1,400.00", $fontB, $brushDark, 530, 300)

# Net Salary Bar
$netBg = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#E8F5E9"))
$netPen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#2E7D32"), 2)
$c.G.FillRectangle($netBg, 210, 335, 610, 45)
$c.G.DrawRectangle($netPen, 210, 335, 610, 45)

$fNet = New-Object System.Drawing.Font("Arial", 11, [System.Drawing.FontStyle]::Bold)
$netCol = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#1B5E20"))
$c.G.DrawString("GROSS SALARY: $ 8,000.00  |  TOTAL DEDUCTIONS: $ 1,400.00  |  NET SALARY: $ 6,600.00", $fNet, $netCol, 225, 348)

$c.G.DrawString("Generated on: 2026-11-23 18:00:00 | System Verified Digital Payslip", $fontB, $brushDark, 220, 400)

$c.Bmp.Save("$diagramsDir\fig_screen_payroll.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose(); $c.Bmp.Dispose()

Write-Output "All 4 UI Wireframe mockups generated successfully."
