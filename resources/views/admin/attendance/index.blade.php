@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
    @php
        $user = auth()->user();
        $hasEmployee = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'employee_id') && method_exists(App\Models\Attendance::class, 'employee');
        $hasAttendanceDate = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'attendance_date');
        $hasDate = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'date');
        $hasCheckInAt = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_in_at');
        $hasCheckIn = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_in');
        $hasCheckOutAt = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_out_at');
        $hasCheckOut = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_out');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'status');
        $hasWorkMinutes = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'work_minutes');
        $hasWorkingHours = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'working_hours');
        $hasTotalHours = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'total_hours');
        $hasRemarks = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'remarks');
        $hasNote = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'note');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Attendance</h1>
            <p class="text-body-secondary mb-0">Manage employee attendance records.</p>
        </div>

        @if ($user?->hasPermission('attendance.create'))
            <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Attendance
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            @if ($hasEmployee)
                                <th scope="col">Employee</th>
                            @endif
                            @if ($hasAttendanceDate || $hasDate)
                                <th scope="col">{{ $hasAttendanceDate ? 'Attendance Date' : 'Date' }}</th>
                            @endif
                            @if ($hasCheckInAt || $hasCheckIn)
                                <th scope="col">Check In</th>
                            @endif
                            @if ($hasCheckOutAt || $hasCheckOut)
                                <th scope="col">Check Out</th>
                            @endif
                            @if ($hasStatus)
                                <th scope="col">Status</th>
                            @endif
                            @if ($hasWorkMinutes || $hasWorkingHours || $hasTotalHours)
                                <th scope="col">{{ $hasTotalHours ? 'Total Hours' : 'Working Hours' }}</th>
                            @endif
                            @if ($hasRemarks || $hasNote)
                                <th scope="col">{{ $hasRemarks ? 'Remarks' : 'Note' }}</th>
                            @endif
                            <th scope="col">Created at</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            @php
                                $employee = $hasEmployee ? $attendance->employee : null;
                                $employeeName = $employee?->user?->name
                                    ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $dateColumn = $hasAttendanceDate ? 'attendance_date' : 'date';
                                $checkInColumn = $hasCheckInAt ? 'check_in_at' : 'check_in';
                                $checkOutColumn = $hasCheckOutAt ? 'check_out_at' : 'check_out';
                                $hoursColumn = $hasWorkMinutes ? 'work_minutes' : ($hasWorkingHours ? 'working_hours' : 'total_hours');
                                $noteColumn = $hasRemarks ? 'remarks' : 'note';
                            @endphp
                            <tr>
                                <td class="text-body-secondary">{{ $attendance->id }}</td>
                                @if ($hasEmployee)
                                    <td class="fw-semibold">{{ $employeeName ?: 'N/A' }}</td>
                                @endif
                                @if ($hasAttendanceDate || $hasDate)
                                    <td>{{ $attendance->{$dateColumn} ? \Illuminate\Support\Carbon::parse($attendance->{$dateColumn})->format('M d, Y') : 'N/A' }}</td>
                                @endif
                                @if ($hasCheckInAt || $hasCheckIn)
                                    <td>{{ $attendance->{$checkInColumn} ? \Illuminate\Support\Carbon::parse($attendance->{$checkInColumn})->format('M d, Y h:i A') : 'N/A' }}</td>
                                @endif
                                @if ($hasCheckOutAt || $hasCheckOut)
                                    <td>{{ $attendance->{$checkOutColumn} ? \Illuminate\Support\Carbon::parse($attendance->{$checkOutColumn})->format('M d, Y h:i A') : 'N/A' }}</td>
                                @endif
                                @if ($hasStatus)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst((string) $attendance->status) }}
                                        </span>
                                    </td>
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
                                @if ($hasRemarks || $hasNote)
                                    <td>{{ \Illuminate\Support\Str::limit((string) $attendance->{$noteColumn}, 48) ?: 'N/A' }}</td>
                                @endif
                                <td>{{ $attendance->created_at?->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($user?->hasPermission('attendance.update'))
                                            <a href="{{ route('admin.attendance.edit', $attendance) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($user?->hasPermission('attendance.delete'))
                                            <form method="POST" action="{{ route('admin.attendance.destroy', $attendance) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="{{ 3 + ($hasEmployee ? 1 : 0) + (($hasAttendanceDate || $hasDate) ? 1 : 0) + (($hasCheckInAt || $hasCheckIn) ? 1 : 0) + (($hasCheckOutAt || $hasCheckOut) ? 1 : 0) + ($hasStatus ? 1 : 0) + (($hasWorkMinutes || $hasWorkingHours || $hasTotalHours) ? 1 : 0) + (($hasRemarks || $hasNote) ? 1 : 0) }}"
                                    class="text-center text-body-secondary py-5"
                                >
                                    No attendance records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($attendances->hasPages())
            <div class="card-footer bg-white">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
@endsection
