{{-- Reusable service card. Styles: public/css/service-card.css --}}
<article class="service-card" data-category="{{ $service->category }}">
    <a class="service-card-media" href="{{ route('salon.show', $service) }}" tabindex="-1" aria-hidden="true">
        <img src="{{ $service->image_url }}" alt="" loading="lazy" width="600" height="420">
        <span class="service-card-tag">{{ $service->category_label }}</span>
    </a>
    <div class="service-card-body">
        <h3><a href="{{ route('salon.show', $service) }}">{{ $service->name }}</a></h3>
        <p>{{ $service->short_description }}</p>
        <dl class="service-card-meta">
            <div><dt>Starting at</dt><dd>{{ $service->price_label }}</dd></div>
            <div><dt>Duration</dt><dd>{{ $service->duration_label }}</dd></div>
        </dl>
        <div class="service-card-actions">
            <a class="service-card-link" href="{{ route('salon.show', $service) }}">Explore Details <span aria-hidden="true">→</span><span class="sr-only"> about {{ $service->name }}</span></a>
            <a class="btn btn-primary btn-sm" href="{{ route('booking', ['service' => $service->slug]) }}">Book This Service<span class="sr-only">: {{ $service->name }}</span></a>
        </div>
    </div>
</article>
