@extends('layouts.app')

@section('title', 'My HR Dashboard')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">My HR Dashboard</h1>
            <p class="text-body-secondary mb-0">Your profile, attendance, and leave information.</p>
        </div>
        <a href="{{ route('employee.profile.edit') }}" class="btn btn-outline-primary">
            <i class="bi bi-person-gear me-1"></i> Update my profile
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4">
            <section class="card h-100">
                <div class="card-body">
                    <h2 class="h5">My profile</h2>
                    <p class="mb-1 fw-semibold">{{ $employee->user?->name ?? trim($employee->first_name.' '.$employee->last_name) }}</p>
                    <p class="small text-body-secondary mb-2">{{ $employee->user?->email }}</p>
                    <div class="small">Employee ID: <code>{{ $employee->employee_code ?? $employee->id }}</code></div>
                    <div class="small">Department: {{ $employee->department?->name ?? 'N/A' }}</div>
                    <div class="small">Designation: {{ $employee->designation?->name ?? 'N/A' }}</div>
                </div>
            </section>
        </div>
        <div class="col-12 col-lg-4">
            <section class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Attendance this month</h2>
                    <p class="display-6 mb-0">{{ $attendanceCount }}</p>
                    <p class="small text-body-secondary mb-3">Recent records are listed below.</p>
                    @if ($todayAttendance === null)
                        <form method="POST" action="{{ route('employee.attendance.check-in') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">Check in</button>
                        </form>
                    @elseif ($todayAttendance->check_out_at === null)
                        <form method="POST" action="{{ route('employee.attendance.check-out') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm">Check out</button>
                        </form>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Attendance complete</span>
                    @endif
                </div>
            </section>
        </div>
        <div class="col-12 col-lg-4">
            <section class="card h-100">
                <div class="card-body">
                    <h2 class="h5">Leave balances</h2>
                    @forelse ($leaveBalances as $balance)
                        <div class="d-flex justify-content-between gap-2 small">
                            <span>{{ $balance->leave_type ?? 'Leave' }}</span>
                            <span>{{ number_format((float) $balance->remaining_days, 2) }} days remaining</span>
                        </div>
                    @empty
                        <p class="small text-body-secondary mb-0">No leave balances are available for this year.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <section class="card h-100">
                <div class="card-header">
                    <h2 class="h5 mb-0">My recent attendance</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Status</th><th>Check in</th><th>Check out</th></tr></thead>
                        <tbody>
                            @forelse ($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->attendance_date->format('M d, Y') }}</td>
                                    <td>{{ ucfirst($attendance->status) }}</td>
                                    <td>{{ $attendance->check_in_at?->format('h:i A') ?? '—' }}</td>
                                    <td>{{ $attendance->check_out_at?->format('h:i A') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-body-secondary py-4">No attendance records this month.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-12 col-xl-6">
            <section class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">My leave requests</h2>
                    @if (auth()->user()?->hasPermission('leave.create'))
                        <a href="{{ route('admin.leaves.create') }}" class="btn btn-sm btn-outline-primary">Request leave</a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($leaveRequests as $leaveRequest)
                                <tr>
                                    <td>{{ $leaveRequest->start_date->format('M d, Y') }} – {{ $leaveRequest->end_date->format('M d, Y') }}</td>
                                    <td>{{ number_format((float) $leaveRequest->total_days, 2) }}</td>
                                    <td>{{ ucfirst($leaveRequest->status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-body-secondary py-4">No leave requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    @if ($payslips->isNotEmpty())
        <section class="card mt-4">
            <div class="card-header">
                <h2 class="h5 mb-0">My paid payslips</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Pay period</th><th>Payment date</th><th>Net salary</th></tr></thead>
                    <tbody>
                        @foreach ($payslips as $payslip)
                            <tr>
                                <td>{{ sprintf('%02d/%d', $payslip->period_month, $payslip->period_year) }}</td>
                                <td>{{ $payslip->payment_date ? \Illuminate\Support\Carbon::parse($payslip->payment_date)->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ number_format((float) $payslip->net_salary, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
