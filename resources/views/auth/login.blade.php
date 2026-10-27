@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="row g-0 auth-card">
                <div class="col-lg-5 d-none d-lg-flex">
                    <section class="auth-panel w-100 p-4 p-xl-5 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-5">
                                <span class="auth-brand-mark">
                                    <i class="bi bi-building fs-4"></i>
                                </span>
                                <div>
                                    <div class="h4 fw-bold mb-0">{{ config('app.name', 'HRMS Laravel') }}</div>
                                    <div class="text-white-50">People Operations Platform</div>
                                </div>
                            </div>

                            <h1 class="display-6 fw-bold mb-3">Modern HRMS Administration</h1>
                            <p class="text-white-50 mb-4">
                                Manage employee records, attendance, leave, payroll, and reports from one secure workspace.
                            </p>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="auth-feature-icon">
                                    <i class="bi bi-person-vcard"></i>
                                </span>
                                <span>Employee records</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="auth-feature-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </span>
                                <span>Attendance tracking</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="auth-feature-icon">
                                    <i class="bi bi-cash-stack"></i>
                                </span>
                                <span>Leave and payroll overview</span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="p-4 p-md-5">
                        <div class="text-center text-lg-start mb-4">
                            <div class="d-inline-flex d-lg-none align-items-center justify-content-center auth-brand-mark bg-primary-subtle text-primary mb-3">
                                <i class="bi bi-building fs-4"></i>
                            </div>
                            <h2 class="h3 fw-bold mb-2">Sign in</h2>
                            <p class="mb-0 text-body-secondary">Access your HRMS dashboard.</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                Please check the form and try again.
                            </div>
                        @endif

                        <form method="POST" action="{{ url('/login') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    autocomplete="username"
                                    autofocus
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    autocomplete="current-password"
                                    required
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 form-check">
                                <input
                                    id="remember"
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    class="form-check-input @error('remember') is-invalid @enderror"
                                    @checked(old('remember'))
                                >
                                <label class="form-check-label" for="remember">Remember me</label>
                                @error('remember')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Sign in
                                <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
