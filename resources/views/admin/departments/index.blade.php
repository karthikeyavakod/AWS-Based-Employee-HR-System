@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    @php
        $user = auth()->user();
        $hasCode = Illuminate\Support\Facades\Schema::hasColumn('departments', 'code');
        $hasSlug = Illuminate\Support\Facades\Schema::hasColumn('departments', 'slug');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('departments', 'status');
        $hasDescription = Illuminate\Support\Facades\Schema::hasColumn('departments', 'description');
        $hasEmployeesCount = $departments->getCollection()->contains(fn ($department) => array_key_exists('employees_count', $department->getAttributes()));
        $hasDesignationsCount = $departments->getCollection()->contains(fn ($department) => array_key_exists('designations_count', $department->getAttributes()));
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Departments</h1>
            <p class="text-body-secondary mb-0">Manage organizational departments.</p>
        </div>

        @if ($user?->hasPermission('department.create'))
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Department
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
                            <th scope="col">Name</th>
                            @if ($hasCode || $hasSlug)
                                <th scope="col">{{ $hasCode ? 'Code' : 'Slug' }}</th>
                            @endif
                            @if ($hasStatus)
                                <th scope="col">Status</th>
                            @endif
                            @if ($hasDescription)
                                <th scope="col">Description</th>
                            @endif
                            @if ($hasEmployeesCount)
                                <th scope="col">Employees</th>
                            @endif
                            @if ($hasDesignationsCount)
                                <th scope="col">Designations</th>
                            @endif
                            <th scope="col">Created at</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $department)
                            <tr>
                                <td class="text-body-secondary">{{ $department->id }}</td>
                                <td class="fw-semibold">{{ $department->name }}</td>
                                @if ($hasCode || $hasSlug)
                                    <td><code>{{ $hasCode ? $department->code : $department->slug }}</code></td>
                                @endif
                                @if ($hasStatus)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst($department->status) }}
                                        </span>
                                    </td>
                                @endif
                                @if ($hasDescription)
                                    <td class="text-body-secondary">{{ $department->description ?: 'N/A' }}</td>
                                @endif
                                @if ($hasEmployeesCount)
                                    <td>{{ $department->employees_count }}</td>
                                @endif
                                @if ($hasDesignationsCount)
                                    <td>{{ $department->designations_count }}</td>
                                @endif
                                <td>{{ $department->created_at?->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($user?->hasPermission('department.update'))
                                            <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($user?->hasPermission('department.delete'))
                                            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}">
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
                                    colspan="{{ 4 + (($hasCode || $hasSlug) ? 1 : 0) + ($hasStatus ? 1 : 0) + ($hasDescription ? 1 : 0) + ($hasEmployeesCount ? 1 : 0) + ($hasDesignationsCount ? 1 : 0) }}"
                                    class="text-center text-body-secondary py-5"
                                >
                                    No departments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($departments->hasPages())
            <div class="card-footer bg-white">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
@endsection
