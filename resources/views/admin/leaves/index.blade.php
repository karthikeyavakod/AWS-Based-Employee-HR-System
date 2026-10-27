@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
    @php
        $user = auth()->user();
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
            <h1 class="h3 mb-1">Leave Requests</h1>
            <p class="text-body-secondary mb-0">Review and manage employee leave requests.</p>
        </div>

        @if ($user?->hasPermission('leave.create'))
            <a href="{{ route('admin.leaves.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Leave
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
                            <th scope="col">Created at</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaveRequests as $leaveRequest)
                            @php
                                $employee = $hasEmployee ? $leaveRequest->employee : null;
                                $employeeName = $employee?->user?->name
                                    ?: trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                                $leaveType = $hasLeaveTypeId ? $leaveTypesById->get($leaveRequest->leave_type_id) : null;
                                $leaveTypeName = $leaveType?->name
                                    ?? $leaveType?->code
                                    ?? ($hasLeaveType ? $leaveRequest->leave_type : null)
                                    ?? ($hasType ? $leaveRequest->type : null);
                            @endphp
                            <tr>
                                <td class="text-body-secondary">{{ $leaveRequest->id }}</td>
                                @if ($hasEmployee)
                                    <td class="fw-semibold">{{ $employeeName ?: 'N/A' }}</td>
                                @endif
                                @if ($hasLeaveTypeId || $hasLeaveType || $hasType)
                                    <td>{{ $leaveTypeName ?: 'N/A' }}</td>
                                @endif
                                @if ($hasStartDate)
                                    <td>{{ $leaveRequest->start_date ? \Illuminate\Support\Carbon::parse($leaveRequest->start_date)->format('M d, Y') : 'N/A' }}</td>
                                @endif
                                @if ($hasEndDate)
                                    <td>{{ $leaveRequest->end_date ? \Illuminate\Support\Carbon::parse($leaveRequest->end_date)->format('M d, Y') : 'N/A' }}</td>
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
                                    <td>{{ \Illuminate\Support\Str::limit((string) $leaveRequest->reason, 48) ?: 'N/A' }}</td>
                                @endif
                                <td>{{ $leaveRequest->created_at?->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end flex-wrap gap-2">
                                        @if ($user?->hasPermission('leave.view'))
                                            <a href="{{ route('admin.leaves.edit', $leaveRequest) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($user?->hasPermission('leave.approve'))
                                            <form method="POST" action="{{ route('admin.leaves.approve', $leaveRequest) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif

                                        @if ($user?->hasPermission('leave.reject'))
                                            <form method="POST" action="{{ route('admin.leaves.reject', $leaveRequest) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    Reject
                                                </button>
                                            </form>
                                        @endif

                                        @if ($user?->hasPermission('leave.delete'))
                                            <form method="POST" action="{{ route('admin.leaves.destroy', $leaveRequest) }}">
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
                                    colspan="{{ 3 + ($hasEmployee ? 1 : 0) + (($hasLeaveTypeId || $hasLeaveType || $hasType) ? 1 : 0) + ($hasStartDate ? 1 : 0) + ($hasEndDate ? 1 : 0) + ($hasTotalDays ? 1 : 0) + ($hasStatus ? 1 : 0) + ($hasReason ? 1 : 0) }}"
                                    class="text-center text-body-secondary py-5"
                                >
                                    No leave requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($leaveRequests->hasPages())
            <div class="card-footer bg-white">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>
@endsection
