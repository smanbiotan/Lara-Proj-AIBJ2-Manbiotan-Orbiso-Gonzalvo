{{--
    ABOUT PAGE — Member 2: Gerald EJ Gonzalvo
    Script: "Read More / Read Less" toggle (+ animated number counters)
    → public/js/about.js     Styles → public/css/about.css
--}}
@extends('layouts.app')

@section('title', 'About')


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

@endsection
