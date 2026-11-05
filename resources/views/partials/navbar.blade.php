@php
    $user = auth()->user();
    $roles = collect();
    $announcements = collect();
    $announcementsUrl = null;

    if ($user !== null) {
        $roles = $user->relationLoaded('roles') ? $user->roles : $user->roles()->orderBy('name')->get();

        if ($user->hasPermission('announcement.view')) {
            $announcementsUrl = route('admin.announcements.index');
        } elseif ($user->hasRole('employee')) {
            $announcementsUrl = route('employee.announcements');
        }

        if ($announcementsUrl !== null && \Illuminate\Support\Facades\Schema::hasTable('company_announcements')) {
            $announcements = \App\Models\CompanyAnnouncement::query()
                ->published()
                ->with('creator')
                ->latest()
                ->limit(5)
                ->get();
        }
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
            <span class="text-body-secondary small">Smart HR Connect workspace</span>
        </div>

        <div class="topbar-search flex-grow-1 d-none d-lg-block">
            <form method="GET" action="{{ route('admin.search') }}" class="d-flex position-relative">
                <button type="submit" class="position-absolute top-50 start-0 translate-middle-y border-0 bg-transparent p-0 text-muted">
                    <i class="bi bi-search"></i>
                </button>
                <input type="search" name="q" class="form-control ps-5" placeholder="Search employees, reports, payroll..." aria-label="Search" value="{{ request()->query('q', '') }}">
            </form>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <a href="{{ $announcementsUrl ?? '#' }}" class="topbar-icon-btn text-decoration-none text-dark {{ $announcementsUrl === null ? 'disabled opacity-50' : '' }}" aria-label="Messages" {{ $announcementsUrl === null ? 'tabindex="-1" aria-disabled="true"' : '' }}>
                <i class="bi bi-chat-dots"></i>
            </a>
            <div class="dropdown">
                <button class="topbar-icon-btn position-relative dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications" {{ $announcementsUrl === null ? 'disabled' : '' }}>
                    <i class="bi bi-bell"></i>
                    @if ($announcements->isNotEmpty())
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                            <span class="visually-hidden">New notifications</span>
                        </span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end p-0" style="width: 340px;">
                    <div class="dropdown-header px-3 py-2 border-bottom">
                        <span class="fw-semibold small">Notifications</span>
                        @if ($announcementsUrl)
                            <a href="{{ $announcementsUrl }}" class="small text-decoration-none float-end">View all</a>
                        @endif
                    </div>
                    @if ($announcements->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach ($announcements as $announcement)
                                <a href="{{ $announcementsUrl }}" class="list-group-item list-group-item-action d-flex gap-2 p-2 text-decoration-none text-dark">
                                    <div class="bg-light bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 2.5rem; height: 2.5rem;">
                                        <i class="bi bi-bell text-primary fs-6"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">{{ $announcement->title }}</div>
                                        <div class="text-body-secondary smaller">{{ $announcement->publish_at?->format('M d, Y') ?? 'Immediate' }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 text-center text-body-secondary small">
                            No new notifications.
                        </div>
                    @endif
                </div>
            </div>

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
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('employee.password.change') }}">
                        <i class="bi bi-key"></i>
                        <span>Change Password</span>
                    </a>
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
