Add-Type -AssemblyName System.Drawing

$diagramsDir = "c:\xampp\htdocs\AWS-Based-Employee-HR-System-main\docs\diagrams"
if (!(Test-Path $diagramsDir)) { New-Item -ItemType Directory -Force -Path $diagramsDir }

function New-Canvas ($w, $h) {
    $bmp = New-Object System.Drawing.Bitmap $w, $h
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::ClearTypeGridFit
    $g.Clear([System.Drawing.Color]::White)
    return @{ Bmp = $bmp; G = $g }
}

function Draw-Box ($g, $x, $y, $w, $h, $title, $subtext, $bgHex, $borderHex, $titleColorHex="#FFFFFF") {
    $bgColor = [System.Drawing.ColorTranslator]::FromHtml($bgHex)
    $borderColor = [System.Drawing.ColorTranslator]::FromHtml($borderHex)
    $titleColor = [System.Drawing.ColorTranslator]::FromHtml($titleColorHex)
    
    $bgBrush = New-Object System.Drawing.SolidBrush($bgColor)
    $borderPen = New-Object System.Drawing.Pen($borderColor, 2)
    $titleBrush = New-Object System.Drawing.SolidBrush($titleColor)
    $bodyBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(50, 50, 50))
    
    $g.FillRectangle($bgBrush, $x, $y, $w, $h)
    $g.DrawRectangle($borderPen, $x, $y, $w, $h)
    
    $headerHeight = 26
    $headerBg = New-Object System.Drawing.SolidBrush($borderColor)
    $g.FillRectangle($headerBg, $x, $y, $w, $headerHeight)
    
    $titleFont = New-Object System.Drawing.Font("Arial", 9.5, [System.Drawing.FontStyle]::Bold)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    
    $rectHeader = New-Object System.Drawing.RectangleF($x, $y, $w, $headerHeight)
    $g.DrawString($title, $titleFont, $titleBrush, $rectHeader, $sf)
    
    if ($subtext -ne "") {
        $bodyFont = New-Object System.Drawing.Font("Arial", 8.5, [System.Drawing.FontStyle]::Regular)
        $rectBody = New-Object System.Drawing.RectangleF(($x + 6), ($y + $headerHeight + 6), ($w - 12), ($h - $headerHeight - 8))
        $sfBody = New-Object System.Drawing.StringFormat
        $sfBody.Alignment = [System.Drawing.StringAlignment]::Center
        $sfBody.LineAlignment = [System.Drawing.StringAlignment]::Center
        $g.DrawString($subtext, $bodyFont, $bodyBrush, $rectBody, $sfBody)
    }
}

function Draw-Arrow ($g, $x1, $y1, $x2, $y2, $hexColor="#2B579A", $label="") {
    $color = [System.Drawing.ColorTranslator]::FromHtml($hexColor)
    $pen = New-Object System.Drawing.Pen($color, 2)
    $pen.CustomEndCap = New-Object System.Drawing.Drawing2D.AdjustableArrowCap(4, 4, $true)
    $g.DrawLine($pen, $x1, $y1, $x2, $y2)
    
    if ($label -ne "") {
        $font = New-Object System.Drawing.Font("Arial", 8, [System.Drawing.FontStyle]::Bold)
        $brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(40, 40, 40))
        $bgLabel = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(240, 245, 255))
        $midX = ($x1 + $x2) / 2
        $midY = ($y1 + $y2) / 2
        $sz = $g.MeasureString($label, $font)
        $g.FillRectangle($bgLabel, ($midX - $sz.Width/2), ($midY - $sz.Height/2), $sz.Width, $sz.Height)
        $sf = New-Object System.Drawing.StringFormat
        $sf.Alignment = [System.Drawing.StringAlignment]::Center
        $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
        $g.DrawString($label, $font, $brush, $midX, $midY, $sf)
    }
}

# -------------------------------------------------------------
# 1. Fig 3.3.1: Laravel MVC & Request Lifecycle Architecture
# -------------------------------------------------------------
$c = New-Canvas 900 480
$titleFont = New-Object System.Drawing.Font("Arial", 13, [System.Drawing.FontStyle]::Bold)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
$c.G.DrawString("Fig 3.3.1: Laravel 12 MVC & HTTP Request Lifecycle Architecture", $titleFont, $titleBrush, 20, 15)

Draw-Box $c.G 40 70 140 70 "HTTP Request" "Browser / Client`nGET/POST/PATCH" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 220 70 140 70 "Public Entry" "index.php`nApplication Boot" "#FEF7E0" "#F9AB00"
Draw-Box $c.G 400 70 150 70 "HTTP Kernel" "Session / CSRF`nAuth & Role Middleware" "#FCE8E6" "#D93025"
Draw-Box $c.G 590 70 140 70 "Routing Engine" "routes/web.php`nPermission Matching" "#E6F4EA" "#1E8E3E"
Draw-Box $c.G 760 70 110 70 "Form Requests" "Validation`n& Auth Checks" "#F3E8FD" "#9334E6"

Draw-Box $c.G 220 220 180 80 "Controllers Layer" "DashboardController`nEmployeeController`nPayrollController" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 450 220 180 80 "Business Services" "AttendanceService`nPayrollService`nLeaveService / Audit" "#FEF7E0" "#F9AB00"
Draw-Box $c.G 680 220 180 80 "Eloquent ORM" "ActiveRecord Models`nRelationships & Pivots" "#E6F4EA" "#1E8E3E"

Draw-Box $c.G 450 360 180 80 "Database Engine" "MySQL 8.0 / MariaDB`n(or SQLite Engine)" "#F1F3F4" "#5F6368"
Draw-Box $c.G 220 360 180 80 "Presentation Layer" "Blade Views & Components`nTailwind & Bootstrap 5" "#FCE8E6" "#D93025"

Draw-Arrow $c.G 180 105 220 105 "#1A73E8"
Draw-Arrow $c.G 360 105 400 105 "#F9AB00"
Draw-Arrow $c.G 550 105 590 105 "#D93025"
Draw-Arrow $c.G 730 105 760 105 "#1E8E3E"
Draw-Arrow $c.G 815 140 310 220 "#9334E6" "Pass"

Draw-Arrow $c.G 400 260 450 260 "#1A73E8" "Call"
Draw-Arrow $c.G 630 260 680 260 "#F9AB00" "Execute"
Draw-Arrow $c.G 770 300 630 380 "#1E8E3E" "SQL Query"
Draw-Arrow $c.G 450 400 400 400 "#5F6368" "Data Set"
Draw-Arrow $c.G 270 360 270 300 "#D93025" "Render"
Draw-Arrow $c.G 220 260 110 140 "#1A73E8" "HTML Response"

$c.Bmp.Save("$diagramsDir\fig_laravel_arch.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose()
$c.Bmp.Dispose()

# -------------------------------------------------------------
# 2. Fig 4.1.1: AWS Cloud Deployment & System Architecture
# -------------------------------------------------------------
$c = New-Canvas 920 520
$titleFont = New-Object System.Drawing.Font("Arial", 13, [System.Drawing.FontStyle]::Bold)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
$c.G.DrawString("Fig 4.1.1: AWS Cloud Deployment Architecture (Smart HR Connect)", $titleFont, $titleBrush, 20, 15)

# Outer AWS VPC Box
$vpcPen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#FF9900"), 2)
$vpcPen.DashStyle = [System.Drawing.Drawing2D.DashStyle]::Dash
$c.G.DrawRectangle($vpcPen, 200, 55, 690, 445)
$vpcFont = New-Object System.Drawing.Font("Arial", 10, [System.Drawing.FontStyle]::Bold)
$vpcBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#E07A00"))
$c.G.DrawString("Amazon Web Services (AWS) Cloud - Public/Private Subnet", $vpcFont, $vpcBrush, 215, 65)

# External Users
Draw-Box $c.G 20 120 140 70 "HR Admin" "Browser Interface`nRole: Super/HR" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 20 240 140 70 "Employee" "Self-Service Portal`nAttendance/Leave" "#E6F4EA" "#1E8E3E"
Draw-Box $c.G 20 360 140 70 "Dept Manager" "Approval Dashboard`nReview Staff" "#FEF7E0" "#F9AB00"

# AWS Security Group Box
Draw-Box $c.G 225 180 150 180 "AWS Security Group" "Inbound Filter:`nHTTP (Port 80)`nHTTPS (Port 443)`nSSH Port 22 (Admin IP)`n`nStateful Firewall" "#F1F3F4" "#5F6368"

# EC2 Instance Box
$ec2Pen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#2B579A"), 2)
$c.G.DrawRectangle($ec2Pen, 410, 100, 260, 380)
$ec2Font = New-Object System.Drawing.Font("Arial", 10, [System.Drawing.FontStyle]::Bold)
$ec2Brush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#1B365D"))
$c.G.DrawString("Amazon EC2 (Amazon Linux 2023)", $ec2Font, $ec2Brush, 420, 110)

Draw-Box $c.G 425 140 230 65 "Apache Web Server" "VirtualHost Configuration`nmod_rewrite & SSL TLS" "#FCE8E6" "#D93025"
Draw-Box $c.G 425 220 230 65 "PHP 8.2-FPM Runtime" "FastCGI Process Manager`nOPcache & Extensions" "#FEF7E0" "#F9AB00"
Draw-Box $c.G 425 300 230 65 "Laravel 12 Engine" "Auth, RBAC, Controllers`nAttendance & Payroll Services" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 425 385 230 75 "EBS Volume Storage" "hrms_db (SQLite/MySQL)`nAudit Logs & File Cache" "#E6F4EA" "#1E8E3E"

# AWS IAM Role & S3
Draw-Box $c.G 705 140 165 95 "IAM Instance Profile" "SmartHrEc2S3Role`ns3:GetObject`ns3:PutObject`ns3:ListBucket`nTemporary STS Tokens" "#F3E8FD" "#9334E6"
Draw-Box $c.G 705 280 165 140 "Amazon S3 Bucket" "Private Object Store`nHR Document Storage`nPayslip Archive (PDF)`nEncrypted at Rest`nBlock Public Access" "#FEF7E0" "#F9AB00"

# Arrows
Draw-Arrow $c.G 160 155 225 210 "#1A73E8" "HTTPS"
Draw-Arrow $c.G 160 275 225 270 "#1E8E3E" "HTTPS"
Draw-Arrow $c.G 160 395 225 330 "#F9AB00" "HTTPS"

Draw-Arrow $c.G 375 270 425 170 "#D93025" "Forward"
Draw-Arrow $c.G 540 205 540 220 "#F9AB00" "FastCGI"
Draw-Arrow $c.G 540 285 540 300 "#1A73E8" "Execute"
Draw-Arrow $c.G 540 365 540 385 "#1E8E3E" "I/O Read/Write"

Draw-Arrow $c.G 705 185 655 300 "#9334E6" "Credentials"
Draw-Arrow $c.G 655 340 705 340 "#F9AB00" "Store/Fetch"

$c.Bmp.Save("$diagramsDir\fig_cloud_arch.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose()
$c.Bmp.Dispose()

# -------------------------------------------------------------
# 3. Fig 3.3.4: TCP/IP vs OSI Network Stack
# -------------------------------------------------------------
$c = New-Canvas 820 380
$titleFont = New-Object System.Drawing.Font("Arial", 13, [System.Drawing.FontStyle]::Bold)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
$c.G.DrawString("Fig 3.3.4: Network Protocol Architecture (Smart HR Connect)", $titleFont, $titleBrush, 20, 15)

Draw-Box $c.G 40 60 320 60 "Application Layer (OSI 5-7)" "HTTP / HTTPS, DNS, SSH, SMTP`n(Laravel 12 Web Requests)" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 40 135 320 60 "Transport Layer (OSI 4)" "TCP (Port 80/443/22) / UDP`nConnection-oriented & Reliable" "#FEF7E0" "#F9AB00"
Draw-Box $c.G 40 210 320 60 "Internet Layer (OSI 3)" "IPv4 / IPv6, ICMP, Routing`n(AWS VPC & Security Group Rules)" "#E6F4EA" "#1E8E3E"
Draw-Box $c.G 40 285 320 60 "Network Access Layer (OSI 1-2)" "Ethernet, Virtual NIC, AWS Hypervisor`nPhysical Data Link" "#F1F3F4" "#5F6368"

Draw-Box $c.G 440 60 340 285 "Packet Delivery Flow" "Client Browser sends GET /dashboard`n`n1. DNS resolves EC2 Public IP`n2. TCP 3-Way Handshake (SYN, SYN-ACK, ACK)`n3. TLS 1.3 Handshake for HTTPS Encryption`n4. HTTP Payload encapsulated in TCP Segments`n5. IP Datagram routing across internet to AWS IGW`n6. AWS Security Group validates Port 443 rule`n7. Apache receives TCP stream, dispatches to PHP`n8. Response encapsulated & returned via TCP" "#F9F9FB" "#2B579A"

Draw-Arrow $c.G 360 90 440 90 "#1A73E8"
Draw-Arrow $c.G 360 165 440 165 "#F9AB00"
Draw-Arrow $c.G 360 240 440 240 "#1E8E3E"
Draw-Arrow $c.G 360 315 440 315 "#5F6368"

$c.Bmp.Save("$diagramsDir\fig_tcp_ip.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose()
$c.Bmp.Dispose()

# -------------------------------------------------------------
# 4. Fig 5.2.1: Data Flow Diagram (DFD Level 0 & Level 1)
# -------------------------------------------------------------
$c = New-Canvas 920 540
$titleFont = New-Object System.Drawing.Font("Arial", 13, [System.Drawing.FontStyle]::Bold)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
$c.G.DrawString("Fig 5.2.1: Context Diagram / Data Flow Diagram (Level 0 & 1)", $titleFont, $titleBrush, 20, 15)

# Central System Bubble
$bubbleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#E8F0FE"))
$bubblePen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#1A73E8"), 3)
$c.G.FillEllipse($bubbleBrush, 340, 180, 240, 160)
$c.G.DrawEllipse($bubblePen, 340, 180, 240, 160)

$bubbleFont = New-Object System.Drawing.Font("Arial", 11, [System.Drawing.FontStyle]::Bold)
$bubbleTextBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#1B365D"))
$sf = New-Object System.Drawing.StringFormat
$sf.Alignment = [System.Drawing.StringAlignment]::Center
$sf.LineAlignment = [System.Drawing.StringAlignment]::Center
$c.G.DrawString("0.0`nSmart HR Connect`nCore Management`nSystem Engine", $bubbleFont, $bubbleTextBrush, 460, 260, $sf)

# External Entities
Draw-Box $c.G 30 80 170 75 "Employee" "Clock-in / Clock-out`nLeave Applications`nView Profile & Payslip" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 30 350 170 75 "HR Manager" "Employee Onboarding`nAttendance Corrections`nPayroll Execution" "#FEF7E0" "#F9AB00"
Draw-Box $c.G 720 80 170 75 "Department Manager" "Attendance Review`nLeave Recommendation`nApproval / Rejection" "#E6F4EA" "#1E8E3E"
Draw-Box $c.G 720 350 170 75 "Super Admin" "User Accounts & RBAC`nRole & Permission Grants`nAudit Log Review" "#FCE8E6" "#D93025"

# Database Store
Draw-Box $c.G 360 420 200 65 "D1: HRMS Central Database" "Users, Roles, Employees, Attendances,`nLeaves, Payrolls, Audit Logs" "#F1F3F4" "#5F6368"

# DFD Flows
Draw-Arrow $c.G 200 115 360 200 "#1A73E8" "Attendance & Leave Req"
Draw-Arrow $c.G 360 230 200 140 "#1A73E8" "Profile & Payslips"

Draw-Arrow $c.G 200 375 360 300 "#F9AB00" "Payroll Run & Records"
Draw-Arrow $c.G 360 325 200 405 "#F9AB00" "HR Reports & Alerts"

Draw-Arrow $c.G 720 115 560 200 "#1E8E3E" "Leave Approval Status"
Draw-Arrow $c.G 560 230 720 140 "#1E8E3E" "Team Roster & Logs"

Draw-Arrow $c.G 720 375 560 300 "#D93025" "Role / Permission Update"
Draw-Arrow $c.G 560 325 720 405 "#D93025" "Audit Trail & System Logs"

Draw-Arrow $c.G 440 340 440 420 "#5F6368" "SQL Write / Update"
Draw-Arrow $c.G 480 420 480 340 "#5F6368" "SQL Read / Query"

$c.Bmp.Save("$diagramsDir\fig_dfd.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose()
$c.Bmp.Dispose()

# -------------------------------------------------------------
# 5. Fig 5.3.1: UML Use Case Diagram
# -------------------------------------------------------------
$c = New-Canvas 920 600
$titleFont = New-Object System.Drawing.Font("Arial", 13, [System.Drawing.FontStyle]::Bold)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
$c.G.DrawString("Fig 5.3.1: UML Use Case Diagram (Smart HR Connect)", $titleFont, $titleBrush, 20, 15)

# System Boundary
$sysPen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#1B365D"), 2)
$c.G.DrawRectangle($sysPen, 260, 50, 420, 530)
$sysFont = New-Object System.Drawing.Font("Arial", 10, [System.Drawing.FontStyle]::Bold)
$sysBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#1B365D"))
$c.G.DrawString("Smart HR Connect System Boundary", $sysFont, $sysBrush, 360, 58)

function Draw-UseCase ($g, $x, $y, $w, $h, $text, $bgHex="#E8F0FE", $borderHex="#1A73E8") {
    $bgBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml($bgHex))
    $pen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml($borderHex), 1.5)
    $g.FillEllipse($bgBrush, $x, $y, $w, $h)
    $g.DrawEllipse($pen, $x, $y, $w, $h)
    $font = New-Object System.Drawing.Font("Arial", 8.5, [System.Drawing.FontStyle]::Bold)
    $brush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#202124"))
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $g.DrawString($text, $font, $brush, ($x + $w/2), ($y + $h/2), $sf)
}

Draw-UseCase $c.G 340 85 240 38 "UC1: Login & Session Auth" "#E8F0FE" "#1A73E8"
Draw-UseCase $c.G 340 135 240 38 "UC2: Self Attendance Check-In/Out" "#E6F4EA" "#1E8E3E"
Draw-UseCase $c.G 340 185 240 38 "UC3: Submit Leave Request" "#E6F4EA" "#1E8E3E"
Draw-UseCase $c.G 340 235 240 38 "UC4: View Payslips & Personal Data" "#E6F4EA" "#1E8E3E"
Draw-UseCase $c.G 340 285 240 38 "UC5: Approve / Reject Leave" "#FEF7E0" "#F9AB00"
Draw-UseCase $c.G 340 335 240 38 "UC6: Manage Employee Records" "#FEF7E0" "#F9AB00"
Draw-UseCase $c.G 340 385 240 38 "UC7: Process Monthly Payroll" "#FEF7E0" "#F9AB00"
Draw-UseCase $c.G 340 435 240 38 "UC8: View Reports & Analytics" "#FEF7E0" "#F9AB00"
Draw-UseCase $c.G 340 485 240 38 "UC9: Manage Roles & Permissions" "#FCE8E6" "#D93025"
Draw-UseCase $c.G 340 535 240 38 "UC10: Export Audit Logs (CSV/PDF)" "#FCE8E6" "#D93025"

Draw-Box $c.G 40 130 160 80 "Employee" "Self-Service User`nRole: Employee" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 40 350 160 80 "HR Manager" "Operations Lead`nRole: HR Manager" "#FEF7E0" "#F9AB00"
Draw-Box $c.G 720 180 170 80 "Department Manager" "Team Supervisor`nRole: Dept Manager" "#E6F4EA" "#1E8E3E"
Draw-Box $c.G 720 440 170 80 "Super Admin" "System Administrator`nFull Permission Bypass" "#FCE8E6" "#D93025"

$linkPen = New-Object System.Drawing.Pen([System.Drawing.Color]::Gray, 1.2)
$linkPen.DashStyle = [System.Drawing.Drawing2D.DashStyle]::Dash

$c.G.DrawLine($linkPen, 200, 150, 340, 104)
$c.G.DrawLine($linkPen, 200, 160, 340, 154)
$c.G.DrawLine($linkPen, 200, 170, 340, 204)
$c.G.DrawLine($linkPen, 200, 180, 340, 254)

$c.G.DrawLine($linkPen, 200, 360, 340, 104)
$c.G.DrawLine($linkPen, 200, 370, 340, 354)
$c.G.DrawLine($linkPen, 200, 380, 340, 404)
$c.G.DrawLine($linkPen, 200, 390, 340, 454)

$c.G.DrawLine($linkPen, 720, 200, 580, 104)
$c.G.DrawLine($linkPen, 720, 220, 580, 304)
$c.G.DrawLine($linkPen, 720, 240, 580, 454)

$c.G.DrawLine($linkPen, 720, 460, 580, 104)
$c.G.DrawLine($linkPen, 720, 475, 580, 504)
$c.G.DrawLine($linkPen, 720, 490, 580, 554)

$c.Bmp.Save("$diagramsDir\fig_usecase.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose()
$c.Bmp.Dispose()

# -------------------------------------------------------------
# 6. Fig 5.4.1: UML Sequence Diagram (Leave Workflow)
# -------------------------------------------------------------
$c = New-Canvas 920 520
$titleFont = New-Object System.Drawing.Font("Arial", 13, [System.Drawing.FontStyle]::Bold)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
$c.G.DrawString("Fig 5.4.1: UML Sequence Diagram - Leave Request & Approval Lifecycle", $titleFont, $titleBrush, 20, 15)

Draw-Box $c.G 30 55 120 40 "Employee" "" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 190 55 140 40 "LeaveController" "" "#FEF7E0" "#F9AB00"
Draw-Box $c.G 370 55 140 40 "LeaveService" "" "#E6F4EA" "#1E8E3E"
Draw-Box $c.G 550 55 150 40 "Database (MySQL)" "" "#F1F3F4" "#5F6368"
Draw-Box $c.G 740 55 140 40 "Dept Manager / HR" "" "#FCE8E6" "#D93025"

$lifePen = New-Object System.Drawing.Pen([System.Drawing.Color]::LightGray, 1.5)
$lifePen.DashStyle = [System.Drawing.Drawing2D.DashStyle]::Dash
$c.G.DrawLine($lifePen, 90, 95, 90, 480)
$c.G.DrawLine($lifePen, 260, 95, 260, 480)
$c.G.DrawLine($lifePen, 440, 95, 440, 480)
$c.G.DrawLine($lifePen, 625, 95, 625, 480)
$c.G.DrawLine($lifePen, 810, 95, 810, 480)

Draw-Arrow $c.G 90 120 260 120 "#1A73E8" "1: POST /leave/store (start, end, type)"
Draw-Arrow $c.G 260 150 440 150 "#F9AB00" "2: validateAndSubmitRequest()"
Draw-Arrow $c.G 440 180 625 180 "#1E8E3E" "3: Check leave balance & overlaps"
Draw-Arrow $c.G 625 210 440 210 "#5F6368" "4: Returns valid balance status"
Draw-Arrow $c.G 440 240 625 240 "#1E8E3E" "5: INSERT leave_request (status='pending')"
Draw-Arrow $c.G 260 270 90 270 "#1A73E8" "6: Success Alert: Request Submitted"

Draw-Arrow $c.G 810 310 260 310 "#D93025" "7: GET /admin/leave-requests (review)"
Draw-Arrow $c.G 260 340 810 340 "#F9AB00" "8: Display pending requests list"
Draw-Arrow $c.G 810 380 260 380 "#D93025" "9: POST /leave/{id}/approve"
Draw-Arrow $c.G 260 410 440 410 "#F9AB00" "10: processApproval(requestId, approverId)"
Draw-Arrow $c.G 440 440 625 440 "#1E8E3E" "11: UPDATE leave_requests SET status='approved'"
Draw-Arrow $c.G 440 465 625 465 "#1E8E3E" "12: DEDUCT leave_balances.used_days"

$c.Bmp.Save("$diagramsDir\fig_sequence.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose()
$c.Bmp.Dispose()

# -------------------------------------------------------------
# 7. Fig 5.5.1: UML Collaboration / Component Diagram
# -------------------------------------------------------------
$c = New-Canvas 880 430
$titleFont = New-Object System.Drawing.Font("Arial", 13, [System.Drawing.FontStyle]::Bold)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
$c.G.DrawString("Fig 5.5.1: Collaboration & Component Architecture Diagram", $titleFont, $titleBrush, 20, 15)

Draw-Box $c.G 40 70 170 90 "Client Presentation" "Blade Template Engine`nBootstrap 5.3 UI Components`nResponsive Grid & Icons" "#E8F0FE" "#1A73E8"
Draw-Box $c.G 250 70 180 90 "HTTP / Middleware" "Kernel Pipeline`nPermissionMiddleware`nActiveStatusFilter" "#FEF7E0" "#F9AB00"
Draw-Box $c.G 470 70 180 90 "Application Services" "AttendanceService`nPayrollEngineService`nAuditLogService" "#E6F4EA" "#1E8E3E"
Draw-Box $c.G 690 70 160 90 "ORM / Persistence" "Eloquent Active Record`nQuery Builder`nSchema Migrations" "#F3E8FD" "#9334E6"

Draw-Box $c.G 250 250 180 90 "Security & Access" "RBAC Matrix Tables`nBcrypt Password Hasher`nCSRF Token Verifier" "#FCE8E6" "#D93025"
Draw-Box $c.G 470 250 180 90 "Data Storage Layer" "MySQL / MariaDB RDBMS`nAmazon S3 Storage`nLocal File Cache" "#F1F3F4" "#5F6368"

Draw-Arrow $c.G 210 115 250 115 "#1A73E8" "1: Request"
Draw-Arrow $c.G 430 115 470 115 "#F9AB00" "2: Delegate"
Draw-Arrow $c.G 650 115 690 115 "#1E8E3E" "3: Persist"

Draw-Arrow $c.G 340 160 340 250 "#D93025" "Validate RBAC"
Draw-Arrow $c.G 560 160 560 250 "#5F6368" "Execute I/O"
Draw-Arrow $c.G 690 150 630 250 "#9334E6" "Commit Transaction"

$c.Bmp.Save("$diagramsDir\fig_collaboration.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose()
$c.Bmp.Dispose()

# -------------------------------------------------------------
# 8-11: Activity Diagrams (Employee, HR, Dept Manager, Super Admin)
# -------------------------------------------------------------
function Draw-Activity ($path, $title, $steps) {
    $c = New-Canvas 780 500
    $titleFont = New-Object System.Drawing.Font("Arial", 12.5, [System.Drawing.FontStyle]::Bold)
    $titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
    $c.G.DrawString($title, $titleFont, $titleBrush, 20, 15)
    
    $startBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#1E8E3E"))
    $c.G.FillEllipse($startBrush, 40, 50, 30, 30)
    $fontS = New-Object System.Drawing.Font("Arial", 8, [System.Drawing.FontStyle]::Bold)
    $whiteB = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $c.G.DrawString("Start", $fontS, $whiteB, 55, 65, $sf)
    
    $boxW = 180
    $boxH = 45
    
    for ($i=0; $i -lt $steps.Length; $i++) {
        $st = $steps[$i]
        $row = [Math]::Floor($i / 3)
        $col = $i % 3
        
        $bx = 100 + $col * 220
        $by = 50 + $row * 105
        
        Draw-Box $c.G $bx $by $boxW $boxH "Step $($i+1)" $st "#E8F0FE" "#1A73E8"
        
        if ($i -gt 0) {
            $prevCol = ($i-1) % 3
            $prevRow = [Math]::Floor(($i-1) / 3)
            $px = 100 + $prevCol * 220 + $boxW/2
            $py = 50 + $prevRow * 105 + $boxH/2
            $targetX = $bx + $boxW/2
            $targetY = $by + $boxH/2
            
            if ($row -eq $prevRow) {
                Draw-Arrow $c.G ($px + $boxW/2) $py ($bx) $targetY "#1A73E8"
            } else {
                Draw-Arrow $c.G $px ($py + $boxH/2) $targetX $by "#1A73E8"
            }
        } else {
            Draw-Arrow $c.G 70 65 100 65 "#1A73E8"
        }
    }
    
    $c.Bmp.Save($path, [System.Drawing.Imaging.ImageFormat]::Png)
    $c.G.Dispose()
    $c.Bmp.Dispose()
}

Draw-Activity "$diagramsDir\fig_activity_employee.png" "Fig 5.6.1: Flow Chart / Activity Diagram: Employee Workflow" @(
    "Login with Credentials",
    "View Self-Service Dashboard",
    "Perform Daily Check-In",
    "Submit Attendance Correction",
    "Apply for Annual / Sick Leave",
    "Track Leave Approval Status",
    "Check Out at End of Day",
    "View & Download Payslip",
    "Update Profile / Password"
)

Draw-Activity "$diagramsDir\fig_activity_hr.png" "Fig 5.6.2: Flow Chart / Activity Diagram: HR Administrator Workflow" @(
    "HR Manager Authentication",
    "Review HR Dashboard KPIs",
    "Onboard & Register Employee",
    "Assign Dept & Designation",
    "Monitor Daily Attendance",
    "Verify Leave Allocations",
    "Execute Monthly Payroll Run",
    "Generate Itemized Payslips",
    "Export Departmental HR Reports"
)

Draw-Activity "$diagramsDir\fig_activity_dept.png" "Fig 5.6.3: Flow Chart / Activity Diagram: Department Manager Flow" @(
    "Manager Login Authentication",
    "Access Department Portal",
    "View Department Staff List",
    "Inspect Daily Shift Attendance",
    "Receive Team Leave Requests",
    "Evaluate Leave Entitlements",
    "Approve / Reject Requests",
    "Submit Reason for Rejection",
    "Review Team Monthly Attendance"
)

Draw-Activity "$diagramsDir\fig_activity_admin.png" "Fig 5.6.4: Flow Chart / Activity Diagram: Super Admin Workflow" @(
    "Super Admin Authentication",
    "System Overview & Health",
    "Role Management (CRUD)",
    "Configure System Permissions",
    "Assign Roles to Users",
    "Activate / Suspend User",
    "Inspect Security Audit Logs",
    "Export Audit Report (CSV/PDF)",
    "Maintain Cloud Infrastructure"
)

# -------------------------------------------------------------
# 12. Fig 5.7.1: Entity Relationship Diagram (Database Schema)
# -------------------------------------------------------------
$c = New-Canvas 980 680
$titleFont = New-Object System.Drawing.Font("Arial", 13, [System.Drawing.FontStyle]::Bold)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::Navy)
$c.G.DrawString("Fig 5.7.1: Entity Relationship Diagram (ERD - Database Schema)", $titleFont, $titleBrush, 20, 15)

function Draw-ERTable ($g, $x, $y, $w, $name, $cols) {
    $headerH = 24
    $rowH = 16
    $totalH = $headerH + ($cols.Length * $rowH) + 6
    
    $bgBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#FAFAFA"))
    $borderPen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#1B365D"), 1.5)
    $g.FillRectangle($bgBrush, $x, $y, $w, $totalH)
    $g.DrawRectangle($borderPen, $x, $y, $w, $totalH)
    
    $hdrBg = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml("#1B365D"))
    $g.FillRectangle($hdrBg, $x, $y, $w, $headerH)
    
    $titleFont = New-Object System.Drawing.Font("Arial", 9, [System.Drawing.FontStyle]::Bold)
    $titleWhite = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::Center
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Center
    $g.DrawString($name, $titleFont, $titleWhite, ($x + $w/2), ($y + $headerH/2), $sf)
    
    $colFont = New-Object System.Drawing.Font("Consolas", 7.5, [System.Drawing.FontStyle]::Regular)
    $bodyBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(40, 40, 40))
    $keyBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::DarkRed)
    
    $cy = $y + $headerH + 4
    foreach ($col in $cols) {
        if ($col -match "^PK" -or $col -match "^FK") {
            $g.DrawString($col, $colFont, $keyBrush, ($x + 6), $cy)
        } else {
            $g.DrawString($col, $colFont, $bodyBrush, ($x + 6), $cy)
        }
        $cy += $rowH
    }
}

Draw-ERTable $c.G 30 50 170 "USERS" @("PK id : bigint", "name : string", "email : string", "password : string", "status : enum", "created_at : timestamp")
Draw-ERTable $c.G 240 50 160 "ROLES" @("PK id : bigint", "name : string", "slug : string", "is_active : boolean")
Draw-ERTable $c.G 440 50 160 "ROLE_USER" @("PK id : bigint", "FK user_id : bigint", "FK role_id : bigint")
Draw-ERTable $c.G 640 50 160 "PERMISSIONS" @("PK id : bigint", "name : string", "slug : string")
Draw-ERTable $c.G 830 50 130 "PERMISSION_ROLE" @("PK id : bigint", "FK role_id", "FK perm_id")

Draw-ERTable $c.G 30 230 180 "DEPARTMENTS" @("PK id : bigint", "name : string", "code : string", "description : text", "is_active : boolean")
Draw-ERTable $c.G 250 230 180 "DESIGNATIONS" @("PK id : bigint", "FK dept_id : bigint", "title : string", "code : string")
Draw-ERTable $c.G 470 230 220 "EMPLOYEES" @("PK id : bigint", "FK user_id : bigint", "FK dept_id : bigint", "FK desig_id : bigint", "employee_code : string", "phone : string", "joining_date : date", "status : enum")
Draw-ERTable $c.G 730 230 220 "ATTENDANCES" @("PK id : bigint", "FK employee_id : bigint", "attendance_date : date", "check_in : time", "check_out : time", "worked_minutes : int", "status : enum")

Draw-ERTable $c.G 30 460 180 "LEAVE_TYPES" @("PK id : bigint", "name : string", "code : string", "default_days : int")
Draw-ERTable $c.G 250 460 220 "LEAVE_REQUESTS" @("PK id : bigint", "FK employee_id : bigint", "FK leave_type_id : bigint", "start_date : date", "end_date : date", "total_days : decimal", "status : enum", "approved_by : bigint")
Draw-ERTable $c.G 510 460 210 "PAYROLL_RUNS" @("PK id : bigint", "title : string", "pay_period : string", "start_date : date", "end_date : date", "status : enum")
Draw-ERTable $c.G 760 460 200 "PAYSLIPS" @("PK id : bigint", "FK payroll_run_id", "FK employee_id", "basic_salary : decimal", "allowances : decimal", "deductions : decimal", "net_salary : decimal", "status : enum")

$relPen = New-Object System.Drawing.Pen([System.Drawing.ColorTranslator]::FromHtml("#006699"), 1.5)
$c.G.DrawLine($relPen, 200, 90, 440, 90)
$c.G.DrawLine($relPen, 400, 90, 440, 90)
$c.G.DrawLine($relPen, 400, 110, 830, 110)
$c.G.DrawLine($relPen, 800, 90, 830, 90)

$c.G.DrawLine($relPen, 120, 175, 470, 260)
$c.G.DrawLine($relPen, 210, 270, 250, 270)
$c.G.DrawLine($relPen, 430, 270, 470, 270)
$c.G.DrawLine($relPen, 690, 270, 730, 270)

$c.G.DrawLine($relPen, 210, 500, 250, 500)
$c.G.DrawLine($relPen, 580, 400, 470, 460)
$c.G.DrawLine($relPen, 720, 500, 760, 500)
$c.G.DrawLine($relPen, 690, 360, 760, 480)

$c.Bmp.Save("$diagramsDir\fig_erd.png", [System.Drawing.Imaging.ImageFormat]::Png)
$c.G.Dispose()
$c.Bmp.Dispose()

Write-Output "All 12 core architecture & UML diagrams generated successfully in $diagramsDir"
