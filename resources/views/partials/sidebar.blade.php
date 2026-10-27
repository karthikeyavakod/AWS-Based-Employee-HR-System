@php
    $user = auth()->user();

    $menuItems = [
        ['label' => 'Dashboard', 'permission' => 'dashboard.view', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'bi-speedometer2'],
        ['label' => 'Roles', 'permission' => 'role.view', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'icon' => 'bi-person-badge'],
        ['label' => 'Employees', 'permission' => 'employee.view', 'route' => 'admin.employees.index', 'active' => 'admin.employees.*', 'icon' => 'bi-person-vcard'],
        ['label' => 'Departments', 'permission' => 'department.view', 'route' => 'admin.departments.index', 'active' => 'admin.departments.*', 'icon' => 'bi-diagram-3'],
        ['label' => 'Designations', 'permission' => 'designation.view', 'route' => 'admin.designations.index', 'active' => 'admin.designations.*', 'icon' => 'bi-award'],
        ['label' => 'Attendance', 'permission' => 'attendance.view', 'route' => 'admin.attendance.index', 'active' => 'admin.attendance.*', 'icon' => 'bi-calendar-check'],
        ['label' => 'Leave Management', 'permission' => 'leave.view', 'route' => 'admin.leaves.index', 'active' => 'admin.leaves.*', 'icon' => 'bi-calendar2-week'],
        ['label' => 'Payroll', 'permission' => 'payroll.view', 'route' => 'admin.payrolls.index', 'active' => 'admin.payrolls.*', 'icon' => 'bi-cash-stack'],
        ['label' => 'Reports', 'permission' => 'report.view', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'icon' => 'bi-bar-chart'],
    ];
@endphp

<div class="d-flex flex-column h-100">
    <div class="px-4 py-4">
        <a href="{{ route('dashboard') }}" class="sidebar-brand d-flex align-items-center gap-3">
            <span class="sidebar-brand-mark">
                <i class="bi bi-building fs-5"></i>
            </span>
            <span class="d-flex flex-column lh-sm">
                <span class="fw-bold">{{ config('app.name', 'HRMS Laravel') }}</span>
                <span class="small text-white-50">People Operations</span>
            </span>
        </a>
    </div>

    <nav class="flex-grow-1 overflow-auto px-3 pb-3" aria-label="Main navigation">
        <div class="sidebar-section fw-semibold text-uppercase px-3 mb-2">Workspace</div>

        <ul class="nav nav-pills flex-column gap-1">
            @foreach ($menuItems as $item)
                @if ($user?->hasPermission($item['permission']) && Illuminate\Support\Facades\Route::has($item['route']))
                    <li class="nav-item">
                        <a
                            href="{{ route($item['route']) }}"
                            class="nav-link sidebar-link d-flex align-items-center gap-2 rounded-2 px-3 py-2 {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                        >
                            <span class="sidebar-link-icon">
                                <i class="bi {{ $item['icon'] }}"></i>
                            </span>
                            <span class="fw-medium">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </nav>

    <div class="p-3">
        <div class="sidebar-user-card rounded-2 p-3">
            <div class="small text-white-50">Signed in as</div>
            <div class="fw-semibold text-truncate">{{ $user?->name ?? 'User' }}</div>
        </div>
    </div>
</div>
