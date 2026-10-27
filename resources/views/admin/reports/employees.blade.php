@extends('layouts.app')

@section('title', 'Employee Report')

@section('content')
    @php
        $filters = $filters ?? [];
        $departments = collect($departments ?? []);
        $designations = collect($designations ?? []);
        $hasUser = Illuminate\Support\Facades\Schema::hasColumn('employees', 'user_id') && method_exists(App\Models\Employee::class, 'user');
        $hasDepartment = Illuminate\Support\Facades\Schema::hasColumn('employees', 'department_id') && method_exists(App\Models\Employee::class, 'department');
        $hasDesignation = Illuminate\Support\Facades\Schema::hasColumn('employees', 'designation_id') && method_exists(App\Models\Employee::class, 'designation');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('employees', 'status');
        $hasJoiningDate = Illuminate\Support\Facades\Schema::hasColumn('employees', 'joining_date');
        $dateLabel = $hasJoiningDate ? 'Joined Date' : 'Created At';
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Employee Report</h1>
            <p class="text-body-secondary mb-0">Read-only employee records with department and designation filters.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Reports
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.employees') }}" class="row g-3 align-items-end">
                @if ($departments->isNotEmpty())
                    <div class="col-md-4">
                        <label for="department_id" class="form-label">Department</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $department->id)>
                                    {{ $department->name ?? $department->code ?? 'Department #'.$department->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($designations->isNotEmpty())
                    <div class="col-md-4">
                        <label for="designation_id" class="form-label">Designation</label>
                        <select id="designation_id" name="designation_id" class="form-select">
                            <option value="">All designations</option>
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}" @selected((string) ($filters['designation_id'] ?? '') === (string) $designation->id)>
                                    {{ $designation->name ?? $designation->code ?? 'Designation #'.$designation->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($hasStatus)
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <input type="text" id="status" name="status" value="{{ $filters['status'] ?? '' }}" class="form-control" placeholder="active">
                    </div>
                @endif

                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.reports.employees') }}" class="btn btn-outline-secondary">Reset</a>
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
                            <th scope="col">Employee ID</th>
                            <th scope="col">Name</th>
                            @if ($hasUser)
                                <th scope="col">Email</th>
                            @endif
                            @if ($hasDepartment)
                                <th scope="col">Department</th>
                            @endif
                            @if ($hasDesignation)
                                <th scope="col">Designation</th>
                            @endif
                            @if ($hasStatus)
                                <th scope="col">Status</th>
                            @endif
                            <th scope="col">{{ $dateLabel }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            @php
                                $employeeName = $hasUser && $employee->user
                                    ? $employee->user->name
                                    : trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $dateValue = $hasJoiningDate ? $employee->joining_date : $employee->created_at;
                            @endphp
                            <tr>
                                <td class="text-body-secondary">{{ $employee->employee_code ?? $employee->id }}</td>
                                <td class="fw-semibold">{{ $employeeName ?: 'N/A' }}</td>
                                @if ($hasUser)
                                    <td>{{ $employee->user?->email ?? 'N/A' }}</td>
                                @endif
                                @if ($hasDepartment)
                                    <td>{{ $employee->department?->name ?? 'N/A' }}</td>
                                @endif
                                @if ($hasDesignation)
                                    <td>{{ $employee->designation?->name ?? 'N/A' }}</td>
                                @endif
                                @if ($hasStatus)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst(str_replace('_', ' ', (string) $employee->status)) }}
                                        </span>
                                    </td>
                                @endif
                                <td>{{ $dateValue ? Illuminate\Support\Carbon::parse($dateValue)->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + ($hasUser ? 1 : 0) + ($hasDepartment ? 1 : 0) + ($hasDesignation ? 1 : 0) + ($hasStatus ? 1 : 0) }}" class="text-center text-body-secondary py-5">
                                    No employee records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
