<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="app-shell">
    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>
            <span>{{ config('app.name') }}</span>
        </a>
        @php
            $nav = [
                ['admin.dashboard', 'bi-grid-1x2', 'Dashboard', 'admin.dashboard'],
                ['admin.members.index', 'bi-people', 'Members', 'admin.members.*'],
                ['admin.subscriptions.index', 'bi-card-checklist', 'Subscriptions', 'admin.subscriptions.*'],
                ['admin.payments.index', 'bi-receipt', 'Payments', 'admin.payments.*'],
                ['admin.attendance.index', 'bi-calendar-check', 'Attendance', 'admin.attendance.*'],
                ['admin.plans.index', 'bi-box-seam', 'Membership Plans', 'admin.plans.*'],
                ['admin.trainers.index', 'bi-person-badge', 'Trainers', 'admin.trainers.*'],
                ['admin.expenses.index', 'bi-wallet2', 'Expenses', 'admin.expenses.*'],
                ['admin.inquiries.index', 'bi-envelope-paper', 'Inquiries', 'admin.inquiries.*'],
                ['admin.reports.index', 'bi-bar-chart-line', 'Reports', 'admin.reports.*'],
            ];
        @endphp
        <nav class="nav flex-column">
            <div class="nav-label">Main</div>
            @foreach ($nav as [$r, $icon, $label, $pattern])
                <a href="{{ route($r) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                    <i class="bi {{ $icon }}"></i> {{ $label }}
                </a>
            @endforeach
            <div class="nav-label">Account</div>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-shield-lock"></i> Staff Users</a>
            @endif
            <a href="{{ route('admin.profile.edit') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}"><i class="bi bi-person-gear"></i> My Profile</a>
            <a href="{{ route('home') }}" target="_blank" class="nav-link"><i class="bi bi-globe2"></i> View Website</a>
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- Main --}}
    <div class="main">
        <header class="topbar">
            <button class="btn btn-icon d-lg-none" id="sidebarToggle" aria-label="Menu"><i class="bi bi-list fs-4"></i></button>
            <div class="topbar-title">
                <h1 class="h5 mb-0">@yield('title', 'Dashboard')</h1>
                @hasSection('subtitle')<small class="text-muted">@yield('subtitle')</small>@endif
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
                @yield('actions')
                <div class="dropdown">
                    <button class="btn btn-user dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="avatar avatar-sm">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><span class="dropdown-item-text small text-muted text-capitalize">{{ auth()->user()->role }}</span></li>
                        <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content">
            @include('partials.flash')
            @yield('content')
        </main>
        <footer class="footer">&copy; {{ date('Y') }} {{ config('app.name') }} · Gym Management System</footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const sb = document.getElementById('sidebar'), bd = document.getElementById('sidebarBackdrop'), t = document.getElementById('sidebarToggle');
        const toggle = () => { sb.classList.toggle('open'); bd.classList.toggle('show'); };
        t && t.addEventListener('click', toggle); bd.addEventListener('click', toggle);
        document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));
    })();
</script>
@stack('scripts')
</body>
</html>
