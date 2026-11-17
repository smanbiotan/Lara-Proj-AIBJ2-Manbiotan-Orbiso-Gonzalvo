{{--
    CONTACT PAGE — Member 3: Anthony Josh Orbiso
    Script: client-side form validation (empty fields, email format)
    with error messages and a success message → public/js/contact.js
    Styles → public/css/contact.css
    The form is also validated by Laravel and saved to the `inquiries` table.
--}}
@extends('layouts.app')

@section('title', 'Contact')
@section('handles_errors', '1')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@section('content')
    @php
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $openDays = count($hours['open_days']) === 7 ? 'Monday – Sunday' : collect($hours['open_days'])->map(fn ($d) => $dayNames[$d])->implode(', ');
    @endphp

    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Contact us</span>
            <h1>We'd love to <em>hear from you</em></h1>
            <p>Have a question about a service, pricing, or your appointment? Send us a message and our team will get back to you.</p>
        </div>
    </section>

    <section class="section contact-section">
        <div class="container contact-grid">
            {{-- Business contact details --}}
            <aside class="contact-info" aria-labelledby="contactDetailsTitle">
                <h2 id="contactDetailsTitle">Contact details</h2>
                <ul class="contact-list">
                    <li>
                        <span class="contact-icon" aria-hidden="true">@include('partials.admin-icon', ['name' => 'mail', 'size' => 19])</span>
                        <div><strong>Email</strong><span>{{ $settings['business_email'] ?: 'To be announced' }}</span></div>
                    </li>
                    <li>
                        <span class="contact-icon" aria-hidden="true">@include('partials.admin-icon', ['name' => 'phone', 'size' => 19])</span>
                        <div><strong>Phone</strong><span>{{ $settings['business_phone'] ?: 'To be announced' }}</span></div>
                    </li>
                    <li>
                        <span class="contact-icon" aria-hidden="true">@include('partials.admin-icon', ['name' => 'pin', 'size' => 19])</span>
                        <div><strong>Address</strong><span>{{ $settings['business_address'] ?: 'Salon address to be announced' }}</span></div>
                    </li>
                    <li>
                        <span class="contact-icon" aria-hidden="true">@include('partials.admin-icon', ['name' => 'clock', 'size' => 19])</span>
                        <div>
                            <strong>Opening hours</strong>
                            <span>{{ $openDays }}<br>{{ date('g:i A', strtotime($hours['opening_time'])) }} – {{ date('g:i A', strtotime($hours['closing_time'])) }}</span>
                            @if($hours['is_sample'])<small class="sample-note">Sample schedule — to be confirmed by the salon.</small>@endif
                        </div>
                    </li>
                </ul>
                <div class="contact-note">
                    <strong>Prefer to book directly?</strong>
                    <p>See real available times and request your appointment online.</p>
                    <a class="btn btn-primary btn-sm" href="{{ route('booking') }}">Book Now</a>
                </div>
            </aside>

            {{-- Contact form --}}
            <div class="contact-form-card">
                <h2>Send us a message</h2>
                <p class="form-hint">All fields are required.</p>

                <div class="form-success" id="contactSuccess" role="status" tabindex="-1" @if(!session('success')) hidden @endif>
                    <span class="form-success-icon" aria-hidden="true">✓</span>
                    <div><strong>Message sent!</strong><p data-success-text>{{ session('success') ?: 'Thank you! Your message has been sent.' }}</p></div>
                </div>
                <div class="form-alert" id="contactAlert" role="alert" hidden></div>

                <form id="contactForm" action="{{ route('contact.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="field-row">
                        <div class="field @error('name') has-error @enderror">
                            <label for="name">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" aria-describedby="name-error">
                            <span class="error-text" id="name-error" data-error-for="name">@error('name'){{ $message }}@enderror</span>
                        </div>
                        <div class="field @error('email') has-error @enderror">
                            <label for="email">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" aria-describedby="email-error">
                            <span class="error-text" id="email-error" data-error-for="email">@error('email'){{ $message }}@enderror</span>
                        </div>
                    </div>
                    <div class="field @error('subject') has-error @enderror">
                        <label for="subject">Subject</label>
                        <input id="subject" name="subject" type="text" value="{{ old('subject') }}" maxlength="255" aria-describedby="subject-error">
                        <span class="error-text" id="subject-error" data-error-for="subject">@error('subject'){{ $message }}@enderror</span>
                    </div>
                    <div class="field @error('message') has-error @enderror">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="6" maxlength="5000" aria-describedby="message-error">{{ old('message') }}</textarea>
                        <span class="error-text" id="message-error" data-error-for="message">@error('message'){{ $message }}@enderror</span>
                    </div>
                    {{-- Honeypot: hidden from people, bots tend to fill it in --}}
                    <div class="hp-field" aria-hidden="true"><label for="website">Website</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>

                    <button class="btn btn-primary contact-submit" type="submit">
                        <span data-submit-label>Send Message</span>
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/contact.js') }}" defer></script>
@endpush
