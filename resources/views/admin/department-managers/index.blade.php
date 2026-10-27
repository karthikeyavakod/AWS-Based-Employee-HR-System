@extends('layouts.app')

@section('title', 'Department Managers')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Department Managers</h1>
            <p class="text-body-secondary mb-0">Assign managers to departments.</p>
        </div>

        @if (auth()->user()?->hasPermission('user.create'))
            <a href="{{ route('admin.department-managers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Assign Manager
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Department</th>
                            <th scope="col">Manager</th>
                            <th scope="col">Assigned At</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departmentManagers as $dm)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $dm->department?->name ?? 'N/A' }}</td>
                                <td>{{ $dm->user?->name ?? 'N/A' }}</td>
                                <td class="text-body-secondary small">{{ $dm->created_at?->format('M d, Y') }}</td>
                                <td>
                                    @if (auth()->user()?->hasPermission('user.delete'))
                                        <form method="POST" action="{{ route('admin.department-managers.destroy', $dm) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this department manager assignment?')">
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-body-secondary py-5">No department managers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($departmentManagers->hasPages())
            <div class="card-footer bg-white">
                {{ $departmentManagers->links() }}
            </div>
        @endif
    </div>
@endsection