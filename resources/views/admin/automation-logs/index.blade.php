@extends('layouts.admin')
@section('title', 'Automation Logs')

@section('content')
    <div class="grid-2" style="margin-bottom:22px">
        <section class="a-card">
            <div class="a-card-head"><div><h2>What the statuses mean</h2></div></div>
            <ul class="config-list">
                <li><span><span class="badge badge-sent_to_n8n">Sent to n8n</span></span><span class="muted">n8n accepted the event. This does <strong>not</strong> prove an email was delivered.</span></li>
                <li><span><span class="badge badge-email_sent">Email sent</span></span><span class="muted">n8n reported back that the email step succeeded.</span></li>
                <li><span><span class="badge badge-failed">Failed</span></span><span class="muted">n8n was unreachable or returned an error. The booking itself is still saved.</span></li>
                <li><span><span class="badge badge-skipped">Skipped</span></span><span class="muted">The n8n URL is not configured in .env yet.</span></li>
            </ul>
        </section>
        <section class="a-card">
            <div class="a-card-head"><div><h2>Integration configuration</h2><p>Read from the server .env (values are never shown).</p></div></div>
            <ul class="config-list">
                @foreach($configured as $label => $ok)
                    <li><span>{{ $label }}</span><span class="badge badge-{{ $ok ? 'success' : 'pending' }}">{{ $ok ? 'Configured' : 'Not configured' }}</span></li>
                @endforeach
            </ul>
        </section>
    </div>

    <section class="a-card">
        <form class="filters" method="GET" action="{{ route('admin.automation-logs') }}">
            <label class="admin-field"><span>Workflow</span>
                <select name="workflow" data-auto-submit>
                    <option value="">All workflows</option>
                    @foreach(['assistant' => 'AI assistant (chat)', 'appointment_email' => 'Appointment emails', 'reminder' => 'Reminders'] as $k => $v)<option value="{{ $k }}" @selected(request('workflow') === $k)>{{ $v }}</option>@endforeach
                </select>
            </label>
            <label class="admin-field"><span>Status</span>
                <select name="status" data-auto-submit><option value="">All statuses</option>@foreach(\App\Models\AutomationLog::STATUS_LABELS as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }} ({{ $summary[$k] ?? 0 }})</option>@endforeach</select>
            </label>
            <label class="admin-field grow"><span>Appointment reference</span><input type="search" name="q" value="{{ request('q') }}" placeholder="BB-…"></label>
            <button class="a-btn a-btn-primary" type="submit">Filter</button>
            @if(request()->hasAny(['workflow', 'status', 'q']))<a class="a-btn" href="{{ route('admin.automation-logs') }}">Clear</a>@endif
        </form>
        <div class="table-wrap">
            <table class="a-table">
                <thead><tr><th>Date &amp; time</th><th>Workflow</th><th>Event</th><th>Appointment</th><th>Status</th><th>Details</th></tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="muted" style="white-space:nowrap">{{ $log->created_at->format('M j, Y g:i:s A') }}</td>
                        <td>{{ ['assistant' => 'AI assistant', 'appointment_email' => 'Appointment email', 'reminder' => 'Reminder'][$log->workflow] ?? $log->workflow }}</td>
                        <td class="mono">{{ $log->event }}</td>
                        <td>@if($log->appointment_id)<a class="mono" href="{{ route('admin.appointments.show', $log->appointment_id) }}">{{ $log->appointment_reference }}</a>@else<span class="muted">{{ $log->appointment_reference ?? '—' }}</span>@endif</td>
                        <td><span class="badge badge-{{ $log->status }}">{{ $log->status_label }}</span>@if($log->http_status)<br><span class="muted">HTTP {{ $log->http_status }}</span>@endif</td>
                        <td class="muted" style="max-width:360px">{{ $log->message }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty"><strong>No automation activity yet</strong>Chat messages, bookings, and status changes will create log entries.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.admin-pagination', ['paginator' => $logs])
    </section>
@endsection
