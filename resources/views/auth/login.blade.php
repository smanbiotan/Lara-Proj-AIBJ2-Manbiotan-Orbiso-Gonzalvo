{{-- Customer Sign In (customer accounts only — administrators use /admin/login) --}}
@extends('layouts.app')

@section('title', 'Sign In')
@section('handles_errors', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
    <section class="auth-section">
        <div class="container auth-grid">
            <div class="auth-visual" style="background-image:url('{{ \App\Support\SalonImages::url('images/services/makeup-2.jpg', 1100) }}')">
                <div class="auth-visual-inner">
                    <span class="eyebrow">Welcome back</span>
                    <h2>Your self-care, all in one place.</h2>
                    <p>Sign in to see your appointment history and book faster next time.</p>
                </div>
            </div>
            <div class="auth-card">
                <h1>Sign In</h1>
                <p class="auth-sub">Sign in to your Blush &amp; Bloom customer account.</p>
                @if(session()->has('booking_draft'))<p class="auth-booking-note">✿ Your booking details are saved. Sign in to confirm your appointment.</p>@endif

                @if(\App\Http\Controllers\Auth\GoogleAuthController::enabled())
                    <a class="btn-google" href="{{ route('google.redirect') }}">@include('partials.google-icon') <span>Continue with Google</span></a>
                    <div class="auth-divider"><span>or</span></div>
                @endif

                @if($errors->any())<div class="auth-error" role="alert">{{ $errors->first() }}</div>@endif

                <form method="POST" action="{{ route('login.store') }}" novalidate>
                    @csrf
                    <div class="field">
                        <label for="email">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus maxlength="255">
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="password-wrap">
                            <input id="password" name="password" type="password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">Show</button>
                        </div>
                    </div>
                    <label class="check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                    <button class="btn btn-primary auth-submit" type="submit">Sign In</button>
                </form>

                <p class="auth-switch">New to Blush &amp; Bloom? <a href="{{ route('register') }}">Create an account</a></p>
                <p class="auth-switch">Want to browse first? <a href="{{ route('salon.index') }}">View our services</a></p>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}" defer></script>
@endpush
