@extends('layouts.guest')

@section('title', 'Choose a password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="auth-card p-4 p-md-5">
                <h1 class="h3 fw-bold mb-2">Choose a password</h1>
                <p class="text-body-secondary mb-4">Use at least 12 characters. This setup link expires after 60 minutes.</p>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">The link may have expired. Request a new one and try again.</div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $email) }}"
                            class="form-control @error('email') is-invalid @enderror"
                            autocomplete="email"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            minlength="12"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                            minlength="12"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
