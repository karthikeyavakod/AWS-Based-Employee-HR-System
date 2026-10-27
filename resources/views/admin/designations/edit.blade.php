@extends('layouts.app')

@section('title', 'Edit Designation')

@section('content')
    @php
        $hasDepartment = Illuminate\Support\Facades\Schema::hasColumn('designations', 'department_id');
        $hasCode = Illuminate\Support\Facades\Schema::hasColumn('designations', 'code');
        $hasSlug = Illuminate\Support\Facades\Schema::hasColumn('designations', 'slug');
        $hasDescription = Illuminate\Support\Facades\Schema::hasColumn('designations', 'description');
        $hasStatus = Illuminate\Support\Facades\Schema::hasColumn('designations', 'status');
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Designation</h1>
            <p class="text-body-secondary mb-0">Update details for {{ $designation->name }}.</p>
        </div>

        <a href="{{ route('admin.designations.index') }}" class="btn btn-outline-secondary">
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
            <form method="POST" action="{{ route('admin.designations.update', $designation) }}">
                @csrf
                @method('PUT')

                @if ($hasDepartment)
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                        <select id="department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">Select department</option>
                            @foreach ($departments as $department)
                                <option
                                    value="{{ $department->id }}"
                                    @selected((string) old('department_id', $designation->department_id) === (string) $department->id)
                                >
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $designation->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($hasCode || $hasSlug)
                    @php
                        $identifierColumn = $hasCode ? 'code' : 'slug';
                    @endphp
                    <div class="mb-3">
                        <label for="{{ $identifierColumn }}" class="form-label">{{ ucfirst($identifierColumn) }}</label>
                        <input
                            id="{{ $identifierColumn }}"
                            type="text"
                            name="{{ $identifierColumn }}"
                            value="{{ old($identifierColumn, $designation->{$identifierColumn}) }}"
                            class="form-control @error($identifierColumn) is-invalid @enderror"
                        >
                        <div class="form-text">Leave blank to regenerate it from the designation name.</div>
                        @error($identifierColumn)
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
                        >{{ old('description', $designation->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                @if ($hasStatus)
                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" @selected(old('status', $designation->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $designation->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.designations.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Designation</button>
                </div>
            </form>
        </div>
    </div>
@endsection
