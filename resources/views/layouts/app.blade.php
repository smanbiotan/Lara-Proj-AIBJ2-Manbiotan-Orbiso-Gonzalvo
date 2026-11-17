{{--
    MASTER LAYOUT — Blush & Bloom Salon
    Member 1: Sherwin Alvin Manbiotan (Project Setup, Shared Layout, Home Page)
    Every customer page uses: @extends('layouts.app') + @section('content')
    Page assets are added with @push('styles') and @push('scripts').
--}}
<!DOCTYPE html>
<html lang="en" data-theme="dark">{{-- dark is the default; a saved choice in localStorage (bb-theme) overrides it --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Blush & Bloom Salon — thoughtful beauty and wellness experiences. Explore services and book your appointment online.')">
    <title>@hasSection('title')@yield('title') · @endif Blush &amp; Bloom Salon</title>

    {{-- Apply the saved theme before the page paints (prevents a flash of the wrong theme). --}}
    <script>
        (function () { document.documentElement.classList.add('js'); try { var t = localStorage.getItem('bb-theme'); if (t === 'dark' || t === 'light') document.documentElement.setAttribute('data-theme', t); } catch (e) {} })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght,SOFT,WONK@0,9..144,300..500,100,0;1,9..144,300..500,100,0&family=Figtree:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
    @stack('styles')
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>

    {{-- Header: Brand | Home | About | Salon | Contact | Sign In | Book Now (no search, no admin link) --}}
    <header class="site-header" id="siteHeader">
        <div class="container header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Blush & Bloom Salon — Home">
                @include('partials.logo')
                <span class="brand-text"><strong>Blush &amp; Bloom</strong><small>SALON</small></span>
            </a>

            @php
                $navItems = [
                    ['url' => url('/'), 'label' => 'Home', 'active' => request()->is('/')],
                    ['url' => url('/about'), 'label' => 'About', 'active' => request()->is('about')],
                    ['url' => url('/salon'), 'label' => 'Salon', 'active' => request()->is('salon', 'salon/*')],
                    ['url' => url('/contact'), 'label' => 'Contact', 'active' => request()->is('contact')],
                ];
                $customer = auth('customer')->user();
            @endphp

            <nav class="main-nav" aria-label="Main navigation">
                <ul>
                    @foreach($navItems as $item)
                        <li><a href="{{ $item['url'] }}" @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="header-actions">
                @if($customer)
                    <a class="header-link" href="{{ route('account') }}">My Account</a>
                @else
                    <a class="header-link" href="{{ route('login') }}">Sign In</a>
                @endif
                <a class="btn btn-primary" href="{{ route('booking') }}">Book Now</a>
                {{-- Light / dark mode switch — right of Book Now (circular reveal animation) --}}
                <button class="theme-switch" id="themeToggle" type="button" aria-pressed="false" aria-label="Switch to dark mode" title="Switch to dark mode">
                    <svg class="sun-moon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
                        <mask id="themeMoonMask" class="moon">
                            <rect x="0" y="0" width="100%" height="100%" fill="white"/>
                            <circle class="moon-cut" cx="24" cy="10" r="6" fill="black"/>
                        </mask>
                        <circle class="sun-core" cx="12" cy="12" r="6" mask="url(#themeMoonMask)" fill="currentColor"/>
                        <g class="sun-rays" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </g>
                    </svg>
                </button>
            </div>

            <button class="menu-toggle" id="menuToggle" type="button" aria-expanded="false" aria-controls="mobileMenu" aria-label="Open menu">
                <span></span>
            </button>
        </div>
    </header>

    {{-- Mobile navigation: same links as desktop --}}
    <nav class="mobile-menu" id="mobileMenu" aria-label="Mobile navigation">
        <ul>
            @foreach($navItems as $item)
                <li><a href="{{ $item['url'] }}" @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a></li>
            @endforeach
        </ul>
        <div class="mobile-actions">
            @if($customer)
                <a class="btn btn-outline" href="{{ route('account') }}">My Account</a>
            @else
                <a class="btn btn-outline" href="{{ route('login') }}">Sign In</a>
            @endif
            <a class="btn btn-primary" href="{{ route('booking') }}">Book Now</a>
        </div>
    </nav>

    @if(session('success'))
        <div class="flash flash-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any() && !View::hasSection('handles_errors'))
        <div class="flash flash-error" role="alert">
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a class="brand" href="{{ route('home') }}" aria-label="Blush & Bloom Salon — Home">
                        @include('partials.logo')
                        <span class="brand-text"><strong>Blush &amp; Bloom</strong><small>SALON</small></span>
                    </a>
                    <p style="margin-top:18px">Thoughtful beauty and wellness experiences, designed to help you feel refreshed, confident, and cared for.</p>
                </div>
                <div>
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ url('/about') }}">About</a></li>
                        <li><a href="{{ route('salon.index') }}">Salon</a></li>
                        <li><a href="{{ url('/contact') }}">Contact</a></li>
                        <li><a href="{{ route('booking') }}">Book Now</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Project</h4>
                    <ul>
                        <li>Section AIBJ2</li>
                        <li>IT-110 Group Project</li>
                        <li>A fictional salon for a school project</li>
                    </ul>
                </div>
                <div>
                    <h4>Group Members</h4>
                    <ul class="member-list">
                        <li>Sherwin Alvin Manbiotan <small>Member 1 · Group Leader · Layout &amp; Home</small></li>
                        <li>Gerald EJ Gonzalvo <small>Member 2 · About Page</small></li>
                        <li>Anthony Josh Orbiso <small>Member 3 · Contact Page</small></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} Blush &amp; Bloom Salon · Built with Laravel by Section AIBJ2</span>
                <span>Butuan City, Agusan del Norte, Philippines</span>
            </div>
        </div>
    </footer>

    <button class="back-to-top" id="backToTop" type="button" aria-label="Back to top">↑</button>

    @include('partials.chatbot')

    <script src="{{ asset('js/layout.js') }}" defer></script>
    <script src="{{ asset('js/chatbot.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
