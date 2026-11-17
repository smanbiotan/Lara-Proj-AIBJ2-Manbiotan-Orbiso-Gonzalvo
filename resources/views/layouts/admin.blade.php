{{-- BLUSH & BLOOM SALON – ADMIN PORTAL layout (separate from the customer website) --}}
@php
    $admin = auth('admin')->user();
    $nameWords = preg_split('/\s+/', trim($admin?->name ?? 'Admin'));
    $initials = strtoupper(mb_substr($nameWords[0], 0, 1).(count($nameWords) > 1 ? mb_substr(end($nameWords), 0, 1) : ''));
    $isDark = ($admin?->theme ?? 'dark') !== 'light'; // dark by default
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $isDark ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · Blush &amp; Bloom Salon – Admin Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght,SOFT,WONK@0,9..144,300..500,100,0;1,9..144,300..500,100,0&family=Figtree:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body class="admin">
    <a class="skip-link" href="#admin-main">Skip to content</a>
    <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            @include('partials.logo', ['class' => 'admin-brand-mark'])
            <span><strong>Blush &amp; Bloom</strong><small>ADMIN PORTAL</small></span>
        </a>
        @php
            $links = [
                ['admin.dashboard', 'admin.dashboard', 'Dashboard', 'dashboard'],
                ['admin.appointments.index', 'admin.appointments.*', 'Appointments', 'calendar'],
                ['admin.customers.index', 'admin.customers.*', 'Customers', 'users'],
                ['admin.inquiries.index', 'admin.inquiries.*', 'Inquiries', 'mail'],
                ['admin.services.index', 'admin.services.*', 'Services', 'flower'],
                ['admin.automation-logs', 'admin.automation-logs', 'Automation Logs', 'activity'],
            ];
        @endphp
        <span class="admin-nav-label">Menu</span>
        <nav class="admin-nav">
            @foreach($links as [$route, $pattern, $label, $icon])
                <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'is-active' : '' }}" @if(request()->routeIs($pattern)) aria-current="page" @endif>
                    <span class="nav-icon">@include('partials.admin-icon', ['name' => $icon])</span>{{ $label }}
                </a>
            @endforeach
        </nav>

        {{-- Account menu: click the admin name to open Profile, Settings, theme switch and Log out --}}
        <div class="account-menu" id="accountMenu">
            <div class="account-popover" id="accountPopover" role="menu" aria-labelledby="accountMenuBtn" hidden>
                <div class="account-popover-head">
                    <span class="admin-avatar admin-avatar-lg" aria-hidden="true">{{ $initials }}</span>
                    <span><strong>{{ $admin?->name }}</strong><small>{{ $admin?->email }}</small></span>
                </div>
                <a role="menuitem" href="{{ route('admin.profile') }}" class="account-item">@include('partials.admin-icon', ['name' => 'user']) My profile</a>
                <a role="menuitem" href="{{ route('admin.settings') }}" class="account-item">@include('partials.admin-icon', ['name' => 'settings']) Settings</a>
                <a role="menuitem" href="{{ route('home') }}" target="_blank" rel="noopener" class="account-item">@include('partials.admin-icon', ['name' => 'external']) View website</a>
                <a role="menuitem" href="{{ route('admin.profile') }}#help" class="account-item">@include('partials.admin-icon', ['name' => 'help']) Help</a>
                <hr>
                <div class="theme-segment" role="radiogroup" aria-label="Theme" data-theme-url="{{ route('admin.settings.theme') }}">
                    <button type="button" role="radio" data-theme-choice="light" aria-checked="{{ $isDark ? 'false' : 'true' }}">@include('partials.admin-icon', ['name' => 'sun', 'size' => 16]) Light</button>
                    <button type="button" role="radio" data-theme-choice="dark" aria-checked="{{ $isDark ? 'true' : 'false' }}">@include('partials.admin-icon', ['name' => 'moon', 'size' => 16]) Dark</button>
                </div>
                <hr>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button role="menuitem" type="submit" class="account-item">@include('partials.admin-icon', ['name' => 'logout']) Log out</button>
                </form>
            </div>

            <button class="account-trigger" id="accountMenuBtn" type="button" aria-haspopup="menu" aria-expanded="false" aria-controls="accountPopover">
                <span class="admin-avatar" aria-hidden="true">{{ $initials }}</span>
                <span class="account-trigger-text"><strong>{{ $admin?->name }}</strong><small>Administrator</small></span>
                @include('partials.admin-icon', ['name' => 'chevron', 'class' => 'account-chevron', 'size' => 16])
            </button>
        </div>
    </aside>
    <div class="admin-sidebar-backdrop" id="adminBackdrop" hidden></div>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-menu-btn" id="adminMenuBtn" type="button" aria-controls="adminSidebar" aria-expanded="false" aria-label="Open navigation">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <small>BLUSH &amp; BLOOM SALON – ADMIN PORTAL</small>
                <h1>@yield('title', 'Dashboard')</h1>
            </div>
            <div class="admin-topbar-actions">@yield('actions')</div>
            <a class="topbar-profile" href="{{ route('admin.profile') }}" title="My profile" aria-label="My profile">
                <span class="admin-avatar" aria-hidden="true">{{ $initials }}</span>
            </a>
        </header>

        <main id="admin-main" class="admin-content" tabindex="-1">
            @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())
                <div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    <script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
