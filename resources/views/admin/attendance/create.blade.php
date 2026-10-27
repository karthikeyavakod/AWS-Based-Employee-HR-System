@extends('layouts.app')

@section('title', 'Create Attendance')

@section('content')
    @php
        $hasEmployee = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'employee_id');
        $hasAttendanceDate = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'attendance_date');
        $hasDate = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'date');
        $hasCheckInAt = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_in_at');
        $hasCheckIn = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_in');
        $hasCheckOutAt = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_out_at');
        $hasCheckOut = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'check_out');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'status');
        $hasRemarks = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'remarks');
        $hasNote = Illuminate\Support\Facades\Schema::hasColumn('attendances', 'note');
        $dateColumn = $hasAttendanceDate ? 'attendance_date' : 'date';
        $checkInColumn = $hasCheckInAt ? 'check_in_at' : 'check_in';
        $checkOutColumn = $hasCheckOutAt ? 'check_out_at' : 'check_out';
        $noteColumn = $hasRemarks ? 'remarks' : 'note';
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Create Attendance</h1>
            <p class="text-body-secondary mb-0">Record attendance for an employee.</p>
        </div>

        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.attendance.store') }}">
                @csrf

                <div class="row g-3 mb-3">
                    @if ($hasEmployee)
                        <div class="col-12 col-lg-6">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select id="employee_id" name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                <option value="">Select employee</option>
                                @foreach ($employees as $employee)
                                    @php
                                        $employeeName = $employee->user?->name
                                            ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                    @endphp
                                    <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>
                                        {{ $employeeName ?: 'Employee #'.$employee->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasAttendanceDate || $hasDate)
                        <div class="col-12 col-lg-6">
                            <label for="{{ $dateColumn }}" class="form-label">{{ $hasAttendanceDate ? 'Attendance Date' : 'Date' }} <span class="text-danger">*</span></label>
                            <input
                                id="{{ $dateColumn }}"
                                type="date"
                                name="{{ $dateColumn }}"
                                value="{{ old($dateColumn) }}"
                                class="form-control @error($dateColumn) is-invalid @enderror"
                                required
                            >
                            @error($dateColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-3">
                    @if ($hasCheckInAt || $hasCheckIn)
                        <div class="col-12 col-lg-6">
                            <label for="{{ $checkInColumn }}" class="form-label">Check In</label>
                            <input
                                id="{{ $checkInColumn }}"
                                type="datetime-local"
                                name="{{ $checkInColumn }}"
                                value="{{ old($checkInColumn) }}"
                                class="form-control @error($checkInColumn) is-invalid @enderror"
                            >
                            @error($checkInColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasCheckOutAt || $hasCheckOut)
                        <div class="col-12 col-lg-6">
                            <label for="{{ $checkOutColumn }}" class="form-label">Check Out</label>
                            <input
                                id="{{ $checkOutColumn }}"
                                type="datetime-local"
                                name="{{ $checkOutColumn }}"
                                value="{{ old($checkOutColumn) }}"
                                class="form-control @error($checkOutColumn) is-invalid @enderror"
                            >
                            @error($checkOutColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-4">
                    @if ($hasStatus)
                        <div class="col-12 col-lg-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="present" @selected(old('status', 'present') === 'present')>Present</option>
                                <option value="absent" @selected(old('status') === 'absent')>Absent</option>
                                <option value="late" @selected(old('status') === 'late')>Late</option>
                                <option value="half_day" @selected(old('status') === 'half_day')>Half Day</option>
                                <option value="on_leave" @selected(old('status') === 'on_leave')>On Leave</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                @if ($hasRemarks || $hasNote)
                    <div class="mb-4">
                        <label for="{{ $noteColumn }}" class="form-label">{{ $hasRemarks ? 'Remarks' : 'Note' }}</label>
                        <textarea
                            id="{{ $noteColumn }}"
                            name="{{ $noteColumn }}"
                            rows="4"
                            class="form-control @error($noteColumn) is-invalid @enderror"
                        >{{ old($noteColumn) }}</textarea>
                        @error($noteColumn)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Attendance</button>
                </div>
            </form>
        </div>
    </div>
@endsection
