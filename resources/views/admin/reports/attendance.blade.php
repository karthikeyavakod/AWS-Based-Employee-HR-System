@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
    @php
        $filters = $filters ?? [];
        $employees = collect($employees ?? []);
        $hasEmployee = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'employee_id') && method_exists(App\Models\Attendance::class, 'employee');
        $hasAttendanceDate = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'attendance_date');
        $hasDate = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'date');
        $hasCheckInAt = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_in_at');
        $hasCheckIn = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_in');
        $hasCheckOutAt = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_out_at');
        $hasCheckOut = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_out');
        $hasWorkMinutes = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'work_minutes');
        $hasWorkingHours = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'working_hours');
        $hasTotalHours = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'total_hours');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'status');
        $hasRemarks = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'remarks');
        $hasNote = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'note');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance Report</h1>
            <p class="text-body-secondary mb-0">Read-only attendance records filtered by employee, date range, and status.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Reports
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.attendance') }}" class="row g-3 align-items-end">
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
                        <input type="text" id="status" name="status" value="{{ $filters['status'] ?? '' }}" class="form-control" placeholder="present">
                    </div>
                @endif

                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.attendance') }}" class="btn btn-outline-secondary">Reset</a>
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
                            @if ($hasAttendanceDate || $hasDate)
                                <th scope="col">Date</th>
                            @endif
                            @if ($hasCheckInAt || $hasCheckIn)
                                <th scope="col">Check In</th>
                            @endif
                            @if ($hasCheckOutAt || $hasCheckOut)
                                <th scope="col">Check Out</th>
                            @endif
                            @if ($hasWorkMinutes || $hasWorkingHours || $hasTotalHours)
                                <th scope="col">Working Hours</th>
                            @endif
                            @if ($hasStatus)
                                <th scope="col">Status</th>
                            @endif
                            @if ($hasRemarks || $hasNote)
                                <th scope="col">{{ $hasRemarks ? 'Remarks' : 'Note' }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            @php
                                $employee = $hasEmployee ? $attendance->employee : null;
                                $employeeName = $employee?->user?->name ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $dateColumn = $hasAttendanceDate ? 'attendance_date' : 'date';
                                $checkInColumn = $hasCheckInAt ? 'check_in_at' : 'check_in';
                                $checkOutColumn = $hasCheckOutAt ? 'check_out_at' : 'check_out';
                                $hoursColumn = $hasWorkMinutes ? 'work_minutes' : ($hasWorkingHours ? 'working_hours' : 'total_hours');
                                $noteColumn = $hasRemarks ? 'remarks' : 'note';
                            @endphp
                            <tr>
                                @if ($hasEmployee)
                                    <td class="fw-semibold">{{ $employeeName ?: 'N/A' }}</td>
                                @endif
                                @if ($hasAttendanceDate || $hasDate)
                                    <td>{{ $attendance->{$dateColumn} ? Illuminate\Support\Carbon::parse($attendance->{$dateColumn})->format('M d, Y') : 'N/A' }}</td>
                                @endif
                                @if ($hasCheckInAt || $hasCheckIn)
                                    <td>{{ $attendance->{$checkInColumn} ? Illuminate\Support\Carbon::parse($attendance->{$checkInColumn})->format('M d, Y h:i A') : 'N/A' }}</td>
                                @endif
                                @if ($hasCheckOutAt || $hasCheckOut)
                                    <td>{{ $attendance->{$checkOutColumn} ? Illuminate\Support\Carbon::parse($attendance->{$checkOutColumn})->format('M d, Y h:i A') : 'N/A' }}</td>
                                @endif
                                @if ($hasWorkMinutes || $hasWorkingHours || $hasTotalHours)
                                    <td>
                                        @if ($hasWorkMinutes)
                                            {{ $attendance->work_minutes !== null ? number_format($attendance->work_minutes / 60, 2) : 'N/A' }}
                                        @else
                                            {{ $attendance->{$hoursColumn} ?? 'N/A' }}
                                        @endif
                                    </td>
                                @endif
                                @if ($hasStatus)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst(str_replace('_', ' ', (string) $attendance->status)) }}
                                        </span>
                                    </td>
                                @endif
                                @if ($hasRemarks || $hasNote)
                                    <td>{{ Illuminate\Support\Str::limit((string) $attendance->{$noteColumn}, 48) ?: 'N/A' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($hasEmployee ? 1 : 0) + (($hasAttendanceDate || $hasDate) ? 1 : 0) + (($hasCheckInAt || $hasCheckIn) ? 1 : 0) + (($hasCheckOutAt || $hasCheckOut) ? 1 : 0) + (($hasWorkMinutes || $hasWorkingHours || $hasTotalHours) ? 1 : 0) + ($hasStatus ? 1 : 0) + (($hasRemarks || $hasNote) ? 1 : 0) }}" class="text-center text-body-secondary py-5">
                                    No attendance records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
