@extends('layouts.admin')
@section('title', 'Appointment '.$appointment->reference)
@section('actions')
    <a class="a-btn" href="{{ route('admin.appointments.index') }}">← All appointments</a>
@endsection

@section('content')
    <div class="grid-2">
        <div class="stack">
            <section class="a-card">
                <div class="a-card-head">
                    <div><h2>{{ $appointment->service_name }}</h2><p class="mono">{{ $appointment->reference }}</p></div>
                    <span class="badge badge-{{ strtolower($appointment->status) }}" style="font-size:.9rem">{{ $appointment->status }}</span>
                </div>
                <dl class="detail-list">
                    <dt>Date</dt><dd>{{ $appointment->appointment_date->format('l, F j, Y') }}</dd>
                    <dt>Time</dt><dd>{{ $appointment->time_label }} – {{ $appointment->ends_at->format('g:i A') }} ({{ $appointment->duration_minutes }} min)</dd>
                    <dt>Service price</dt><dd>{{ $appointment->serviceRecord?->price_label ?? '—' }} (starting)</dd>
                    <dt>Booked via</dt><dd>{{ ucfirst($appointment->source) }} on {{ $appointment->created_at?->format('M j, Y g:i A') }}</dd>
                    <dt>Customer notes</dt><dd>{{ $appointment->notes ?: '—' }}</dd>
                    <dt>Last status change</dt><dd>{{ $appointment->status_changed_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                    <dt>Reminder</dt><dd>{{ $appointment->reminder_sent_at ? 'Sent '.$appointment->reminder_sent_at->format('M j, g:i A') : ($appointment->reminder_status ? ucfirst($appointment->reminder_status) : 'Not sent') }}</dd>
                </dl>
            </section>

            <section class="a-card">
                <div class="a-card-head"><div><h2>Update status</h2><p>Confirming, rejecting, or cancelling sends an event to n8n so the customer can be emailed (when configured).</p></div></div>
                <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}">
                    @csrf @method('PATCH')
                    <div class="form-grid">
                        <label class="admin-field"><span>Status</span>
                            <select name="status">@foreach(\App\Models\Appointment::STATUSES as $s)<option value="{{ $s }}" @selected($appointment->status === $s)>{{ $s }}</option>@endforeach</select>
                        </label>
                        <label class="admin-field full"><span>Internal admin notes <small>(not shown to the customer)</small></span><textarea name="admin_notes" rows="2" maxlength="2000">{{ old('admin_notes', $appointment->admin_notes) }}</textarea></label>
                    </div>
                    <button class="a-btn a-btn-primary" type="submit">Save status</button>
                </form>
                <hr style="border:0;border-top:1px solid var(--border);margin:20px 0">
                <div class="status-actions">
                    @foreach(['Confirmed' => 'a-btn-sage', 'Completed' => '', 'Cancelled' => 'a-btn-danger', 'Rejected' => 'a-btn-danger'] as $s => $cls)
                        @if($appointment->status !== $s)
                            <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}" @if(in_array($s, ['Cancelled', 'Rejected'])) data-confirm="Mark this appointment as {{ $s }}?" @endif>
                                @csrf @method('PATCH')<input type="hidden" name="status" value="{{ $s }}">
                                <button class="a-btn {{ $cls }}" type="submit">{{ ['Confirmed' => 'Confirm', 'Completed' => 'Mark completed', 'Cancelled' => 'Cancel', 'Rejected' => 'Reject'][$s] }}</button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </section>

            @if(in_array($appointment->status, \App\Models\Appointment::ACTIVE_STATUSES, true) && $appointment->serviceRecord)
                <section class="a-card">
                    <div class="a-card-head"><div><h2>Reschedule</h2><p>Laravel checks the new time against existing bookings for the same station.</p></div></div>
                    <form method="POST" action="{{ route('admin.appointments.reschedule', $appointment) }}" id="rescheduleForm" data-availability-url="{{ url('/api/appointments/availability') }}" data-service="{{ $appointment->serviceRecord->slug }}">
                        @csrf @method('PATCH')
                        <div class="form-grid">
                            <label class="admin-field"><span>New date</span><input type="date" name="appointment_date" min="{{ now()->toDateString() }}" value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" required></label>
                            <label class="admin-field"><span>New time</span><input type="time" name="appointment_time" step="900" value="{{ old('appointment_time', $appointment->appointment_time) }}" required></label>
                        </div>
                        <p class="muted" id="rescheduleHint">Tip: the availability check ignores this appointment's current slot.</p>
                        <button class="a-btn a-btn-primary" type="submit">Reschedule</button>
                    </form>
                </section>
            @endif
        </div>

        <div class="stack">
            <section class="a-card">
                <div class="a-card-head"><div><h2>Customer</h2></div>@if($appointment->customer)<a class="a-btn a-btn-sm" href="{{ route('admin.customers.show', $appointment->customer) }}">Open profile</a>@endif</div>
                @if($appointment->customer)
                    <dl class="detail-list">
                        <dt>Name</dt><dd>{{ $appointment->customer->name }}</dd>
                        <dt>Email</dt><dd><a href="mailto:{{ $appointment->customer->email }}">{{ $appointment->customer->email }}</a></dd>
                        <dt>Phone</dt><dd>{{ $appointment->customer->phone ?: '—' }}</dd>
                        <dt>Account</dt><dd>{{ $appointment->customer->hasAccount() ? 'Registered' : 'Guest' }}</dd>
                    </dl>
                @else
                    <p class="muted">Customer record not found.</p>
                @endif
            </section>

            <section class="a-card">
                <div class="a-card-head"><div><h2>Automation history</h2><p>Events sent to n8n for this appointment.</p></div></div>
                <ul class="list-compact">
                    @forelse($logs as $log)
                        <li><span><strong>{{ $log->event }}</strong><br><span class="muted">{{ $log->created_at->format('M j, g:i A') }} · {{ $log->message }}</span></span><span class="badge badge-{{ $log->status }}">{{ $log->status_label }}</span></li>
                    @empty
                        <li class="muted">No automation events yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
@endsection
