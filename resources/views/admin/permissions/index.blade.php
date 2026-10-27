@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
    @php
        $hasDescription = Illuminate\Support\Facades\Schema::hasColumn('permissions', 'description');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Permissions</h1>
            <p class="text-body-secondary mb-0">Review the system-defined authorization permissions.</p>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm" role="alert">
        Permissions are system-defined and managed by seeders, not manually created from the UI.
    </div>

    <div class="d-flex flex-column gap-3">
        @forelse ($permissionGroups as $module => $permissions)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h2 class="h5 mb-0 text-capitalize">{{ $module }}</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Module</th>
                                    <th scope="col">Action</th>
                                    @if ($hasDescription)
                                        <th scope="col">Description</th>
                                    @endif
                                    <th scope="col">Created at</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $permission)
                                    @php
                                        $identifier = $permission->slug ?: $permission->name;
                                        $parts = explode('.', $identifier, 2);
                                        $derivedModule = $permission->module ?: $parts[0];
                                        $derivedAction = $parts[1] ?? null;
                                    @endphp
                                    <tr>
                                        <td class="text-body-secondary">{{ $permission->id }}</td>
                                        <td title="{{ $permission->slug ?: $permission->name }}">
                                            <span class="fw-semibold">{{ $permission->name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                {{ $derivedModule }}
                                            </span>
                                        </td>
                                        <td>{{ $derivedAction ?? 'N/A' }}</td>
                                        @if ($hasDescription)
                                            <td>{{ $permission->description ?? 'N/A' }}</td>
                                        @endif
                                        <td>{{ $permission->created_at?->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-body-secondary py-5">
                    No permissions found.
                </div>
            </div>
        @endforelse
    </div>
@endsection
