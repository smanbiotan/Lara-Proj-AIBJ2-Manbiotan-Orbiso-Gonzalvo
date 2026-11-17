{{-- Administrator profile — same layout as the customer "My Account" page on the website --}}
@extends('layouts.admin')
@section('title', 'My Profile')

@php
    $words = preg_split('/\s+/', trim($admin->name));
    $initials = strtoupper(mb_substr($words[0], 0, 1).(count($words) > 1 ? mb_substr(end($words), 0, 1) : ''));
@endphp

@section('content')
    <section class="account-hero">
        <span class="account-eyebrow">My account</span>
        <h2>Hello, <em>{{ $admin->name }}</em></h2>
        <p>Here is your Blush &amp; Bloom administrator account.</p>
    </section>

    <div class="account-layout">
        <aside class="account-panel">
            <div class="account-profile-row">
                <span class="account-avatar-lg" aria-hidden="true">{{ $initials }}</span>
                <div><strong>{{ $admin->name }}</strong><small>Blush &amp; Bloom administrator</small></div>
            </div>
            <h3>Your details</h3>
            <dl class="account-details">
                <div><dt>Name</dt><dd>{{ $admin->name }}</dd></div>
                <div><dt>Email</dt><dd>{{ $admin->email }}</dd></div>
                <div><dt>Role</dt><dd>Administrator</dd></div>
                <div><dt>Member since</dt><dd>{{ $admin->created_at?->format('F Y') ?? '—' }}</dd></div>
                <div><dt>Last sign-in</dt><dd>{{ $admin->last_login_at?->format('M j, Y · g:i A') ?? 'This session' }}</dd></div>
            </dl>
            <a class="a-btn a-btn-primary a-btn-block account-btn" href="{{ route('admin.settings') }}">Business settings</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="a-btn a-btn-outline a-btn-block account-btn" type="submit">Sign out</button>
            </form>
        </aside>

        <div class="account-main">
            <section class="account-panel">
                <h3>Upcoming appointments</h3>
                @forelse($upcoming as $appointment)
                    <a class="account-appt" href="{{ route('admin.appointments.show', $appointment) }}">
                        <div>
                            <strong>{{ $appointment->service_name }} · {{ $appointment->customer?->name }}</strong>
                            <span>{{ $appointment->appointment_date->format('D, M j, Y') }} · {{ $appointment->time_label }}</span>
                            <small>Ref: {{ $appointment->reference }}</small>
                        </div>
                        <span class="badge badge-{{ strtolower($appointment->status) }}">{{ $appointment->status }}</span>
                    </a>
                @empty
                    <div class="account-empty-box">
                        <p>No upcoming appointments yet.</p>
                        <a class="a-btn a-btn-outline" href="{{ route('admin.appointments.index') }}">View all appointments</a>
                    </div>
                @endforelse
                <p class="account-note">To confirm, reschedule or cancel a booking, open it from <a href="{{ route('admin.appointments.index') }}">Appointments</a>.</p>
            </section>

            <section class="account-panel">
                <h3>Edit profile</h3>
                <form method="POST" action="{{ route('admin.settings.profile') }}">
                    @csrf @method('PUT')
                    <div class="form-grid">
                        <label class="admin-field"><span>Name</span><input name="name" value="{{ old('name', $admin->name) }}" required maxlength="120"></label>
                        <label class="admin-field"><span>Email</span><input type="email" name="email" value="{{ old('email', $admin->email) }}" required></label>
                    </div>
                    <button class="a-btn a-btn-primary" type="submit">Save profile</button>
                </form>
            </section>

            <section class="account-panel">
                <h3>Change password</h3>
                <p class="account-note" style="margin-top:-6px">At least 10 characters with letters and numbers.</p>
                <form method="POST" action="{{ route('admin.settings.password') }}">
                    @csrf @method('PUT')
                    <label class="admin-field"><span>Current password</span><input type="password" name="current_password" autocomplete="current-password" required></label>
                    <div class="form-grid">
                        <label class="admin-field"><span>New password</span><input type="password" name="password" autocomplete="new-password" required minlength="10"></label>
                        <label class="admin-field"><span>Confirm new password</span><input type="password" name="password_confirmation" autocomplete="new-password" required></label>
                    </div>
                    <button class="a-btn a-btn-primary" type="submit">Change password</button>
                </form>
            </section>

            <section class="account-panel" id="help">
                <h3>Help</h3>
                <ul class="help-list">
                    <li><strong>New booking?</strong> It arrives as <em>Pending</em> in Appointments. Confirm it and the customer gets an email through n8n.</li>
                    <li><strong>Change prices or photos</strong> in Services. Changes show on the website right away.</li>
                    <li><strong>Hours, contact details and open days</strong> are in Business settings.</li>
                    <li><strong>Emails or the AI assistant not working?</strong> Check Automation Logs to see what n8n reported.</li>
                    <li><strong>Light or dark?</strong> Click your name at the bottom of the sidebar and choose Light or Dark.</li>
                </ul>
            </section>
        </div>
    </div>
@endsection
