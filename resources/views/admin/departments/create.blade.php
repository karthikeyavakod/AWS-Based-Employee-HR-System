@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
    @php
        $hasCode = Illuminate\Support\Facades\Schema::hasColumn('departments', 'code');
        $hasSlug = Illuminate\Support\Facades\Schema::hasColumn('departments', 'slug');
        $hasDescription = Illuminate\Support\Facades\Schema::hasColumn('departments', 'description');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('departments', 'status');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Create Department</h1>
            <p class="text-body-secondary mb-0">Add a new department to the HRMS structure.</p>
        </div>

        <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">
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
            <form method="POST" action="{{ route('admin.departments.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($hasCode || $hasSlug)
                    <div class="mb-3">
                        <label for="{{ $hasCode ? 'code' : 'slug' }}" class="form-label">{{ $hasCode ? 'Code' : 'Slug' }}</label>
                        <input
                            id="{{ $hasCode ? 'code' : 'slug' }}"
                            type="text"
                            name="{{ $hasCode ? 'code' : 'slug' }}"
                            value="{{ old($hasCode ? 'code' : 'slug') }}"
                            class="form-control @error($hasCode ? 'code' : 'slug') is-invalid @enderror"
                        >
                        <div class="form-text">Leave blank to generate it from the department name.</div>
                        @error($hasCode ? 'code' : 'slug')
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
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                @if ($hasStatus)
                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Department</button>
                </div>
            </form>
        </div>
    </div>
@endsection
