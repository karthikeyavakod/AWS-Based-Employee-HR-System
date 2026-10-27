@php
    $user = auth()->user();
    $roles = collect();

    if ($user !== null) {
        $roles = $user->relationLoaded('roles') ? $user->roles : $user->roles()->orderBy('name')->get();
    }
@endphp

<nav class="navbar navbar-expand admin-topbar sticky-top">
    <div class="container-fluid px-3 px-md-4 px-xl-5 gap-3">
        <button
            class="btn btn-outline-secondary d-lg-none"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#adminSidebar"
            aria-controls="adminSidebar"
            aria-label="Open navigation"
        >
            <i class="bi bi-list"></i>
        </button>

        <div class="d-none d-md-flex flex-column flex-shrink-0">
            <span class="navbar-brand mb-0 h1 fs-5">@yield('title', 'Dashboard')</span>
            <span class="text-body-secondary small">Modern HRMS administration</span>
        </div>

        <div class="topbar-search flex-grow-1 d-none d-lg-block">
            <i class="bi bi-search"></i>
            <input type="search" class="form-control" placeholder="Search employees, reports, payroll..." aria-label="Search">
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <button type="button" class="topbar-icon-btn" aria-label="Messages">
                <i class="bi bi-chat-dots"></i>
            </button>
            <button type="button" class="topbar-icon-btn position-relative" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">New notifications</span>
                </span>
            </button>

            <div class="dropdown">
                <button class="btn btn-light border d-flex align-items-center gap-2 px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="topbar-avatar">
                        <i class="bi bi-person"></i>
                    </span>
                    <span class="d-none d-md-flex flex-column align-items-start lh-sm">
                        <span class="fw-semibold">{{ $user?->name }}</span>
                        @if ($roles?->isNotEmpty())
                            <span class="small text-body-secondary">{{ $roles->pluck('name')->join(', ') }}</span>
                        @endif
                    </span>
                    <i class="bi bi-chevron-down small text-body-secondary"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-end shadow-sm">
                    <div class="px-3 py-2">
                        <div class="fw-semibold">{{ $user?->name }}</div>
                        <div class="small text-body-secondary">{{ $user?->email }}</div>
                        @if ($roles?->isNotEmpty())
                            <div class="small text-body-secondary mt-1">{{ $roles->pluck('name')->join(', ') }}</div>
                        @endif
                    </div>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
