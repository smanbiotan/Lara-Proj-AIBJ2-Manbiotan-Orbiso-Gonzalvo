{{-- BOOK NOW — step-by-step booking with a calendar (Laravel validates availability; works without n8n) --}}
@extends('layouts.app')

@section('title', 'Book an Appointment')
@section('handles_errors', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
@endpush

@php
    $val = fn (string $key, $fallback = null) => old($key, $draft[$key] ?? $fallback);
    $errorStep = match (true) {
        $errors->has('service') => 1,
        $errors->hasAny(['appointment_date', 'appointment_time']) => 2,
        $errors->hasAny(['name', 'email', 'phone', 'notes']) => 3,
        default => null,
    };
    $startStep = $errorStep ?? ($draft && !$customer ? 3 : 1);
@endphp

@section('content')
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Book now</span>
            <h1>Reserve your <em>self-care</em> moment</h1>
            <p>Pick a service, choose a day on the calendar, select a time, and confirm. Your booking starts as <strong>Pending</strong> until our team confirms it.</p>
        </div>
    </section>

    <section class="section booking-section">
        <div class="container booking-grid">
            <form class="booking-card" id="bookingForm" method="POST" action="{{ route('booking.store') }}"
                  data-availability-url="{{ url('/api/appointments/availability') }}"
                  data-start-step="{{ $startStep }}" novalidate>
                @csrf
                <input type="hidden" name="submission_token" value="{{ $submissionToken }}">

                @if($errors->any())
                    <div class="flash-inline" role="alert">
                        <strong>Please check your booking details:</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                @if($draft && !$customer)
                    <div class="draft-note" role="status">
                        <span aria-hidden="true">✿</span>
                        <div><strong>Your details are saved.</strong> Sign in or create an account to confirm your appointment.</div>
                        <button type="button" class="btn btn-primary btn-sm" data-open-auth-gate>Sign in to continue</button>
                    </div>
                @endif

                {{-- Progress --}}
                <ol class="stepper" aria-label="Booking steps">
                    <li data-step-indicator="1"><span>1</span> Service</li>
                    <li data-step-indicator="2"><span>2</span> Date &amp; time</li>
                    <li data-step-indicator="3"><span>3</span> Your details</li>
                </ol>

                {{-- STEP 1: Service --}}
                <fieldset class="wizard-panel" data-step="1">
                    <legend><span class="legend-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.12 15.88M14.47 14.48 20 20M8.12 8.12 12 12"/></svg>
                    </span> Choose a service</legend>
                    <div class="field @error('service') has-error @enderror">
                        <div class="service-options" role="radiogroup" aria-label="Services">
                            @foreach($services as $service)
                                <label class="service-option">
                                    <input type="radio" name="service" value="{{ $service->slug }}" data-name="{{ $service->name }}"
                                           data-price="{{ $service->price_label }}" data-duration="{{ $service->duration_label }}"
                                           @checked($selectedSlug === $service->slug) required>
                                    <span class="service-option-body">
                                        <img src="{{ $service->image_url }}" alt="" loading="lazy">
                                        <span class="service-option-text">
                                            <strong>{{ $service->name }}</strong>
                                            <small>{{ $service->price_label }} · {{ $service->duration_label }}</small>
                                        </span>
                                        <span class="service-option-check" aria-hidden="true">✓</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="error-text" data-error-for="service" hidden>Please choose a service.</p>
                    </div>
                    <div class="wizard-nav">
                        <span></span>
                        <button type="button" class="btn btn-primary" data-next>Next</button>
                    </div>
                </fieldset>

                {{-- STEP 2: Calendar + time --}}
                <fieldset class="wizard-panel" data-step="2">
                    <legend><span class="legend-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span> Select date</legend>

                    <div class="date-time-grid">
                        <div class="field @error('appointment_date') has-error @enderror">
                            <input type="hidden" id="appointment_date" name="appointment_date" value="{{ $val('appointment_date') }}">
                            <div class="calendar" id="bookingCalendar"
                                 data-min="{{ $today }}" data-max="{{ $maxDate }}"
                                 data-open-days="{{ implode(',', $hours['open_days']) }}">
                                <div class="calendar-head">
                                    <button type="button" class="calendar-nav" data-cal-prev aria-label="Previous month">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                    </button>
                                    <strong class="calendar-title" data-cal-title aria-live="polite"></strong>
                                    <button type="button" class="calendar-nav" data-cal-next aria-label="Next month">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                    </button>
                                </div>
                                <div class="calendar-weekdays" aria-hidden="true">
                                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                </div>
                                <div class="calendar-days" data-cal-days role="grid" aria-label="Choose a date"></div>
                            </div>
                            <noscript>
                                <label for="appointment_date_fallback">Appointment date</label>
                                <input id="appointment_date_fallback" name="appointment_date" type="date" value="{{ $val('appointment_date') }}" min="{{ $today }}" max="{{ $maxDate }}">
                            </noscript>
                            <p class="error-text" data-error-for="appointment_date" hidden>Please pick a date on the calendar.</p>
                        </div>

                        <div class="field time-field @error('appointment_time') has-error @enderror">
                            <span class="label" id="timeLabel">Available times</span>
                            <p class="time-date-label" id="timeDateLabel">Pick a date to see open times.</p>
                            <div class="slot-grid" id="slotGrid" role="radiogroup" aria-labelledby="timeLabel" data-old="{{ $val('appointment_time') }}">
                                <p class="slot-message">Choose a service and date to see available times.</p>
                            </div>
                            <noscript>
                                <label for="appointment_time_fallback">Time (HH:MM)</label>
                                <input id="appointment_time_fallback" name="appointment_time" type="time" step="1800" value="{{ $val('appointment_time') }}">
                            </noscript>
                            <p class="error-text" data-error-for="appointment_time" hidden>Please choose a time.</p>
                        </div>
                    </div>

                    <div class="wizard-nav">
                        <button type="button" class="btn btn-ghost" data-back>Back</button>
                        <button type="button" class="btn btn-primary" data-next>Next</button>
                    </div>
                </fieldset>

                {{-- STEP 3: Details --}}
                <fieldset class="wizard-panel" data-step="3">
                    <legend><span class="legend-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    </span> Your details</legend>
                    @if($customer)
                        <p class="signed-in-note">Signed in as <strong>{{ $customer->name }}</strong>. This booking will appear in <a href="{{ route('account') }}">My Account</a>.</p>
                    @else
                        <p class="signed-in-note">You'll sign in or create a free account on the next step, so you can track this booking later.</p>
                    @endif
                    <div class="field-row">
                        <div class="field @error('name') has-error @enderror">
                            <label for="name">Full name</label>
                            <input id="name" name="name" type="text" value="{{ $val('name', $customer?->name) }}" autocomplete="name" required maxlength="255">
                        </div>
                        <div class="field @error('email') has-error @enderror">
                            <label for="email">Email address</label>
                            <input id="email" name="email" type="email" value="{{ $customer?->email ?? $val('email') }}" autocomplete="email" required maxlength="255" @if($customer) readonly @endif>
                        </div>
                    </div>
                    <div class="field @error('phone') has-error @enderror">
                        <label for="phone">Contact number</label>
                        <input id="phone" name="phone" type="tel" value="{{ $val('phone', $customer?->phone) }}" autocomplete="tel" placeholder="e.g. 0917 123 4567" required maxlength="30">
                    </div>
                    <div class="field">
                        <label for="notes">Additional notes <small>(optional)</small></label>
                        <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Allergies, preferred stylist, special occasion…">{{ $val('notes') }}</textarea>
                    </div>

                    <div class="wizard-nav">
                        <button type="button" class="btn btn-ghost" data-back>Back</button>
                        <button class="btn btn-primary booking-submit" id="bookingSubmit" type="submit">
                            {{ $customer ? 'Confirm Appointment' : 'Continue' }}
                        </button>
                    </div>
                    <p class="booking-fineprint">By booking, you agree that the salon may contact you about this appointment. Bookings are confirmed by email after review.</p>
                </fieldset>
            </form>

            <aside class="booking-summary" aria-live="polite">
                <div class="summary-card">
                    <span class="eyebrow">Your booking</span>
                    <dl>
                        <div><dt>Service</dt><dd id="sumService">Not selected</dd></div>
                        <div><dt>Starting price</dt><dd id="sumPrice">—</dd></div>
                        <div><dt>Duration</dt><dd id="sumDuration">—</dd></div>
                        <div><dt>Date</dt><dd id="sumDate">—</dd></div>
                        <div><dt>Time</dt><dd id="sumTime">—</dd></div>
                    </dl>
                </div>
                <div class="summary-card summary-info">
                    <h3>Good to know</h3>
                    <ul>
                        <li>Only real, available times are shown.</li>
                        <li>Appointments are open {{ date('g:i A', strtotime($hours['opening_time'])) }} – {{ date('g:i A', strtotime($hours['closing_time'])) }}@if($hours['is_sample']) (sample hours)@endif.</li>
                        <li>You'll receive a booking reference right away.</li>
                        <li>Status starts as <strong>Pending</strong> until the salon confirms.</li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>

    {{-- Sign-in dialog for guests: appears after they fill in the booking, before confirming --}}
    @unless($customer)
        <div class="auth-gate" id="authGate" @if($showAuthGate) data-open="true" @endif hidden>
            <div class="auth-gate-backdrop" data-close-auth-gate></div>
            <div class="auth-gate-card" role="dialog" aria-modal="true" aria-labelledby="authGateTitle" aria-describedby="authGateText">
                <button type="button" class="auth-gate-close" data-close-auth-gate aria-label="Close">×</button>
                <span class="auth-gate-mark" aria-hidden="true">@include('partials.logo')</span>
                <h2 id="authGateTitle">Almost there!</h2>
                <p id="authGateText">Sign in or create a free account to confirm your appointment. Your booking details are saved — you'll go straight to the confirmation step.</p>

                @if($googleEnabled)
                    <a class="btn-google" href="{{ route('google.redirect') }}">
                        @include('partials.google-icon') <span>Continue with Google</span>
                    </a>
                @else
                    <span class="btn-google is-disabled" aria-disabled="true" title="Google sign-in is not set up yet">
                        @include('partials.google-icon') <span>Continue with Google</span>
                    </span>
                    <small class="auth-gate-hint">Google sign-in will be available once the salon connects it.</small>
                @endif

                <div class="auth-divider"><span>or</span></div>

                <a class="btn btn-primary auth-gate-btn" href="{{ route('login') }}">Sign in with email</a>
                <a class="btn btn-outline auth-gate-btn" href="{{ route('register') }}">Create an account</a>
                <button type="button" class="auth-gate-later" data-close-auth-gate>Edit my booking details</button>
            </div>
        </div>
    @endunless
@endsection

@push('scripts')
    <script src="{{ asset('js/booking.js') }}" defer></script>
@endpush
