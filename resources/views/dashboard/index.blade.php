@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $stats = $stats ?? [];
        $recentEmployees = collect($stats['recent_employees'] ?? []);
        $recentLeaveRequests = collect($stats['recent_leave_requests'] ?? []);
        $recentPayrolls = collect($stats['recent_payrolls'] ?? []);

        $totalEmployees = (int) ($stats['total_employees'] ?? 0);
        $totalDepartments = (int) ($stats['total_departments'] ?? 0);
        $totalDesignations = (int) ($stats['total_designations'] ?? 0);
        $todayAttendance = (int) ($stats['today_attendance_count'] ?? 0);
        $pendingLeaves = (int) ($stats['pending_leave_requests_count'] ?? 0);
        $currentMonthPayrolls = (int) ($stats['current_month_payrolls_count'] ?? 0);
        $attendanceRate = $totalEmployees > 0 ? min(100, round(($todayAttendance / $totalEmployees) * 100)) : 0;

        $summaryCards = [
            ['label' => 'Total Employees', 'value' => $totalEmployees, 'icon' => 'bi-person-vcard', 'tone' => 'teal', 'meta' => 'Active people records'],
            ['label' => 'Total Departments', 'value' => $totalDepartments, 'icon' => 'bi-diagram-3', 'tone' => 'success', 'meta' => 'Organization units'],
            ['label' => 'Total Designations', 'value' => $totalDesignations, 'icon' => 'bi-award', 'tone' => 'slate', 'meta' => 'Defined positions'],
            ['label' => 'Today Attendance', 'value' => $todayAttendance, 'icon' => 'bi-calendar-check', 'tone' => 'warning', 'meta' => 'Checked records today'],
            ['label' => 'Pending Leave Requests', 'value' => $pendingLeaves, 'icon' => 'bi-calendar2-week', 'tone' => 'danger', 'meta' => 'Awaiting review'],
            ['label' => 'Current Month Payrolls', 'value' => $currentMonthPayrolls, 'icon' => 'bi-cash-stack', 'tone' => 'teal', 'meta' => 'Payroll records this month'],
        ];

        $quickLinks = [
            ['label' => 'Employees', 'permission' => 'employee.view', 'route' => 'admin.employees.index', 'icon' => 'bi-person-vcard'],
            ['label' => 'Departments', 'permission' => 'department.view', 'route' => 'admin.departments.index', 'icon' => 'bi-diagram-3'],
            ['label' => 'Designations', 'permission' => 'designation.view', 'route' => 'admin.designations.index', 'icon' => 'bi-award'],
            ['label' => 'Attendance', 'permission' => 'attendance.view', 'route' => 'admin.attendance.index', 'icon' => 'bi-calendar-check'],
            ['label' => 'Leaves', 'permission' => 'leave.view', 'route' => 'admin.leaves.index', 'icon' => 'bi-calendar2-week'],
            ['label' => 'Payrolls', 'permission' => 'payroll.view', 'route' => 'admin.payrolls.index', 'icon' => 'bi-cash-stack'],
            ['label' => 'Roles', 'permission' => 'role.view', 'route' => 'admin.roles.index', 'icon' => 'bi-person-badge'],
            ['label' => 'Reports', 'permission' => 'report.view', 'route' => 'admin.reports.index', 'icon' => 'bi-bar-chart'],
        ];
    @endphp

    <div class="hr-card dashboard-hero p-4 p-xl-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="dashboard-hero-icon">
                        <i class="bi bi-speedometer2 fs-4"></i>
                    </span>
                    <div>
                        <h1 class="h2 fw-bold mb-1">Dashboard</h1>
                        <p class="text-body-secondary mb-0">
                            Welcome, {{ $user?->name ?? 'User' }}. HRMS overview and current system summary.
                        </p>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <span class="soft-badge">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ now()->format('M d, Y') }}
                    </span>
                    <span class="soft-badge">
                        <i class="bi bi-shield-check me-1"></i>
                        Permission-aware workspace
                    </span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="attendance-callout rounded-2 p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-semibold">Attendance Coverage</span>
                        <span class="fw-bold">{{ $attendanceRate }}%</span>
                    </div>
                    <div class="progress progress-thin" role="progressbar" aria-label="Attendance coverage" aria-valuenow="{{ $attendanceRate }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar progress-bar-dashboard" style="width: {{ $attendanceRate }}%;"></div>
                    </div>
                    <p class="small text-body-secondary mb-0 mt-3">
                        {{ number_format($todayAttendance) }} attendance records for {{ number_format($totalEmployees) }} employees today.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        @foreach ($summaryCards as $card)
            <div class="col-12 col-md-6 col-xl-4">
                @include('dashboard.partials.stat-card', ['card' => $card])
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-4">
            <div class="hr-card h-100 p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1">Attendance Summary</h2>
                        <p class="text-body-secondary mb-0">Today against current employee records.</p>
                    </div>
                    <span class="stat-icon stat-warning">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </span>
                </div>
                <div class="display-5 fw-bold mb-2">{{ $attendanceRate }}%</div>
                <div class="progress progress-thin mb-3" role="progressbar" aria-label="Today attendance summary" aria-valuenow="{{ $attendanceRate }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-dashboard" style="width: {{ $attendanceRate }}%;"></div>
                </div>
                <div class="d-flex justify-content-between text-body-secondary small">
                    <span>Attendance</span>
                    <span>{{ number_format($todayAttendance) }} / {{ number_format($totalEmployees) }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="hr-card h-100 p-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1">Quick Actions</h2>
                        <p class="text-body-secondary mb-0">Open modules available to your permissions.</p>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach ($quickLinks as $link)
                        @if ($user?->hasPermission($link['permission']) && Illuminate\Support\Facades\Route::has($link['route']))
                            <div class="col-12 col-sm-6 col-lg-4">
                                <a href="{{ route($link['route']) }}" class="quick-action d-flex align-items-center gap-3 p-3 h-100">
                                    <span class="sidebar-link-icon bg-light">
                                        <i class="bi {{ $link['icon'] }}"></i>
                                    </span>
                                    <span class="fw-semibold">{{ $link['label'] }}</span>
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="hr-card h-100">
                <div class="p-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Recent Employees</h2>
                            <p class="text-body-secondary mb-0">Latest employee records connected to departments and designations.</p>
                        </div>
                        <span class="soft-badge">{{ $recentEmployees->count() }} shown</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table dashboard-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentEmployees as $employee)
                                @php
                                    $employeeName = $employee->user?->name
                                        ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $employeeName ?: 'N/A' }}</td>
                                    <td>{{ $employee->department?->name ?? 'N/A' }}</td>
                                    <td>{{ $employee->designation?->name ?? 'N/A' }}</td>
                                    <td>{{ $employee->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-body-secondary py-4">
                                        No recent employees found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="d-flex flex-column gap-4">
                <div class="hr-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Recent Leave Requests</h2>
                            <p class="text-body-secondary mb-0">Latest leave activity.</p>
                        </div>
                        <span class="stat-icon stat-danger">
                            <i class="bi bi-calendar2-week fs-5"></i>
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($recentLeaveRequests as $leaveRequest)
                            @php
                                $employee = $leaveRequest->employee ?? null;
                                $employeeName = $employee?->user?->name
                                    ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $status = $leaveRequest->status ?? null;
                                $startDate = $leaveRequest->start_date ? \Illuminate\Support\Carbon::parse($leaveRequest->start_date)->format('M d, Y') : null;
                                $endDate = $leaveRequest->end_date ? \Illuminate\Support\Carbon::parse($leaveRequest->end_date)->format('M d, Y') : null;
                            @endphp
                            <div class="d-flex justify-content-between gap-3 border-bottom pb-3">
                                <div>
                                    <div class="fw-semibold">{{ $employeeName ?: 'N/A' }}</div>
                                    <div class="small text-body-secondary">{{ $startDate && $endDate ? $startDate.' - '.$endDate : 'N/A' }}</div>
                                </div>
                                <div>
                                    @if ($status)
                                        <span class="soft-badge">{{ ucfirst(str_replace('_', ' ', (string) $status)) }}</span>
                                    @else
                                        <span class="text-body-secondary small">N/A</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-body-secondary py-4">
                                No recent leave requests found.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="hr-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Recent Payrolls</h2>
                            <p class="text-body-secondary mb-0">Latest payroll records.</p>
                        </div>
                        <span class="stat-icon stat-success">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($recentPayrolls as $payroll)
                            @php
                                $employee = $payroll->employee ?? null;
                                $employeeName = $employee?->user?->name
                                    ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $payPeriod = $payroll->payroll_month
                                    ?? ($payroll->month ?? null)
                                    ?? ($payroll->period_month ?? null)
                                    ?? ($payroll->payroll_run_id ? 'Run #'.$payroll->payroll_run_id : null);
                                $payYear = $payroll->year ?? $payroll->period_year ?? null;
                                $status = $payroll->payment_status ?? $payroll->status ?? null;
                            @endphp
                            <div class="d-flex justify-content-between gap-3 border-bottom pb-3">
                                <div>
                                    <div class="fw-semibold">{{ $employeeName ?: 'N/A' }}</div>
                                    <div class="small text-body-secondary">{{ $payPeriod ? trim($payPeriod.' '.$payYear) : 'N/A' }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">{{ isset($payroll->net_salary) ? number_format((float) $payroll->net_salary, 2) : 'N/A' }}</div>
                                    <div class="small text-body-secondary">{{ $status ? ucfirst(str_replace('_', ' ', (string) $status)) : 'N/A' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-body-secondary py-4">
                                No recent payroll records found.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="hr-card p-4">
                    <h2 class="h5 fw-bold mb-3">Organization Snapshot</h2>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="rounded-2 bg-light p-3">
                                <div class="text-body-secondary small">Departments</div>
                                <div class="h4 mb-0">{{ number_format($totalDepartments) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-2 bg-light p-3">
                                <div class="text-body-secondary small">Designations</div>
                                <div class="h4 mb-0">{{ number_format($totalDesignations) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
