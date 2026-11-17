{{--
    ABOUT PAGE — Member 2: Gerald EJ Gonzalvo
    Script: "Read More / Read Less" toggle (+ animated number counters)
    → public/js/about.js     Styles → public/css/about.css
--}}
@extends('layouts.app')

@section('title', 'About')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('content')
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">About us</span>
            <h1>A little more <em>bloom</em> in your everyday</h1>
            <p>Get to know the story, values, and people behind Blush &amp; Bloom Salon.</p>
        </div>
    </section>

    {{-- BACKGROUND --}}
    <section class="section">
        <div class="container about-story">
            <div class="about-story-media">
                <img src="{{ \App\Support\SalonImages::url('images/about/salon.jpg', 1000) }}" alt="A bright, calm salon interior with styling chairs and natural light" loading="lazy">
            </div>
            <div>
                <span class="eyebrow">Our background</span>
                <h2>Where self-care <em>comes first</em></h2>
                <p>Blush &amp; Bloom Salon was imagined as a place where beauty services feel personal, calm, and welcoming. We bring hair, nail, skin, massage, and makeup services together under one roof so our guests can care for themselves without the rush.</p>
                <div class="read-more" id="storyMore" data-read-more>
                    <p>Our approach is simple: listen first, then create. Every appointment begins with a short conversation about what you need, how much time you have, and how you want to feel when you leave. From there, our team tailors each service to you.</p>
                    <p>We also believe beauty should be easy to plan. That is why our full service menu — with clear starting prices and estimated durations — is available online, and why you can request an appointment at any time of day.</p>
                    <p>Blush &amp; Bloom Salon is a fictional business created for a Section AIBJ2 Laravel group project. The story above describes the salon concept.</p>
                </div>
                <button class="read-more-toggle" type="button" aria-expanded="false" aria-controls="storyMore" data-read-more-toggle>
                    <span data-label>Read More</span> <span aria-hidden="true" class="chevron">⌄</span>
                </button>
            </div>
        </div>
    </section>

    {{-- COUNTERS (values come from the database / settings) --}}
    <section class="counters" aria-label="Salon at a glance">
        <div class="container counter-grid">
            <div class="counter"><strong data-count-to="{{ $serviceCount }}">0</strong><span>Services offered</span></div>
            <div class="counter"><strong data-count-to="5">0</strong><span>Service categories</span></div>
            <div class="counter"><strong data-count-to="5">0</strong><span>Team specialties</span></div>
            <div class="counter"><strong data-count-to="24" data-suffix="/7">0</strong><span>Online booking requests</span></div>
        </div>
    </section>

    {{-- MISSION & VISION --}}
    <section class="section">
        <div class="container mv-grid">
            <article class="mv-card">
                <span class="mv-icon" aria-hidden="true">✿</span>
                <span class="eyebrow">Our mission</span>
                <h3>To help every guest feel refreshed, confident, and cared for.</h3>
                <p>We provide thoughtful beauty and wellness services in a calm space, with honest pricing and attentive, personal care at every visit.</p>
            </article>
            <article class="mv-card">
                <span class="mv-icon" aria-hidden="true">❀</span>
                <span class="eyebrow">Our vision</span>
                <h3>To be the neighbourhood salon people trust for their self-care moments.</h3>
                <p>We want Blush &amp; Bloom to be known for warm hospitality, consistent quality, and a booking experience that is simple from start to finish.</p>
            </article>
        </div>
    </section>

    {{-- CORE VALUES --}}
    <section class="section section-soft">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">What we value</span>
                <h2>Our core values</h2>
            </div>
            <div class="values-grid">
                <div class="value"><h3>Care</h3><p>We treat every guest with patience, kindness, and attention.</p></div>
                <div class="value"><h3>Honesty</h3><p>Clear prices, realistic timings, and straightforward advice.</p></div>
                <div class="value"><h3>Craft</h3><p>We take pride in neat, careful, and well-finished work.</p></div>
                <div class="value"><h3>Calm</h3><p>An unhurried, relaxing atmosphere for every appointment.</p></div>
            </div>
        </div>
    </section>

    {{-- TEAM --}}
    <section class="section">
        <div class="container">
            <div class="section-heading">
                <span class="eyebrow">Meet the team</span>
                <h2>The people behind your <em>glow</em></h2>
                <p class="sample-note">Sample staff profiles for this fictional salon. Photos are stock images of models.</p>
            </div>
            @php
                $team = [
                    ['name' => 'Bianca Reyes', 'photo' => 'images/team/bianca.jpg', 'role' => 'Lead Hair Stylist', 'tone' => 'rose', 'short' => 'Specialises in precision cuts and soft, natural styling.', 'more' => 'Bianca loves helping guests find a cut that fits their routine. She focuses on shape, texture, and easy at-home styling tips.'],
                    ['name' => 'Camille Santos', 'photo' => 'images/team/camille.jpg', 'role' => 'Nail Technician', 'tone' => 'sage', 'short' => 'Known for neat shaping and clean, glossy finishes.', 'more' => 'Camille pays close attention to cuticle care and polish detail, and enjoys recommending shades that suit each guest.'],
                    ['name' => 'Dana Villanueva', 'photo' => 'images/team/dana.jpg', 'role' => 'Esthetician', 'tone' => 'gold', 'short' => 'Leads our calm, relaxing facial experiences.', 'more' => 'Dana creates a soothing space for every facial and always checks skin preferences and sensitivities before starting.'],
                    ['name' => 'Elena Cruz', 'photo' => 'images/team/elena.jpg', 'role' => 'Massage Therapist', 'tone' => 'taupe', 'short' => 'Focuses on comfort-first, relaxing massage sessions.', 'more' => 'Elena adjusts pressure throughout each session and makes sure guests feel comfortable and at ease from start to finish.'],
                    ['name' => 'Francesca Lim', 'photo' => 'images/team/francesca.jpg', 'role' => 'Makeup Artist', 'tone' => 'rose', 'short' => 'Creates looks from fresh and natural to evening glam.', 'more' => 'Francesca builds each look around the occasion and the guest’s features, with careful blending for a long-lasting finish.'],
                ];
            @endphp
            <div class="team-grid">
                @foreach($team as $i => $member)
                    <article class="team-card">
                        @php $photo = \App\Support\SalonImages::url($member['photo'], 320, 320); @endphp
                        <div class="team-avatar team-avatar-{{ $member['tone'] }}" data-initials="{{ collect(explode(' ', $member['name']))->map(fn ($p) => $p[0])->implode('') }}">
                            @if($photo)
                                <img src="{{ $photo }}" alt="Portrait of {{ $member['name'] }}, {{ $member['role'] }}" loading="lazy" width="112" height="112"
                                     onerror="this.parentElement.classList.add('no-photo'); this.remove();">
                            @endif
                        </div>
                        <h3>{{ $member['name'] }}</h3>
                        <span class="team-role">{{ $member['role'] }}</span>
                        <p>{{ $member['short'] }}</p>
                        <div class="read-more" id="team-more-{{ $i }}" data-read-more><p>{{ $member['more'] }}</p></div>
                        <button class="read-more-toggle" type="button" aria-expanded="false" aria-controls="team-more-{{ $i }}" data-read-more-toggle>
                            <span data-label>Read More</span> <span aria-hidden="true" class="chevron">⌄</span>
                        </button>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="about-cta">
        <div class="container">
            <h2>Ready for your moment to bloom?</h2>
            <div class="about-cta-actions">
                <a class="btn btn-light" href="{{ route('booking') }}">Book an appointment</a>
                <a class="btn btn-ghost-light" href="{{ route('salon.index') }}">Explore services</a>
            </div>
        </div>
    </section>
@endsection
