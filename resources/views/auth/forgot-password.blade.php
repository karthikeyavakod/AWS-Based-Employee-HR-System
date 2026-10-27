@extends('layouts.guest')

@section('title', 'Set up or reset password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="auth-card p-4 p-md-5">
                <h1 class="h3 fw-bold mb-2">Set up or reset your password</h1>
                <p class="text-body-secondary mb-4">Enter your work email to receive a secure, time-limited link.</p>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">Please enter a valid email address.</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="form-label">Email address</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            autocomplete="email"
                            required
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send secure link</button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Back to sign in</a>
                </div>
            </div>
        </div>
    </div>
@endsection
