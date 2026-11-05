@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Search Results</h1>
            <p class="text-body-secondary mb-0">Search results for "<span class="text-dark fw-semibold">{{ $query }}</span>"</p>
        </div>

        <a href="{{ route('admin.search') }}" class="btn btn-outline-secondary">Clear</a>
    </div>

    @if ($query === '')
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-body-secondary">Enter a search term to find employees.</p>
                </div>
            </div>
        </div>
    @elseif ($employees->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-body-secondary">No employees found matching "{{ $query }}".</p>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Employee Code</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Department</th>
                                <th scope="col">Designation</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td>{{ $employee->employee_code ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.employees.edit', $employee) }}" class="text-decoration-none">
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </a>
                                    </td>
                                    <td>{{ $employee->user?->email ?? 'N/A' }}</td>
                                    <td>{{ $employee->department?->name ?? 'N/A' }}</td>
                                    <td>{{ $employee->designation?->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $employee->status === 'active' ? 'success' : ($employee->status === 'inactive' ? 'secondary' : 'warning') }}">
                                            {{ ucfirst($employee->status ?? 'unknown') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection