@extends('layouts.app')

@section('title', 'My Account')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
@endpush

@section('content')
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">My account</span>
            <h1>Hello, <em>{{ $customer->name }}</em></h1>
            <p>Here are your Blush &amp; Bloom appointments.</p>
        </div>
    </section>

    <section class="section account-section">
        <div class="container account-grid">
            <aside class="account-card">
                @php
                    $w = preg_split('/\s+/', trim($customer->name));
                    $ini = strtoupper(mb_substr($w[0], 0, 1).(count($w) > 1 ? mb_substr(end($w), 0, 1) : ''));
                @endphp
                <div class="account-profile">
                    @if($customer->avatar)
                        <img class="account-avatar" src="{{ $customer->avatar }}" alt="" referrerpolicy="no-referrer">
                    @else
                        <span class="account-avatar" aria-hidden="true">{{ $ini }}</span>
                    @endif
                    <div><strong>{{ $customer->name }}</strong><small>Blush &amp; Bloom member</small></div>
                </div>
                <h2>Your details</h2>
                <dl>
                    <div><dt>Name</dt><dd>{{ $customer->name }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $customer->email }}</dd></div>
                    <div><dt>Phone</dt><dd>{{ $customer->phone ?: '—' }}</dd></div>
                    <div><dt>Member since</dt><dd>{{ $customer->created_at?->format('F Y') }}</dd></div>
                </dl>
                <a class="btn btn-primary" href="{{ route('booking') }}">Book a new appointment</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline" type="submit">Sign out</button>
                </form>
            </aside>

            <div class="account-card">
                <h2>Appointments</h2>
                @forelse($appointments as $appointment)
                    <article class="appt-row">
                        <div>
                            <strong>{{ $appointment->service_name }}</strong>
                            <span>{{ $appointment->appointment_date->format('D, M j, Y') }} · {{ $appointment->time_label }}</span>
                            <small>Ref: {{ $appointment->reference }}</small>
                        </div>
                        <span class="status-pill status-{{ strtolower($appointment->status) }}">{{ $appointment->status }}</span>
                    </article>
                @empty
                    <div class="account-empty">
                        <p>You don't have any appointments yet.</p>
                        <a class="btn btn-outline" href="{{ route('salon.index') }}">Explore services</a>
                    </div>
                @endforelse
                <p class="sample-note">To change or cancel an appointment, please <a href="{{ url('/contact') }}">contact the salon</a>.</p>
            </div>
        </div>
    </section>
@endsection
