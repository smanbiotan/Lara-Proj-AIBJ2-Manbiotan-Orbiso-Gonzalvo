{{-- Review & confirm — shown after a guest signs in or creates an account --}}
@extends('layouts.app')

@section('title', 'Confirm Your Appointment')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
@endpush

@section('content')
    <section class="section">
        <div class="container confirm-wrap">
            <div class="confirm-card review-card">
                <span class="eyebrow">Last step</span>
                <h1>Confirm your appointment</h1>
                <p>You're signed in as <strong>{{ $customer->name }}</strong>. Please check the details below, then confirm.</p>

                @unless($stillAvailable)
                    <div class="flash-inline" role="alert">That time was just taken. Please <a href="{{ route('booking') }}">choose another time</a> — your other details are saved.</div>
                @endunless

                <div class="review-service">
                    <img src="{{ $service->image_url }}" alt="" loading="lazy">
                    <div>
                        <strong>{{ $service->name }}</strong>
                        <span>{{ $service->price_label }} · {{ $service->duration_label }}</span>
                    </div>
                </div>

                <dl class="confirm-details">
                    <div><dt>Date</dt><dd>{{ $dateLabel }}</dd></div>
                    <div><dt>Time</dt><dd>{{ $timeLabel }}</dd></div>
                    <div><dt>Name</dt><dd>{{ $draft['name'] }}</dd></div>
                    <div><dt>Contact number</dt><dd>{{ $draft['phone'] }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $customer->email }}</dd></div>
                    <div><dt>Notes</dt><dd>{{ $draft['notes'] ?: '—' }}</dd></div>
                </dl>

                <form method="POST" action="{{ route('booking.confirm') }}" class="confirm-actions" id="confirmForm">
                    @csrf
                    <a class="btn btn-outline" href="{{ route('booking') }}">Edit details</a>
                    <button class="btn btn-primary" type="submit" @disabled(!$stillAvailable)>Confirm Appointment</button>
                </form>
                <p class="booking-fineprint">Your booking starts as <strong>Pending</strong> until the salon confirms it by email.</p>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.getElementById('confirmForm')?.addEventListener('submit', function (e) {
            const btn = this.querySelector('button[type=submit]');
            if (btn.disabled) { e.preventDefault(); return; }
            btn.disabled = true; btn.textContent = 'Confirming…';
        });
    </script>
@endpush
