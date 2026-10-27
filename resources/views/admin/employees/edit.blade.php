@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
    @php
        $hasUser = Illuminate\Support\Facades\Schema::hasColumn('employees', 'user_id');
        $hasDepartment = Illuminate\Support\Facades\Schema::hasColumn('employees', 'department_id');
        $hasDesignation = Illuminate\Support\Facades\Schema::hasColumn('employees', 'designation_id');
        $hasEmployeeCode = Illuminate\Support\Facades\Schema::hasColumn('employees', 'employee_code');
        $hasCode = Illuminate\Support\Facades\Schema::hasColumn('employees', 'code');
        $hasPhone = Illuminate\Support\Facades\Schema::hasColumn('employees', 'phone');
        $hasAddress = Illuminate\Support\Facades\Schema::hasColumn('employees', 'address');
        $hasJoiningDate = Illuminate\Support\Facades\Schema::hasColumn('employees', 'joining_date');
        $hasDateOfBirth = Illuminate\Support\Facades\Schema::hasColumn('employees', 'date_of_birth');
        $hasGender = Illuminate\Support\Facades\Schema::hasColumn('employees', 'gender');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('employees', 'status');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Employee</h1>
            <p class="text-body-secondary mb-0">Update {{ $employee->user?->name ?? trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')) }}.</p>
        </div>

        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
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
            <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
                @csrf
                @method('PUT')

                @if ($hasUser)
                    <h2 class="h5 mb-3">Account</h2>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name', $employee->user?->name) }}"
                                class="form-control @error('name') is-invalid @enderror"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-lg-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', $employee->user?->email) }}"
                                class="form-control @error('email') is-invalid @enderror"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                        >
                        <div class="form-text">Leave blank to keep current password.</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <h2 class="h5 mb-3">Employee Details</h2>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-lg-6">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input
                            id="first_name"
                            type="text"
                            name="first_name"
                            value="{{ old('first_name', $employee->first_name) }}"
                            class="form-control @error('first_name') is-invalid @enderror"
                            required
                        >
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input
                            id="last_name"
                            type="text"
                            name="last_name"
                            value="{{ old('last_name', $employee->last_name) }}"
                            class="form-control @error('last_name') is-invalid @enderror"
                            required
                        >
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    @if ($hasDepartment)
                        <div class="col-12 col-lg-6">
                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select id="department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string) old('department_id', $employee->department_id) === (string) $department->id)>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasDesignation)
                        <div class="col-12 col-lg-6">
                            <label for="designation_id" class="form-label">Designation <span class="text-danger">*</span></label>
                            <select id="designation_id" name="designation_id" class="form-select @error('designation_id') is-invalid @enderror" required>
                                <option value="">Select designation</option>
                                @foreach ($designations as $designation)
                                    <option value="{{ $designation->id }}" @selected((string) old('designation_id', $employee->designation_id) === (string) $designation->id)>
                                        {{ $designation->name }} @if ($designation->department) - {{ $designation->department->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('designation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-3">
                    @if ($hasEmployeeCode || $hasCode)
                        @php
                            $identifierColumn = $hasEmployeeCode ? 'employee_code' : 'code';
                        @endphp
                        <div class="col-12 col-lg-6">
                            <label for="{{ $identifierColumn }}" class="form-label">{{ $hasEmployeeCode ? 'Employee Code' : 'Code' }}</label>
                            <input
                                id="{{ $identifierColumn }}"
                                type="text"
                                name="{{ $identifierColumn }}"
                                value="{{ old($identifierColumn, $employee->{$identifierColumn}) }}"
                                class="form-control @error($identifierColumn) is-invalid @enderror"
                            >
                            <div class="form-text">Leave blank to regenerate it from the employee name.</div>
                            @error($identifierColumn)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasPhone)
                        <div class="col-12 col-lg-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input
                                id="phone"
                                type="text"
                                name="phone"
                                value="{{ old('phone', $employee->phone) }}"
                                class="form-control @error('phone') is-invalid @enderror"
                            >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-3">
                    @if ($hasJoiningDate)
                        <div class="col-12 col-lg-6">
                            <label for="joining_date" class="form-label">Joining Date <span class="text-danger">*</span></label>
                            <input
                                id="joining_date"
                                type="date"
                                name="joining_date"
                                value="{{ old('joining_date', $employee->joining_date) }}"
                                class="form-control @error('joining_date') is-invalid @enderror"
                                required
                            >
                            @error('joining_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasDateOfBirth)
                        <div class="col-12 col-lg-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input
                                id="date_of_birth"
                                type="date"
                                name="date_of_birth"
                                value="{{ old('date_of_birth', $employee->date_of_birth) }}"
                                class="form-control @error('date_of_birth') is-invalid @enderror"
                            >
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="row g-3 mb-3">
                    @if ($hasGender)
                        <div class="col-12 col-lg-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Select gender</option>
                                <option value="male" @selected(old('gender', $employee->gender) === 'male')>Male</option>
                                <option value="female" @selected(old('gender', $employee->gender) === 'female')>Female</option>
                                <option value="other" @selected(old('gender', $employee->gender) === 'other')>Other</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if ($hasStatus)
                        <div class="col-12 col-lg-6">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" @selected(old('status', $employee->status) === 'active')>Active</option>
                                <option value="inactive" @selected(old('status', $employee->status) === 'inactive')>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>

                @if ($hasAddress)
                    <div class="mb-4">
                        <label for="address" class="form-label">Address</label>
                        <textarea
                            id="address"
                            name="address"
                            rows="4"
                            class="form-control @error('address') is-invalid @enderror"
                        >{{ old('address', $employee->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
@endsection
