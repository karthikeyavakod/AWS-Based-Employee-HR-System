@extends('layouts.app')

@section('title', 'Request Attendance Correction')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Request Attendance Correction</h1>
            <p class="text-body-secondary mb-0">Submit a correction request for an employee's attendance.</p>
        </div>

        <a href="{{ route('admin.attendance-corrections.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.attendance-corrections.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                        <select id="employee_id" name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">Select employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->user?->name ?? trim($employee->first_name . ' ' . $employee->last_name) }} ({{ $employee->employee_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="attendance_date" class="form-label">Attendance Date <span class="text-danger">*</span></label>
                        <input type="date" id="attendance_date" name="attendance_date" value="{{ old('attendance_date') }}" class="form-control @error('attendance_date') is-invalid @enderror" required>
                        @error('attendance_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="requested_check_in_at" class="form-label">Requested Check-in</label>
                        <input type="datetime-local" id="requested_check_in_at" name="requested_check_in_at" value="{{ old('requested_check_in_at') }}" class="form-control @error('requested_check_in_at') is-invalid @enderror">
                        @error('requested_check_in_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="requested_check_out_at" class="form-label">Requested Check-out</label>
                        <input type="datetime-local" id="requested_check_out_at" name="requested_check_out_at" value="{{ old('requested_check_out_at') }}" class="form-control @error('requested_check_out_at') is-invalid @enderror">
                        @error('requested_check_out_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="requested_status" class="form-label">Requested Status</label>
                        <select id="requested_status" name="requested_status" class="form-select @error('requested_status') is-invalid @enderror">
                            <option value="">Select status</option>
                            <option value="present" {{ old('requested_status') === 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ old('requested_status') === 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="late" {{ old('requested_status') === 'late' ? 'selected' : '' }}>Late</option>
                            <option value="half_day" {{ old('requested_status') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                        @error('requested_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea id="reason" name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.attendance-corrections.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Correction</button>
                </div>
            </form>
        </div>
    </div>
@endsection