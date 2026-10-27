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
            --hr-auth-sidebar: #143C4C;
            --hr-auth-sidebar-soft: #174E66;
            --hr-auth-accent: #2B77A0;
            --hr-auth-bg: #F4FAFC;
            --hr-auth-card: #FFFFFF;
            --hr-auth-border: #E3EEF2;
            --hr-auth-text: #15313d;
            --hr-auth-muted: #6b7f89;
            --hr-auth-shadow: 0 20px 48px rgba(20, 60, 76, .12);
        }

        body.auth-body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(43, 119, 160, .12), transparent 26rem),
                var(--hr-auth-bg);
            color: var(--hr-auth-text);
        }

        .auth-shell {
            min-height: 100vh;
        }

        .auth-panel,
        .auth-card {
            border-radius: .5rem;
            overflow: hidden;
            box-shadow: var(--hr-auth-shadow);
        }

        .auth-panel {
            background:
                linear-gradient(145deg, rgba(20, 60, 76, .96), rgba(23, 78, 102, .96)),
                var(--hr-auth-sidebar);
            color: #fff;
        }

        .auth-brand-mark,
        .auth-feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .auth-brand-mark {
            width: 3rem;
            height: 3rem;
            border-radius: .75rem;
            background: rgba(255, 255, 255, .14);
        }

        .auth-feature-icon {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: .65rem;
            background: rgba(255, 255, 255, .12);
        }

        .auth-card {
            background: var(--hr-auth-card);
            border: 1px solid var(--hr-auth-border);
        }

        .auth-body .form-control {
            min-height: 2.9rem;
            border-color: #D8E8EE;
            border-radius: .75rem;
            background: #FBFDFE;
        }

        .auth-body .form-control:focus {
            border-color: var(--hr-auth-accent);
            box-shadow: 0 0 0 .2rem rgba(43, 119, 160, .12);
        }

        .auth-body .form-label {
            font-weight: 600;
            color: var(--hr-auth-text);
        }

        .auth-body .btn {
            min-height: 2.9rem;
            border-radius: .75rem;
            font-weight: 700;
        }

        .auth-body .btn-primary {
            --bs-btn-bg: var(--hr-auth-sidebar-soft);
            --bs-btn-border-color: var(--hr-auth-sidebar-soft);
            --bs-btn-hover-bg: var(--hr-auth-sidebar);
            --bs-btn-hover-border-color: var(--hr-auth-sidebar);
            --bs-btn-active-bg: #103241;
            --bs-btn-active-border-color: #103241;
        }

        .auth-body .alert {
            border-radius: .75rem;
            border: 1px solid var(--hr-auth-border);
        }

        @media (max-width: 991.98px) {
            .auth-panel {
                min-height: auto;
            }
        }
    </style>
</head>
<body class="auth-body">
    <main class="auth-shell d-flex align-items-center py-4 py-lg-5">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
