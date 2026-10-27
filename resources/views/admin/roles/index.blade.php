@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    @php
        $user = auth()->user();
        $hasSlug = Illuminate\Support\Facades\Schema::hasColumn('roles', 'slug');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('roles', 'status');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Roles</h1>
            <p class="text-body-secondary mb-0">Manage access groups for HRMS users.</p>
        </div>

        @if ($user?->hasPermission('role.create'))
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Role
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
                            @if ($hasSlug)
                                <th scope="col">Slug</th>
                            @endif
                            @if ($hasStatus)
                                <th scope="col">Status</th>
                            @endif
                            <th scope="col">Users</th>
                            <th scope="col">Permissions</th>
                            <th scope="col">Created at</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            @php
                                $isSuperAdminRole = ($hasSlug && $role->slug === 'super-admin') || $role->name === 'Super Admin';
                            @endphp
                            <tr>
                                <td class="text-body-secondary">{{ $role->id }}</td>
                                <td class="fw-semibold">{{ $role->name }}</td>
                                @if ($hasSlug)
                                    <td><code>{{ $role->slug }}</code></td>
                                @endif
                                @if ($hasStatus)
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst($role->status) }}
                                        </span>
                                    </td>
                                @endif
                                <td>{{ $role->users_count }}</td>
                                <td>{{ $role->permissions_count }}</td>
                                <td>{{ $role->created_at?->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        @if ($user?->hasPermission('role.update'))
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                        @endif

                                        @if ($user?->hasPermission('permission.assign'))
                                            <a href="{{ route('admin.roles.permissions.edit', $role) }}" class="btn btn-sm btn-outline-secondary">
                                                Permissions
                                            </a>
                                        @endif

                                        @if ($user?->hasPermission('role.delete'))
                                            @if ($isSuperAdminRole)
                                                <button type="button" class="btn btn-sm btn-outline-danger" disabled>
                                                    Delete
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + ($hasSlug ? 1 : 0) + ($hasStatus ? 1 : 0) }}" class="text-center text-body-secondary py-5">
                                    No roles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($roles->hasPages())
            <div class="card-footer bg-white">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
@endsection
