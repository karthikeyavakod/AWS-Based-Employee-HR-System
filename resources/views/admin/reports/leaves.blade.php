@extends('layouts.app')

@section('title', 'Leave Report')

@section('content')
    @php
        $filters = $filters ?? [];
        $employees = collect($employees ?? []);
        $hasEmployee = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'employee_id') && method_exists(App\Models\LeaveRequest::class, 'employee');
        $hasLeaveTypeId = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'leave_type_id');
        $hasLeaveType = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'leave_type');
        $hasType = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'type');
        $hasStartDate = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'start_date');
        $hasEndDate = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'end_date');
        $hasTotalDays = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'total_days');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'status');
        $hasReason = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'reason');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Leave Report</h1>
            <p class="text-body-secondary mb-0">Read-only leave request data filtered by employee, dates, and status.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Reports
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.leaves') }}" class="row g-3 align-items-end">
                @if ($employees->isNotEmpty())
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select id="employee_id" name="employee_id" class="form-select">
                            <option value="">All employees</option>
                            @foreach ($employees as $employee)
                                @php
                                    $employeeName = $employee->user?->name ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                @endphp
                                <option value="{{ $employee->id }}" @selected((string) ($filters['employee_id'] ?? '') === (string) $employee->id)>
                                    {{ $employeeName ?: 'Employee #'.$employee->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control">
                </div>

                @if ($hasStatus)
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" id="status" name="status" value="{{ $filters['status'] ?? '' }}" class="form-control" placeholder="pending">
                    </div>
                @endif

                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.leaves') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            @if ($hasEmployee)
                                <th scope="col">Employee</th>
                            @endif
                            @if ($hasLeaveTypeId || $hasLeaveType || $hasType)
                                <th scope="col">Leave Type</th>
                            @endif
                            @if ($hasStartDate)
                                <th scope="col">Start Date</th>
                            @endif
                            @if ($hasEndDate)
                                <th scope="col">End Date</th>
                            @endif
                            @if ($hasTotalDays)
                                <th scope="col">Total Days</th>
                            @endif
                            @if ($hasStatus)
                                <th scope="col">Status</th>
                            @endif
                            @if ($hasReason)
                                <th scope="col">Reason</th>
                            @endif
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaveRequests as $leaveRequest)
                            @php
                                $employee = $hasEmployee ? $leaveRequest->employee : null;
                                $employeeName = $employee?->user?->name ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $leaveTypeName = ($hasLeaveType ? $leaveRequest->leave_type : null)
                                    ?? ($hasType ? $leaveRequest->type : null)
                                    ?? ($hasLeaveTypeId ? 'Type #'.$leaveRequest->leave_type_id : null);
                            @endphp
                            <tr>
                                @if ($hasEmployee)
                                    <td class="fw-semibold">{{ $employeeName ?: 'N/A' }}</td>
                                @endif
                                @if ($hasLeaveTypeId || $hasLeaveType || $hasType)
                                    <td>{{ $leaveTypeName ?: 'N/A' }}</td>
                                @endif
                                @if ($hasStartDate)
                                    <td>{{ $leaveRequest->start_date ? Illuminate\Support\Carbon::parse($leaveRequest->start_date)->format('M d, Y') : 'N/A' }}</td>
                                @endif
                                @if ($hasEndDate)
                                    <td>{{ $leaveRequest->end_date ? Illuminate\Support\Carbon::parse($leaveRequest->end_date)->format('M d, Y') : 'N/A' }}</td>
                                @endif
                                @if ($hasTotalDays)
                                    <td>{{ $leaveRequest->total_days ?? 'N/A' }}</td>
                                @endif
                                @if ($hasStatus)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst(str_replace('_', ' ', (string) $leaveRequest->status)) }}
                                        </span>
                                    </td>
                                @endif
                                @if ($hasReason)
                                    <td>{{ Illuminate\Support\Str::limit((string) $leaveRequest->reason, 48) ?: 'N/A' }}</td>
                                @endif
                                <td>{{ $leaveRequest->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 1 + ($hasEmployee ? 1 : 0) + (($hasLeaveTypeId || $hasLeaveType || $hasType) ? 1 : 0) + ($hasStartDate ? 1 : 0) + ($hasEndDate ? 1 : 0) + ($hasTotalDays ? 1 : 0) + ($hasStatus ? 1 : 0) + ($hasReason ? 1 : 0) }}" class="text-center text-body-secondary py-5">
                                    No leave records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
