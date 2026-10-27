@extends('layouts.app')

@section('title', 'Assign Permissions')

@section('content')
    @php
        $selectedPermissionIds = collect(old('permissions', $assignedPermissionIds))
            ->map(fn ($permissionId) => (int) $permissionId)
            ->all();
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Assign Permissions</h1>
            <p class="text-body-secondary mb-0">Manage permissions for {{ $role->name }}.</p>
        </div>

        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            Back
        </a>
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

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Please review the selected permissions and try again.
        </div>
    @endif

    <div class="alert alert-info border-0 shadow-sm" role="alert">
        Permissions are system-defined and managed by seeders.
    </div>

    <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            @forelse ($permissions as $module => $modulePermissions)
                <div class="col-12 col-xl-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h2 class="h5 mb-0 text-capitalize">{{ $module }}</h2>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach ($modulePermissions as $permission)
                                    <div class="col-12 col-md-6">
                                        <div class="form-check">
                                            <input
                                                id="permission-{{ $permission->id }}"
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission->id }}"
                                                class="form-check-input @error('permissions') is-invalid @enderror @error('permissions.*') is-invalid @enderror"
                                                @checked(in_array($permission->id, $selectedPermissionIds, true))
                                            >
                                            <label class="form-check-label" for="permission-{{ $permission->id }}" title="{{ $permission->slug ?: $permission->name }}">
                                                <span class="fw-semibold">{{ $permission->name }}</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center text-body-secondary py-5">
                            No permissions found.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @error('permissions')
            <div class="text-danger small mt-3">{{ $message }}</div>
        @enderror
        @error('permissions.*')
            <div class="text-danger small mt-3">{{ $message }}</div>
        @enderror

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Permissions</button>
        </div>
    </form>
@endsection
