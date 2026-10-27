@extends('layouts.app')

@section('title', 'Employees')

@section('content')
    @php
        $user = auth()->user();
        $hasUser = Illuminate\Support\Facades\Schema::hasColumn('employees', 'user_id') && method_exists(App\Models\Employee::class, 'user');
        $hasDepartment = Illuminate\Support\Facades\Schema::hasColumn('employees', 'department_id') && method_exists(App\Models\Employee::class, 'department');
        $hasDesignation = Illuminate\Support\Facades\Schema::hasColumn('employees', 'designation_id') && method_exists(App\Models\Employee::class, 'designation');
        $hasEmployeeCode = Illuminate\Support\Facades\Schema::hasColumn('employees', 'employee_code');
        $hasCode = Illuminate\Support\Facades\Schema::hasColumn('employees', 'code');
        $hasPhone = Illuminate\Support\Facades\Schema::hasColumn('employees', 'phone');
        $hasJoiningDate = Illuminate\Support\Facades\Schema::hasColumn('employees', 'joining_date');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('employees', 'status');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Employees</h1>
            <p class="text-body-secondary mb-0">Manage employee profiles and linked user accounts.</p>
        </div>

        @if ($user?->hasPermission('employee.create'))
            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Employee
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
                            @if ($hasEmployeeCode || $hasCode)
                                <th scope="col">{{ $hasEmployeeCode ? 'Employee Code' : 'Code' }}</th>
                            @endif
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            @if ($hasPhone)
                                <th scope="col">Phone</th>
                            @endif
                            @if ($hasDepartment)
                                <th scope="col">Department</th>
                            @endif
                            @if ($hasDesignation)
                                <th scope="col">Designation</th>
                            @endif
                            @if ($hasJoiningDate)
                                <th scope="col">Joining Date</th>
                            @endif
                            @if ($hasStatus)
                                <th scope="col">Status</th>
                            @endif
                            <th scope="col">Created at</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            @php
                                $employeeName = $hasUser && $employee->user
                                    ? $employee->user->name
                                    : trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                            @endphp
                            <tr>
                                <td class="text-body-secondary">{{ $employee->id }}</td>
                                @if ($hasEmployeeCode || $hasCode)
                                    <td><code>{{ $hasEmployeeCode ? $employee->employee_code : $employee->code }}</code></td>
                                @endif
                                <td class="fw-semibold">{{ $employeeName ?: 'N/A' }}</td>
                                <td>{{ $hasUser ? ($employee->user?->email ?? 'N/A') : 'N/A' }}</td>
                                @if ($hasPhone)
                                    <td>{{ $employee->phone ?: 'N/A' }}</td>
                                @endif
                                @if ($hasDepartment)
                                    <td>{{ $employee->department?->name ?? 'N/A' }}</td>
                                @endif
                                @if ($hasDesignation)
                                    <td>{{ $employee->designation?->name ?? 'N/A' }}</td>
                                @endif
                                @if ($hasJoiningDate)
                                    <td>{{ $employee->joining_date ? \Illuminate\Support\Carbon::parse($employee->joining_date)->format('M d, Y') : 'N/A' }}</td>
                                @endif
                                @if ($hasStatus)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                @endif
                                <td>{{ $employee->created_at?->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($user?->hasPermission('employee.update'))
                                            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($user?->hasPermission('employee.delete'))
                                            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}">
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
                                    colspan="{{ 5 + (($hasEmployeeCode || $hasCode) ? 1 : 0) + ($hasPhone ? 1 : 0) + ($hasDepartment ? 1 : 0) + ($hasDesignation ? 1 : 0) + ($hasJoiningDate ? 1 : 0) + ($hasStatus ? 1 : 0) }}"
                                    class="text-center text-body-secondary py-5"
                                >
                                    No employees found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($employees->hasPages())
            <div class="card-footer bg-white">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
@endsection
