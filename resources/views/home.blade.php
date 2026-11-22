{{--
    HOME PAGE — Member 1: Sherwin Alvin Manbiotan
    Script: image slider/carousel  → public/js/home.js
    Page styles → public/css/home.css
--}}
@extends('layouts.app')

@section('title', 'Home')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/service-card.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')
    @php
        $slides = [
            ['image' => 'images/hero/hero-haircut.jpg', 'label' => 'Precision haircuts'],
            ['image' => 'images/hero/hero-styling.jpg', 'label' => 'Hair styling'],
            ['image' => 'images/hero/hero-facial.jpg', 'label' => 'Facial treatments'],
            ['image' => 'images/hero/hero-nails.jpg', 'label' => 'Nail care'],
            ['image' => 'images/hero/hero-massage.jpg', 'label' => 'Body massage'],
        ];
    @endphp

    {{-- HERO SLIDER --}}
    <section class="hero" id="heroSlider" aria-roledescription="carousel" aria-label="Blush & Bloom Salon highlights" data-interval="2000">
        <div class="hero-slides">
            @foreach($slides as $i => $slide)
                <div class="hero-slide {{ $i === 0 ? 'is-active' : '' }}" role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ count($slides) }}: {{ $slide['label'] }}" aria-hidden="{{ $i === 0 ? 'false' : 'true' }}"
                     style="background-image: url('{{ \App\Support\SalonImages::url($slide['image'], 1920) }}')"></div>
            @endforeach
        </div>
        <div class="hero-overlay" aria-hidden="true"></div>

        <div class="container hero-content">
            <span class="eyebrow hero-eyebrow">Your moment to bloom</span>
            <h1>Take time for yourself.<br><em>Feel beautiful. Feel renewed.</em></h1>
            <p>Thoughtful beauty and wellness experiences, designed to help you feel refreshed, confident, and cared for. Discover your next self-care moment at Blush &amp; Bloom Salon.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('booking') }}">Book an appointment</a>
                <a class="btn btn-ghost-light" href="{{ route('salon.index') }}">Explore our services</a>
            </div>
        </div>

        <div class="hero-indicators" role="tablist" aria-label="Choose a slide">
            @foreach($slides as $i => $slide)
                <button type="button" class="hero-indicator {{ $i === 0 ? 'is-active' : '' }}" role="tab" aria-selected="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Show slide {{ $i + 1 }}: {{ $slide['label'] }}" data-slide="{{ $i }}">
                    <span class="hero-indicator-fill"></span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- WELCOME --}}
    <section class="section welcome">
        <div class="container welcome-grid">
            <div class="welcome-media">
                <img src="{{ \App\Support\SalonImages::url('images/services/haircut-2.jpg', 1000) }}" alt="A stylist cutting a client's hair in a bright salon" loading="lazy">
                <img class="welcome-media-small" src="{{ \App\Support\SalonImages::url('images/services/facial-treatment-2.jpg', 700) }}" alt="A client relaxing during a facial treatment" loading="lazy">
            </div>
            <div>
                <span class="eyebrow">Welcome to Blush &amp; Bloom Salon</span>
                <h2>Beauty that feels like <em>care</em>, not a chore.</h2>
                <p>Blush &amp; Bloom Salon is a calm, welcoming space for hair, nails, skin, massage, and makeup. Every visit starts with listening — so the result feels like you, only more refreshed.</p>
                <p>Browse our services, see clear starting prices and durations, and request a time that suits you in just a few steps.</p>
                <a class="btn btn-outline" href="{{ url('/about') }}">Get to know us</a>
            </div>
        </div>
    </section>

    {{-- FEATURED SERVICES (from the database) --}}
    <section class="section section-soft">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Featured services</span>
                <h2>Find your kind of <em>care</em></h2>
                <p>Starting prices and estimated durations come straight from our salon menu.</p>
            </div>
            @if($featuredServices->isEmpty())
                <p class="sample-note" style="text-align:center">Our service menu is being updated. Please check back soon.</p>
            @else
                <div class="service-grid">
                    @foreach($featuredServices as $service)
                        @include('partials.service-card', ['service' => $service])
                    @endforeach
                </div>
            @endif
            <div class="center-action"><a class="btn btn-primary" href="{{ route('salon.index') }}">View all services</a></div>
        </div>
    </section>

    {{-- WHY CHOOSE US --}}
    <section class="section">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Why choose us</span>
                <h2>Why choose Blush &amp; Bloom Salon?</h2>
            </div>
            <div class="why-grid">
                <article class="why-card"><span class="why-icon" aria-hidden="true">✿</span><h3>Personal consultations</h3><p>We talk through what you want before we begin, so every service is shaped around you.</p></article>
                <article class="why-card"><span class="why-icon" aria-hidden="true">❖</span><h3>Clear, honest pricing</h3><p>Starting prices and estimated times are listed for every service — no guesswork.</p></article>
                <article class="why-card"><span class="why-icon" aria-hidden="true">☘</span><h3>A calm atmosphere</h3><p>Soft tones, natural light, and unhurried appointments help you truly relax.</p></article>
                <article class="why-card"><span class="why-icon" aria-hidden="true">◷</span><h3>Easy online booking</h3><p>See real available times, request a slot, and get updates about your booking by email.</p></article>
            </div>
        </div>
    </section>

    {{-- HOW BOOKING WORKS --}}
    <section class="section section-soft">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Simple steps</span>
                <h2>How appointment booking works</h2>
            </div>
            <ol class="steps">
                <li><span class="step-num">1</span><h3>Choose a service</h3><p>Explore the Salon page and pick the treatment you would like.</p></li>
                <li><span class="step-num">2</span><h3>Pick a date &amp; time</h3><p>Only times that are actually free are shown for your chosen service.</p></li>
                <li><span class="step-num">3</span><h3>Submit your request</h3><p>You receive a booking reference. Your appointment starts as <strong>Pending</strong>.</p></li>
                <li><span class="step-num">4</span><h3>Get confirmed</h3><p>Our team reviews and confirms your visit, and you are notified by email.</p></li>
            </ol>
            <div class="center-action"><a class="btn btn-primary" href="{{ route('booking') }}">Book your visit</a></div>
        </div>
    </section>

    {{-- TESTIMONIALS (sample content) --}}
    <section class="section">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Kind words</span>
                <h2>Customer testimonials</h2>
                <p class="sample-note">Sample testimonials for demonstration purposes only — not real customer reviews.</p>
            </div>
            <div class="testimonial-grid">
                @php
                    $testimonials = [
                        ['quote' => 'The stylist really listened to what I wanted. My haircut is so easy to manage now.', 'name' => 'Andrea Villanueva', 'service' => 'Haircut', 'image' => 'images/services/haircut-2.jpg', 'initials' => 'AV'],
                        ['quote' => 'Such a peaceful place. The massage was exactly the break I needed after a long week.', 'name' => 'Patricia Lim', 'service' => 'Body Massage', 'image' => 'images/services/body-massage.jpg', 'initials' => 'PL'],
                        ['quote' => 'Booking online took a minute, and I knew the price before I arrived. Lovely experience.', 'name' => 'Joy Ramirez', 'service' => 'Facial Treatment', 'image' => 'images/services/facial-treatment.jpg', 'initials' => 'JR'],
                    ];
                @endphp
                @foreach($testimonials as $t)
                    <figure class="testimonial">
                        <div class="stars" role="img" aria-label="Rated 5 out of 5 stars">
                            @for($s = 0; $s < 5; $s++)<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 2.5l2.94 5.96 6.58.96-4.76 4.64 1.12 6.55L12 17.52l-5.88 3.09 1.12-6.55L2.48 9.42l6.58-.96L12 2.5z"/></svg>@endfor
                        </div>
                        <blockquote>“{{ $t['quote'] }}”</blockquote>
                        <figcaption>
                            <span class="testimonial-avatar" aria-hidden="true" data-initials="{{ $t['initials'] }}">
                                <img src="{{ \App\Support\SalonImages::url($t['image'], 120, 120) }}" alt="" loading="lazy" width="48" height="48"
                                     onerror="this.parentElement.classList.add('no-photo'); this.remove();">
                            </span>
                            <span><strong>{{ $t['name'] }}</strong><small>{{ $t['service'] }}</small></span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CONTACT INFORMATION --}}
    <section class="section section-soft">
        <div class="container contact-strip">
            <div>
                <span class="eyebrow">Get in touch</span>
                <h2>We'd love to <em>hear from you</em></h2>
                <p>Questions about a service or your booking? Send us a message or ask Bloomie, our virtual assistant.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ url('/contact') }}">Contact us</a>
                    <button class="btn btn-outline" type="button" data-open-chat>Ask Bloomie</button>
                </div>
            </div>
            <dl class="contact-facts">
                <div><dt>Email</dt><dd>{{ $settings['business_email'] ?: 'To be announced' }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $settings['business_phone'] ?: 'To be announced' }}</dd></div>
                <div><dt>Address</dt><dd>{{ $settings['business_address'] ?: 'Address to be announced' }}</dd></div>
                <div><dt>Hours</dt><dd>{{ date('g:i A', strtotime($settings['opening_time'])) }} – {{ date('g:i A', strtotime($settings['closing_time'])) }}@if($settings['hours_are_sample'] === '1') <small>(sample hours)</small>@endif</dd></div>
            </dl>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/home.js') }}" defer></script>
@endpush
