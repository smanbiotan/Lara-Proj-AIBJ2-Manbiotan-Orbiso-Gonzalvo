{{-- Individual service detail page: /salon/{slug} (database-driven) --}}
@extends('layouts.app')

@section('title', $service->name)
@section('meta_description', $service->short_description)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/service-card.css') }}">
    <link rel="stylesheet" href="{{ asset('css/service.css') }}">
@endpush

@section('content')
    @php $slides = $service->slides; @endphp

    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('salon.index') }}">Salon</a></li>
                <li aria-current="page">{{ $service->name }}</li>
            </ol>
        </nav>
    </div>

    {{-- INTRO + SLIDESHOW --}}
    <section class="service-hero">
        <div class="container service-hero-grid">
            <div class="gallery" id="serviceGallery" aria-roledescription="carousel" aria-label="{{ $service->name }} photos" data-interval="5000">
                <div class="gallery-stage">
                    @foreach($slides as $i => $slide)
                        <figure class="gallery-slide {{ $i === 0 ? 'is-active' : '' }}" role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ count($slides) }}" aria-hidden="{{ $i === 0 ? 'false' : 'true' }}">
                            <img src="{{ $slide['url'] }}" alt="{{ $service->name }}: {{ $slide['caption'] }}" @if($i > 0) loading="lazy" @endif>
                            <figcaption>{{ $slide['caption'] }}</figcaption>
                        </figure>
                    @endforeach
                    @if(count($slides) > 1)
                        <button class="gallery-nav gallery-prev" type="button" aria-label="Previous photo" data-gallery-prev>‹</button>
                        <button class="gallery-nav gallery-next" type="button" aria-label="Next photo" data-gallery-next>›</button>
                        <div class="gallery-dots" aria-hidden="true">
                            @foreach($slides as $i => $slide)<span class="gallery-dot {{ $i === 0 ? 'is-active' : '' }}"></span>@endforeach
                        </div>
                    @endif
                </div>
                @if(count($slides) > 1)
                    <div class="gallery-thumbs" role="group" aria-label="Choose a photo">
                        @foreach($slides as $i => $slide)
                            <button type="button" class="gallery-thumb {{ $i === 0 ? 'is-active' : '' }}" data-gallery-thumb="{{ $i }}" aria-label="Show photo {{ $i + 1 }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}">
                                <img src="{{ $slide['url'] }}" alt="" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="service-intro">
                <span class="eyebrow">{{ $service->category_label }}</span>
                <h1>{{ $service->name }}</h1>
                <p class="service-lead">{{ $service->intro ?: $service->short_description }}</p>
                <dl class="service-facts">
                    <div><dt>Starting price</dt><dd class="price">{{ $service->price_label }}</dd></div>
                    <div><dt>Estimated duration</dt><dd>{{ $service->duration_label }}</dd></div>
                </dl>
                <div class="service-actions">
                    <a class="btn btn-primary" href="{{ route('booking', ['service' => $service->slug]) }}">Book This Service</a>
                    <a class="btn btn-outline" href="{{ route('salon.index') }}">All services</a>
                </div>
            </div>
        </div>
    </section>

    {{-- DETAILS --}}
    <section class="section service-details">
        <div class="container details-grid">
            <div class="details-main">
                @if($service->description)
                    <article class="detail-block">
                        <h2>About this service</h2>
                        <p>{{ $service->description }}</p>
                    </article>
                @endif

                <div class="two-lists">
                    @if(!empty($service->inclusions))
                        <article class="detail-block">
                            <h3>What's included</h3>
                            <ul class="check-list">@foreach($service->inclusions as $item)<li>{{ $item }}</li>@endforeach</ul>
                        </article>
                    @endif
                    @if(!empty($service->benefits))
                        <article class="detail-block">
                            <h3>Benefits</h3>
                            <ul class="bloom-list">@foreach($service->benefits as $item)<li>{{ $item }}</li>@endforeach</ul>
                        </article>
                    @endif
                </div>

                @if(!empty($service->faqs))
                    <article class="detail-block">
                        <h2>Frequently asked questions</h2>
                        <div class="faq-list">
                            @foreach($service->faqs as $faq)
                                <details class="faq">
                                    <summary>{{ $faq['q'] }}</summary>
                                    <p>{{ $faq['a'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </article>
                @endif
            </div>

            <aside class="details-aside">
                @if($service->ideal_for)
                    <div class="aside-card">
                        <h3>Recommended for</h3>
                        <p>{{ $service->ideal_for }}</p>
                    </div>
                @endif
                @if($service->preparation)
                    <div class="aside-card">
                        <h3>Before your visit</h3>
                        <p>{{ $service->preparation }}</p>
                    </div>
                @endif
                @if($service->aftercare)
                    <div class="aside-card">
                        <h3>Aftercare</h3>
                        <p>{{ $service->aftercare }}</p>
                    </div>
                @endif
                <div class="aside-card aside-book">
                    <h3>Ready to book?</h3>
                    <p>{{ $service->name }} · {{ $service->price_label }} · {{ $service->duration_label }}</p>
                    <a class="btn btn-primary" href="{{ route('booking', ['service' => $service->slug]) }}">Book This Service</a>
                </div>
            </aside>
        </div>
    </section>

    {{-- RELATED --}}
    @if($related->isNotEmpty())
        <section class="section section-soft">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">You may also like</span>
                    <h2>Related services</h2>
                </div>
                <div class="service-grid">
                    @foreach($related as $item)
                        @include('partials.service-card', ['service' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('js/service.js') }}" defer></script>
@endpush
