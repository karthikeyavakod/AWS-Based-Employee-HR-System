@extends('layouts.app')

@section('title', 'Create Leave Request')

@section('content')
    @php
        $hasEmployee = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'employee_id');
        $hasLeaveTypeId = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'leave_type_id');
        $hasLeaveType = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'leave_type');
        $hasType = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'type');
        $hasStartDate = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'start_date');
        $hasEndDate = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'end_date');
        $hasTotalDays = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'total_days');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'status');
        $hasReason = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'reason');
        $hasRemarks = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'remarks');
        $hasNote = Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'note');
        $typeColumn = $hasLeaveType ? 'leave_type' : 'type';
        $noteColumn = $hasRemarks ? 'remarks' : 'note';
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Create Leave Request</h1>
            <p class="text-body-secondary mb-0">Submit a leave request for an employee.</p>
        </div>

        <a href="{{ route('admin.leaves.index') }}" class="btn btn-outline-secondary">
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
            <form method="POST" action="{{ route('admin.leaves.store') }}">
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

                    @if ($hasLeaveTypeId)
                        <div class="col-12 col-lg-6">
                            <label for="leave_type_id" class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select id="leave_type_id" name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                                <option value="">Select leave type</option>
                                @foreach ($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}" @selected((string) old('leave_type_id') === (string) $leaveType->id)>
                                        {{ $leaveType->name ?? $leaveType->code ?? 'Leave Type #'.$leaveType->id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @elseif ($hasLeaveType || $hasType)
                        <div class="col-12 col-lg-6">
                            <label for="{{ $typeColumn }}" class="form-label">Leave Type</label>
                            <input
                                id="{{ $typeColumn }}"
                                type="text"
                                name="{{ $typeColumn }}"
                                value="{{ old($typeColumn) }}"
                                class="form-control @error($typeColumn) is-invalid @enderror"
                            >
                            @error($typeColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-3">
                    @if ($hasStartDate)
                        <div class="col-12 col-lg-6">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input
                                id="start_date"
                                type="date"
                                name="start_date"
                                value="{{ old('start_date') }}"
                                class="form-control @error('start_date') is-invalid @enderror"
                                required
                            >
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasEndDate)
                        <div class="col-12 col-lg-6">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input
                                id="end_date"
                                type="date"
                                name="end_date"
                                value="{{ old('end_date') }}"
                                class="form-control @error('end_date') is-invalid @enderror"
                                required
                            >
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-4">
                    @if ($hasTotalDays)
                        <div class="col-12 col-lg-6">
                            <label for="total_days" class="form-label">Total Days</label>
                            <input
                                id="total_days"
                                type="number"
                                step="0.5"
                                min="0.5"
                                name="total_days"
                                value="{{ old('total_days') }}"
                                class="form-control @error('total_days') is-invalid @enderror"
                                readonly
                            >
                            <div class="form-text">Leave blank to calculate from the selected dates.</div>
                            @error('total_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasStatus)
                        <div class="col-12 col-lg-6">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
                                <option value="approved" @selected(old('status') === 'approved')>Approved</option>
                                <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
                                <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                @if ($hasReason)
                    <div class="mb-4">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea
                            id="reason"
                            name="reason"
                            rows="4"
                            class="form-control @error('reason') is-invalid @enderror"
                        >{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

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
                    <a href="{{ route('admin.leaves.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Leave Request</button>
                </div>
            </form>
        </div>
    </div>
@endsection
