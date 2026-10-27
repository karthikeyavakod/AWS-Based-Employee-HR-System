@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
    @php
        $hasSlug = Illuminate\Support\Facades\Schema::hasColumn('roles', 'slug');
        $hasDescription = Illuminate\Support\Facades\Schema::hasColumn('roles', 'description');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('roles', 'status');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Role</h1>
            <p class="text-body-secondary mb-0">Update role details for {{ $role->name }}.</p>
        </div>

        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $role->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($hasSlug)
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug</label>
                        <input
                            id="slug"
                            type="text"
                            name="slug"
                            value="{{ old('slug', $role->slug) }}"
                            class="form-control @error('slug') is-invalid @enderror"
                        >
                        <div class="form-text">Leave blank to regenerate it from the role name.</div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                @if ($hasDescription)
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="form-control @error('description') is-invalid @enderror"
                        >{{ old('description', $role->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                @if ($hasStatus)
                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" @selected(old('status', $role->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $role->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
@endsection
