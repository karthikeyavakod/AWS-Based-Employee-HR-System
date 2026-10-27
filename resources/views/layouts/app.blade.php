<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --admin-sidebar-width: 282px;
            --hr-sidebar-bg: #143C4C;
            --hr-sidebar-active: #174E66;
            --hr-sidebar-active-border: #2B77A0;
            --hr-sidebar-text: #D8EAF0;
            --hr-sidebar-muted: #AFC8D1;
            --hr-page-bg: #F4FAFC;
            --hr-card-bg: #ffffff;
            --hr-card-border: #E3EEF2;
            --hr-card-shadow: 0 12px 28px rgba(20, 60, 76, .07);
            --hr-text: #15313d;
            --hr-muted: #6b7f89;
            --hr-accent: #2f8f9d;
            --hr-accent-soft: #e4f4f6;
            --hr-warning-soft: #fff6df;
            --hr-success-soft: #e6f5ee;
            --hr-danger-soft: #fdeced;
        }

        body.admin-body {
            min-height: 100vh;
            background: var(--hr-page-bg);
            color: var(--hr-text);
        }

        .admin-shell {
            min-height: 100vh;
        }

        .admin-sidebar {
            width: var(--admin-sidebar-width);
            min-height: 100vh;
            background: var(--hr-sidebar-bg);
            box-shadow: 12px 0 30px rgba(20, 60, 76, .16);
        }

        .admin-sidebar-offcanvas {
            background: var(--hr-sidebar-bg);
        }

        .admin-main {
            min-width: 0;
            background:
                radial-gradient(circle at top right, rgba(47, 143, 157, .12), transparent 34rem),
                var(--hr-page-bg);
        }

        .admin-content {
            max-width: 1480px;
        }

        .admin-topbar {
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(20, 60, 76, .08);
        }

        .admin-body .card,
        .hr-card,
        .dashboard-stat-card {
            background: var(--hr-card-bg);
            border: 1px solid var(--hr-card-border);
            border-radius: .5rem;
            box-shadow: var(--hr-card-shadow);
            overflow: hidden;
        }

        .admin-body .card.border-0 {
            border: 1px solid var(--hr-card-border) !important;
        }

        .admin-body .shadow-sm {
            box-shadow: var(--hr-card-shadow) !important;
        }

        .admin-body .card-header {
            background: #fff;
            border-bottom-color: var(--hr-card-border);
            padding: 1rem 1.25rem;
        }

        .admin-body .card-body {
            padding: 1.25rem;
        }

        .admin-body .card-body.p-0 {
            padding: 0 !important;
        }

        .admin-body .card-footer {
            background: #fff;
            border-top-color: var(--hr-card-border);
        }

        .admin-body .table {
            --bs-table-bg: transparent;
            --bs-table-hover-bg: #F4FAFC;
            margin-bottom: 0;
        }

        .admin-body .table > :not(caption) > * > * {
            padding: .95rem 1rem;
            border-bottom-color: #E7F0F4;
        }

        .admin-body .table-light {
            --bs-table-bg: #EAF6FA;
            --bs-table-color: var(--hr-text);
            border-color: var(--hr-card-border);
        }

        .admin-body thead th {
            font-size: .8rem;
            letter-spacing: 0;
            text-transform: uppercase;
            color: #183746;
            white-space: nowrap;
        }

        .admin-body .form-control,
        .admin-body .form-select {
            border-color: #D8E8EE;
            border-radius: .75rem;
            min-height: 2.75rem;
            background-color: #FBFDFE;
        }

        .admin-body textarea.form-control {
            min-height: auto;
        }

        .admin-body .form-control:focus,
        .admin-body .form-select:focus {
            border-color: var(--hr-accent);
            box-shadow: 0 0 0 .2rem rgba(47, 143, 157, .12);
        }

        .admin-body .form-label {
            font-weight: 600;
            color: var(--hr-text);
        }

        .admin-body .form-text {
            color: var(--hr-muted);
        }

        .admin-body .btn {
            border-radius: .75rem;
            font-weight: 600;
            padding: .6rem 1rem;
        }

        .admin-body .btn-sm {
            border-radius: .6rem;
            padding: .35rem .7rem;
        }

        .admin-body .btn-primary {
            --bs-btn-bg: var(--hr-accent);
            --bs-btn-border-color: var(--hr-accent);
            --bs-btn-hover-bg: #257b87;
            --bs-btn-hover-border-color: #257b87;
            --bs-btn-active-bg: #216f79;
            --bs-btn-active-border-color: #216f79;
        }

        .admin-body .btn-outline-primary {
            --bs-btn-color: var(--hr-accent);
            --bs-btn-border-color: rgba(47, 143, 157, .42);
            --bs-btn-hover-bg: var(--hr-accent);
            --bs-btn-hover-border-color: var(--hr-accent);
        }

        .admin-body .badge {
            border-radius: 999px;
            padding: .45rem .7rem;
            font-weight: 600;
        }

        .admin-body .alert {
            border-radius: .5rem;
            border: 1px solid var(--hr-card-border);
            box-shadow: 0 10px 22px rgba(20, 60, 76, .06);
        }

        .admin-body code {
            color: #215d70;
            background: #EAF6FA;
            padding: .18rem .4rem;
            border-radius: .45rem;
        }

        .sidebar-brand {
            color: #fff;
            text-decoration: none;
        }

        .sidebar-brand-mark,
        .topbar-avatar,
        .stat-icon,
        .dashboard-hero-icon,
        .topbar-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .sidebar-brand-mark {
            width: 2.55rem;
            height: 2.55rem;
            border-radius: .7rem;
            background: rgba(255, 255, 255, .14);
            color: #fff;
        }

        .sidebar-link {
            color: var(--hr-sidebar-text);
            min-height: 2.65rem;
            border: 1px solid transparent;
            transition: background-color .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
        }

        .sidebar-link:hover,
        .sidebar-link:focus {
            color: #fff;
            background: var(--hr-sidebar-active);
            border-color: var(--hr-sidebar-active-border);
        }

        .sidebar-link.active {
            color: #fff;
            background: var(--hr-sidebar-active);
            border-color: var(--hr-sidebar-active-border);
            box-shadow: 0 10px 22px rgba(0, 0, 0, .08);
        }

        .sidebar-link.active .sidebar-link-icon {
            background: rgba(255, 255, 255, .18);
        }

        .sidebar-link-icon {
            width: 2rem;
            height: 2rem;
            border-radius: .5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--hr-sidebar-muted);
        }

        .sidebar-link:hover .sidebar-link-icon,
        .sidebar-link:focus .sidebar-link-icon,
        .sidebar-link.active .sidebar-link-icon {
            color: #fff;
        }

        .sidebar-section {
            color: var(--hr-sidebar-muted);
            font-size: .75rem;
            letter-spacing: 0;
        }

        .sidebar-user-card {
            background: rgba(255, 255, 255, .08);
        }

        .topbar-search {
            max-width: 430px;
            position: relative;
        }

        .topbar-search .form-control {
            border: 1px solid rgba(20, 60, 76, .08);
            border-radius: .85rem;
            min-height: 2.75rem;
            padding-left: 2.65rem;
            background: #f8fbfc;
        }

        .topbar-search i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--hr-muted);
        }

        .topbar-icon-btn {
            width: 2.55rem;
            height: 2.55rem;
            border-radius: .85rem;
            border: 1px solid rgba(20, 60, 76, .08);
            background: #fff;
            color: var(--hr-text);
        }

        .topbar-avatar {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 50%;
            background: var(--hr-accent-soft);
            color: var(--hr-accent);
        }

        .dashboard-hero {
            overflow: hidden;
        }

        .dashboard-hero-icon {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: .5rem;
            background: var(--hr-accent-soft);
            color: var(--hr-accent);
        }

        .attendance-callout {
            background: var(--hr-accent-soft);
        }

        .progress-thin {
            height: .65rem;
        }

        .progress-bar-dashboard {
            background: var(--hr-accent);
        }

        .dashboard-stat-card {
            min-height: 9.75rem;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 38px rgba(20, 60, 76, .12);
        }

        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: .5rem;
        }

        .stat-icon.stat-teal {
            background: var(--hr-accent-soft);
            color: var(--hr-accent);
        }

        .stat-icon.stat-success {
            background: var(--hr-success-soft);
            color: #23785f;
        }

        .stat-icon.stat-warning {
            background: var(--hr-warning-soft);
            color: #b17800;
        }

        .stat-icon.stat-danger {
            background: var(--hr-danger-soft);
            color: #b6474f;
        }

        .stat-icon.stat-slate {
            background: #edf2f5;
            color: #526873;
        }

        .dashboard-table {
            --bs-table-bg: transparent;
        }

        .dashboard-table thead th {
            color: var(--hr-muted);
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom-color: var(--hr-card-border);
        }

        .dashboard-table tbody td {
            vertical-align: middle;
            border-bottom-color: #edf3f6;
        }

        .soft-badge {
            border-radius: 999px;
            padding: .35rem .65rem;
            background: #edf3f6;
            color: #526873;
        }

        .report-card-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: .5rem;
        }

        .quick-action {
            border-radius: .5rem;
            border: 1px solid var(--hr-card-border);
            background: #fff;
            color: var(--hr-text);
            text-decoration: none;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .quick-action:hover,
        .quick-action:focus {
            color: var(--hr-text);
            border-color: rgba(47, 143, 157, .3);
            box-shadow: 0 10px 24px rgba(20, 60, 76, .08);
            transform: translateY(-1px);
        }

        @media (max-width: 991.98px) {
            .admin-main {
                width: 100%;
            }

            .topbar-search {
                max-width: none;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-shell d-flex">
        <aside class="admin-sidebar d-none d-lg-flex flex-column text-white">
            @include('partials.sidebar')
        </aside>

        <div class="offcanvas offcanvas-start admin-sidebar-offcanvas text-white" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-header border-bottom border-secondary-subtle">
                <h5 class="offcanvas-title" id="adminSidebarLabel">{{ config('app.name', 'HRMS Laravel') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                @include('partials.sidebar')
            </div>
        </div>

        <div class="admin-main flex-grow-1 d-flex flex-column">
            @include('partials.navbar')

            <main class="flex-grow-1 py-4 py-lg-5">
                <div class="admin-content container-fluid px-3 px-md-4 px-xl-5 mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
