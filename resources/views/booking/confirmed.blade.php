@extends('layouts.app')

@section('title', 'Booking Received')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
@endpush

@section('content')
    <section class="section">
        <div class="container confirm-wrap">
            <div class="confirm-card">
                <span class="confirm-icon" aria-hidden="true">✓</span>
                <span class="eyebrow">Booking request received</span>
                <h1>Thank you, {{ $appointment->customer?->name }}!</h1>
                <p>Your appointment request has been saved. It is <strong>Pending</strong> — our team will review it and confirm by email.</p>

                <div class="confirm-ref">
                    <small>Your booking reference</small>
                    <strong>{{ $appointment->reference }}</strong>
                    <small>Keep this reference. You'll need it with your email to check your status with our assistant.</small>
                </div>

                <dl class="confirm-details">
                    <div><dt>Service</dt><dd>{{ $appointment->service_name }}</dd></div>
                    <div><dt>Date</dt><dd>{{ $appointment->appointment_date->format('l, F j, Y') }}</dd></div>
                    <div><dt>Time</dt><dd>{{ $appointment->time_label }} ({{ $appointment->duration_minutes }} min)</dd></div>
                    <div><dt>Status</dt><dd><span class="status-pill status-{{ strtolower($appointment->status) }}">{{ $appointment->status }}</span></dd></div>
                </dl>

                <div class="confirm-actions">
                    <a class="btn btn-primary" href="{{ route('home') }}">Back to Home</a>
                    @auth('customer')
                        <a class="btn btn-outline" href="{{ route('account') }}">View my bookings</a>
                    @else
                        <a class="btn btn-outline" href="{{ route('register') }}">Create an account</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
@endsection
