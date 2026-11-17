@extends('layouts.app')

@section('title', 'Create Account')
@section('handles_errors', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
    <section class="auth-section">
        <div class="container auth-grid">
            <div class="auth-visual" style="background-image:url('{{ \App\Support\SalonImages::url('images/services/nail-care-2.jpg', 1100) }}')">
                <div class="auth-visual-inner">
                    <span class="eyebrow">Join us</span>
                    <h2>Book faster and keep track of every visit.</h2>
                    <p>Already booked as a guest? Register with the same email to see those bookings too.</p>
                </div>
            </div>
            <div class="auth-card">
                <h1>Create Account</h1>
                <p class="auth-sub">It only takes a minute.</p>
                @if(session()->has('booking_draft'))<p class="auth-booking-note">✿ Your booking details are saved. Create your account to confirm your appointment.</p>@endif

                @if(\App\Http\Controllers\Auth\GoogleAuthController::enabled())
                    <a class="btn-google" href="{{ route('google.redirect') }}">@include('partials.google-icon') <span>Continue with Google</span></a>
                    <div class="auth-divider"><span>or</span></div>
                @endif

                @if($errors->any())
                    <div class="auth-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" novalidate>
                    @csrf
                    <div class="field"><label for="name">Full name</label><input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required maxlength="255"></div>
                    <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255"></div>
                    <div class="field"><label for="phone">Contact number</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required maxlength="30"></div>
                    <div class="field">
                        <label for="password">Password <small>(at least 8 characters, with letters and numbers)</small></label>
                        <div class="password-wrap">
                            <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8">
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Show password">Show</button>
                        </div>
                    </div>
                    <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
                    <button class="btn btn-primary auth-submit" type="submit">Create Account</button>
                </form>
                <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}" defer></script>
@endpush
