@extends('layouts.admin')
@section('title', 'Settings')

@section('content')
    @php $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']; @endphp
    @php $openDays = array_map('intval', explode(',', $settings['open_days'])); @endphp
    <div class="grid-2">
        <section class="a-card">
            <div class="a-card-head"><div><h2>Business details &amp; hours</h2><p>Shown on the website and used by the booking system and AI assistant.</p></div></div>
            <form method="POST" action="{{ route('admin.settings.business') }}">
                @csrf @method('PUT')
                <div class="form-grid">
                    <label class="admin-field"><span>Business name</span><input name="business_name" value="{{ old('business_name', $settings['business_name']) }}" required maxlength="120"></label>
                    <label class="admin-field"><span>Tagline</span><input name="business_tagline" value="{{ old('business_tagline', $settings['business_tagline']) }}" maxlength="160"></label>
                    <label class="admin-field"><span>Public email</span><input type="email" name="business_email" value="{{ old('business_email', $settings['business_email']) }}" placeholder=""></label>
                    <label class="admin-field"><span>Public phone</span><input name="business_phone" value="{{ old('business_phone', $settings['business_phone']) }}" placeholder="" maxlength="40"></label>
                    <label class="admin-field full"><span>Address</span><input name="business_address" value="{{ old('business_address', $settings['business_address']) }}" placeholder="" maxlength="500"></label>
                    <label class="admin-field"><span>Opening time</span><input type="time" name="opening_time" value="{{ old('opening_time', $settings['opening_time']) }}" required></label>
                    <label class="admin-field"><span>Closing time</span><input type="time" name="closing_time" value="{{ old('closing_time', $settings['closing_time']) }}" required></label>
                    <div class="admin-field full"><span>Open days</span>
                        <div class="check-row">@foreach($days as $i => $d)<label class="check-pill"><input type="checkbox" name="open_days[]" value="{{ $i }}" @checked(in_array($i, old('open_days', $openDays)))> {{ $d }}</label>@endforeach</div>
                    </div>
                    <label class="admin-field"><span>Booking slot interval</span><select name="slot_interval">@foreach(['15', '30', '60'] as $m)<option value="{{ $m }}" @selected($settings['slot_interval'] == $m)>Every {{ $m }} minutes</option>@endforeach</select></label>
                    <label class="admin-field"><span>Send reminders (hours before)</span><input type="number" name="reminder_hours_before" min="1" max="168" value="{{ old('reminder_hours_before', $settings['reminder_hours_before']) }}" required></label>
                    <div class="admin-field full"><label class="check-pill" style="border-radius:12px"><input type="checkbox" name="hours_are_sample" value="1" @checked($settings['hours_are_sample'] === '1')> These are sample hours (shows a “sample hours” note on the website)</label></div>
                </div>
                <button class="a-btn a-btn-primary" type="submit">Save business settings</button>
            </form>
        </section>

        <div class="stack">
            <section class="a-card">
                <div class="a-card-head"><div><h2>Appearance</h2><p>Choose how the Admin Portal looks for you.</p></div></div>
                <div class="theme-segment theme-segment-lg" role="radiogroup" aria-label="Theme" data-theme-url="{{ route('admin.settings.theme') }}">
                    <button type="button" role="radio" data-theme-choice="light" aria-checked="{{ $admin->theme === 'dark' ? 'false' : 'true' }}">@include('partials.admin-icon', ['name' => 'sun', 'size' => 16]) Light</button>
                    <button type="button" role="radio" data-theme-choice="dark" aria-checked="{{ $admin->theme === 'dark' ? 'true' : 'false' }}">@include('partials.admin-icon', ['name' => 'moon', 'size' => 16]) Dark</button>
                </div>
                <noscript>
                    <form method="POST" action="{{ route('admin.settings.theme') }}" style="margin-top:12px">@csrf @method('PATCH')
                        <button class="a-btn" name="theme" value="light">Light</button> <button class="a-btn" name="theme" value="dark">Dark</button>
                    </form>
                </noscript>
            </section>
            <section class="a-card">
                <div class="a-card-head"><div><h2>Your account</h2><p>Name, email and password are on your profile page.</p></div></div>
                <div class="settings-account">
                    <span class="admin-avatar admin-avatar-lg" aria-hidden="true">{{ strtoupper(mb_substr($admin->name, 0, 1)) }}</span>
                    <span><strong>{{ $admin->name }}</strong><small class="muted">{{ $admin->email }}</small></span>
                    <a class="a-btn" href="{{ route('admin.profile') }}">Open profile</a>
                </div>
            </section>
        </div>
    </div>
@endsection
