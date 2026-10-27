@extends('layouts.app')

@section('title', 'Designations')

@section('content')
    @php
        $user = auth()->user();
        $hasDepartment = Illuminate\Support\Facades\Schema::hasColumn('designations', 'department_id') && method_exists(App\Models\Designation::class, 'department');
        $hasCode = Illuminate\Support\Facades\Schema::hasColumn('designations', 'code');
        $hasSlug = Illuminate\Support\Facades\Schema::hasColumn('designations', 'slug');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('designations', 'status');
        $hasDescription = Illuminate\Support\Facades\Schema::hasColumn('designations', 'description');
        $hasEmployeesCount = $designations->getCollection()->contains(fn ($designation) => array_key_exists('employees_count', $designation->getAttributes()));
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Designations</h1>
            <p class="text-body-secondary mb-0">Manage job titles within departments.</p>
        </div>

        @if ($user?->hasPermission('designation.create'))
            <a href="{{ route('admin.designations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Designation
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
                            @if ($hasDepartment)
                                <th scope="col">Department</th>
                            @endif
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
                            <th scope="col">Created at</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($designations as $designation)
                            <tr>
                                <td class="text-body-secondary">{{ $designation->id }}</td>
                                <td class="fw-semibold">{{ $designation->name }}</td>
                                @if ($hasDepartment)
                                    <td>{{ $designation->department?->name ?? 'N/A' }}</td>
                                @endif
                                @if ($hasCode || $hasSlug)
                                    <td><code>{{ $hasCode ? $designation->code : $designation->slug }}</code></td>
                                @endif
                                @if ($hasStatus)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst($designation->status) }}
                                        </span>
                                    </td>
                                @endif
                                @if ($hasDescription)
                                    <td class="text-body-secondary">{{ $designation->description ?: 'N/A' }}</td>
                                @endif
                                @if ($hasEmployeesCount)
                                    <td>{{ $designation->employees_count }}</td>
                                @endif
                                <td>{{ $designation->created_at?->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($user?->hasPermission('designation.update'))
                                            <a href="{{ route('admin.designations.edit', $designation) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($user?->hasPermission('designation.delete'))
                                            <form method="POST" action="{{ route('admin.designations.destroy', $designation) }}">
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
                                    colspan="{{ 4 + ($hasDepartment ? 1 : 0) + (($hasCode || $hasSlug) ? 1 : 0) + ($hasStatus ? 1 : 0) + ($hasDescription ? 1 : 0) + ($hasEmployeesCount ? 1 : 0) }}"
                                    class="text-center text-body-secondary py-5"
                                >
                                    No designations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($designations->hasPages())
            <div class="card-footer bg-white">
                {{ $designations->links() }}
            </div>
        @endif
    </div>
@endsection
