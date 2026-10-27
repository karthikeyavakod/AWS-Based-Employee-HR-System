@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Reports</h1>
            <p class="text-body-secondary mb-0">Read-only HRMS reports for people, attendance, leave, and payroll data.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="report-card-icon d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary">
                            <i class="bi bi-person-lines-fill"></i>
                        </span>
                        <h2 class="h5 mb-0">Employee Report</h2>
                    </div>
                    <p class="text-body-secondary flex-grow-1">Review employee records by department, designation, and status.</p>
                    <a href="{{ route('admin.reports.employees') }}" class="btn btn-outline-primary mt-2">
                        Open Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="report-card-icon d-inline-flex align-items-center justify-content-center bg-success-subtle text-success">
                            <i class="bi bi-calendar-check"></i>
                        </span>
                        <h2 class="h5 mb-0">Attendance Report</h2>
                    </div>
                    <p class="text-body-secondary flex-grow-1">Filter employee attendance by date range, employee, and status.</p>
                    <a href="{{ route('admin.reports.attendance') }}" class="btn btn-outline-primary mt-2">
                        Open Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="report-card-icon d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning">
                            <i class="bi bi-calendar2-week"></i>
                        </span>
                        <h2 class="h5 mb-0">Leave Report</h2>
                    </div>
                    <p class="text-body-secondary flex-grow-1">Review leave requests by employee, date range, and status.</p>
                    <a href="{{ route('admin.reports.leaves') }}" class="btn btn-outline-primary mt-2">
                        Open Report
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="report-card-icon d-inline-flex align-items-center justify-content-center bg-info-subtle text-info">
                            <i class="bi bi-cash-stack"></i>
                        </span>
                        <h2 class="h5 mb-0">Payroll Report</h2>
                    </div>
                    <p class="text-body-secondary flex-grow-1">View payroll records by employee, period, year, and payment status.</p>
                    <a href="{{ route('admin.reports.payrolls') }}" class="btn btn-outline-primary mt-2">
                        Open Report
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
