@extends('layouts.admin')
@section('title', $service->exists ? 'Edit '.$service->name : 'Add service')
@section('actions')<a class="a-btn" href="{{ route('admin.services.index') }}">← Services</a>@endsection

@section('content')
    <form method="POST" action="{{ $service->exists ? route('admin.services.update', $service->id) : route('admin.services.store') }}" enctype="multipart/form-data">
        @csrf
        @if($service->exists) @method('PUT') @endif
        <div class="grid-2">
            <div class="stack">
                <section class="a-card">
                    <div class="a-card-head"><div><h2>Basic information</h2></div></div>
                    <div class="form-grid">
                        <label class="admin-field"><span>Service name</span><input name="name" value="{{ old('name', $service->name) }}" required maxlength="120"></label>
                        <label class="admin-field"><span>URL slug <small>(optional — generated from the name)</small></span><input name="slug" value="{{ old('slug', $service->slug) }}" maxlength="120" pattern="[A-Za-z0-9_\-]+"></label>
                        <label class="admin-field"><span>Category</span><select name="category">@foreach(\App\Support\SalonDefaults::CATEGORIES as $key => $label)<option value="{{ $key }}" @selected(old('category', $service->category) === $key)>{{ $label }}</option>@endforeach</select></label>
                        <label class="admin-field"><span>Starting price (₱)</span><input type="number" name="price" min="0" step="0.01" value="{{ old('price', $service->price) }}" required></label>
                        <label class="admin-field"><span>Duration (minutes)</span><input type="number" name="duration_minutes" min="15" max="480" step="5" value="{{ old('duration_minutes', $service->duration_minutes) }}" required><small>Used for availability: bookings in the same category cannot overlap.</small></label>
                        <label class="admin-field" style="align-self:center"><span>Visibility</span><span class="check-pill"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))> Available for booking</span></label>
                        <label class="admin-field full"><span>Short description <small>(shown on service cards)</small></span><textarea name="short_description" rows="2" maxlength="500" required>{{ old('short_description', $service->short_description) }}</textarea></label>
                        <label class="admin-field full"><span>Introduction</span><textarea name="intro" rows="3" maxlength="2000">{{ old('intro', $service->intro) }}</textarea></label>
                        <label class="admin-field full"><span>Full description</span><textarea name="description" rows="4" maxlength="5000">{{ old('description', $service->description) }}</textarea></label>
                    </div>
                </section>
                <section class="a-card">
                    <div class="a-card-head"><div><h2>Service page details</h2><p>One item per line.</p></div></div>
                    <div class="form-grid">
                        <label class="admin-field"><span>What's included</span><textarea name="inclusions" rows="5">{{ old('inclusions', implode("\n", $service->inclusions ?? [])) }}</textarea></label>
                        <label class="admin-field"><span>Benefits</span><textarea name="benefits" rows="5">{{ old('benefits', implode("\n", $service->benefits ?? [])) }}</textarea></label>
                        <label class="admin-field full"><span>Recommended for</span><textarea name="ideal_for" rows="2" maxlength="1000">{{ old('ideal_for', $service->ideal_for) }}</textarea></label>
                        <label class="admin-field"><span>Preparation</span><textarea name="preparation" rows="3" maxlength="2000">{{ old('preparation', $service->preparation) }}</textarea></label>
                        <label class="admin-field"><span>Aftercare</span><textarea name="aftercare" rows="3" maxlength="2000">{{ old('aftercare', $service->aftercare) }}</textarea></label>
                        <label class="admin-field full"><span>FAQs <small>(one per line: Question? | Answer)</small></span><textarea name="faqs" rows="4">{{ old('faqs', collect($service->faqs ?? [])->map(fn ($f) => $f['q'].' | '.$f['a'])->implode("\n")) }}</textarea></label>
                    </div>
                </section>
            </div>
            <div class="stack">
                <section class="a-card">
                    <div class="a-card-head"><div><h2>Main photo</h2><p>JPG, PNG, or WebP · max 3 MB · at least 300×200 px.</p></div></div>
                    @if($service->exists)<img src="{{ $service->image_url }}" alt="Current photo" style="width:100%;border-radius:12px;margin-bottom:12px;aspect-ratio:10/7;object-fit:cover">@endif
                    <label class="admin-field"><span>Upload new photo</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
                </section>
                @if($service->exists)
                    <section class="a-card">
                        <div class="a-card-head"><div><h2>Gallery photos</h2><p>Shown in the service page slideshow.</p></div></div>
                        <div class="gallery-edit">
                            @forelse($service->gallery ?? [] as $index => $photo)
                                <label><img src="{{ \App\Support\SalonImages::url($photo['image'], 300) }}" alt=""><span><input type="checkbox" name="remove_gallery[]" value="{{ $index }}"> Remove</span></label>
                            @empty
                                <p class="muted">No gallery photos yet.</p>
                            @endforelse
                        </div>
                        <label class="admin-field" style="margin-top:14px"><span>Add gallery photos</span><input type="file" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple></label>
                    </section>
                @endif
                <button class="a-btn a-btn-primary a-btn-block" type="submit">{{ $service->exists ? 'Save service' : 'Create service' }}</button>
            </div>
        </div>
    </form>
@endsection
