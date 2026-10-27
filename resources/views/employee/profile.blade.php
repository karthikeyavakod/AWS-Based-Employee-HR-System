@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">My Profile</h1>
            <p class="text-body-secondary mb-0">You can update your contact details and password.</p>
        </div>
        <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">Back to dashboard</a>
        <a href="{{ route('employee.password.change') }}" class="btn btn-outline-primary">
            <i class="bi bi-key me-1"></i> Change Password
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">Please review the highlighted fields.</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('employee.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}" maxlength="255">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="address">Address</label>
                    <textarea id="address" name="address" rows="4" maxlength="5000" class="form-control @error('address') is-invalid @enderror">{{ old('address', $employee->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Save contact details</button>
            </form>
        </div>
    </div>
@endsection
