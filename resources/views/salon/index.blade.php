{{-- SALON — service catalogue (all data comes from the `services` table) --}}
@extends('layouts.app')

@section('title', 'Salon Services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/service-card.css') }}">
    <link rel="stylesheet" href="{{ asset('css/salon.css') }}">
@endpush

@section('content')
    <section class="page-hero salon-hero">
        <div class="container">
            <span class="eyebrow">Our salon</span>
            <h1>Services made for your <em>moment</em></h1>
            <p>Explore our hair, nail, skin, spa, and makeup services. Every service shows a starting price and estimated duration, so you can plan your visit with confidence.</p>
        </div>
    </section>

    <section class="section salon-catalogue">
        <div class="container">
            @if($services->isEmpty())
                <div class="empty-state card">
                    <h2>Our menu is being refreshed</h2>
                    <p>No services are available for booking right now. Please check back soon or contact us.</p>
                    <a class="btn btn-primary" href="{{ url('/contact') }}">Contact us</a>
                </div>
            @else
                <div class="category-filter" role="toolbar" aria-label="Filter services by category">
                    <button type="button" class="filter-btn is-active" data-filter="all" aria-pressed="true">All services <span class="filter-count">{{ $services->count() }}</span></button>
                    @foreach($categories as $key => $label)
                        <button type="button" class="filter-btn" data-filter="{{ $key }}" aria-pressed="false">{{ $label }} <span class="filter-count">{{ $services->where('category', $key)->count() }}</span></button>
                    @endforeach
                </div>
                <p class="filter-status sr-only" id="filterStatus" aria-live="polite"></p>

                <div class="service-grid" id="serviceGrid">
                    @foreach($services as $service)
                        @include('partials.service-card', ['service' => $service])
                    @endforeach
                </div>
                <p class="sample-note salon-note">Prices are starting prices for this demonstration salon. Final pricing may vary depending on hair length, design, or add-ons.</p>
            @endif
        </div>
    </section>

    <section class="salon-help">
        <div class="container salon-help-inner">
            <div>
                <h2>Not sure which service to choose?</h2>
                <p>Ask Bloomie, our virtual assistant, or send us a message — we're happy to help.</p>
            </div>
            <div class="salon-help-actions">
                <button class="btn btn-light" type="button" data-open-chat>Ask Bloomie</button>
                <a class="btn btn-ghost-light" href="{{ url('/contact') }}">Contact us</a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/salon.js') }}" defer></script>
@endpush
