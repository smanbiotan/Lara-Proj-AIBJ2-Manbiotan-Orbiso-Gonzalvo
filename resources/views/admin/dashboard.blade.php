@extends('layouts.admin')
@section('title', 'Dashboard')
@section('actions')
    <a class="a-btn" href="{{ route('admin.appointments.index', ['status' => 'Pending']) }}">Review pending</a>
    <a class="a-btn a-btn-primary" href="{{ route('admin.services.create') }}">+ Add service</a>
@endsection

@section('content')
    <p class="muted" style="margin-top:0">Welcome back, {{ auth('admin')->user()->name }}. Today is {{ now()->format('l, F j, Y') }}. All figures below come from the database.</p>

    <div class="stats">
        <a class="stat stat-sage" href="{{ route('admin.customers.index') }}"><small>Total customers</small><strong>{{ $stats['customers'] }}</strong></a>
        <a class="stat stat-taupe" href="{{ route('admin.appointments.index') }}"><small>Total appointments</small><strong>{{ $stats['appointments'] }}</strong></a>
        <a class="stat stat-gold" href="{{ route('admin.appointments.index', ['status' => 'Pending']) }}"><small>Pending</small><strong>{{ $stats['pending'] }}</strong></a>
        <a class="stat stat-sage" href="{{ route('admin.appointments.index', ['status' => 'Confirmed']) }}"><small>Confirmed</small><strong>{{ $stats['confirmed'] }}</strong></a>
        <a class="stat stat-rose" href="{{ route('admin.inquiries.index') }}"><small>Open inquiries</small><strong>{{ $stats['openInquiries'] }}</strong></a>
        <a class="stat stat-taupe" href="{{ route('admin.appointments.index', ['date' => today()->toDateString()]) }}"><small>Today's bookings</small><strong>{{ $stats['today'] }}</strong></a>
    </div>

    <div class="grid-2">
        <div class="stack">
            <section class="a-card">
                <div class="a-card-head"><div><h2>Weekly bookings</h2><p>Appointments per week (by appointment date), last 8 weeks.</p></div></div>
                @if($weekly->sum('count') === 0)
                    <div class="empty"><strong>No booking data yet</strong>New appointments will appear in this chart.</div>
                @else
                    <div class="chart-box"><canvas id="weeklyChart" data-chart='@json($weekly)' role="img" aria-label="Bar chart of weekly appointments"></canvas></div>
                @endif
            </section>

            <section class="a-card">
                <div class="a-card-head"><div><h2>Recent appointment activity</h2><p>Latest booking requests received.</p></div><a class="a-btn a-btn-sm" href="{{ route('admin.appointments.index') }}">View all</a></div>
                <div class="table-wrap">
                    <table class="a-table">
                        <thead><tr><th>Reference</th><th>Customer</th><th>Service</th><th>When</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($recent as $a)
                            <tr>
                                <td><a class="mono" href="{{ route('admin.appointments.show', $a) }}">{{ $a->reference }}</a></td>
                                <td>{{ $a->customer?->name ?? '—' }}</td>
                                <td>{{ $a->service_name }}</td>
                                <td>{{ $a->appointment_date->format('M j') }} · {{ $a->time_label }}</td>
                                <td><span class="badge badge-{{ strtolower($a->status) }}">{{ $a->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty"><strong>No appointments yet</strong>Bookings from the website will show here.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="stack">
            <section class="a-card">
                <div class="a-card-head"><div><h2>Popular services</h2><p>All-time bookings per service.</p></div></div>
                @if($popular->sum('appointments_count') === 0)
                    <div class="empty"><strong>No data yet</strong></div>
                @else
                    <div class="chart-box" style="height:230px"><canvas id="popularChart" data-chart='@json($popular->map(fn ($s) => ['name' => $s->name, 'count' => $s->appointments_count]))' role="img" aria-label="Bar chart of bookings per service"></canvas></div>
                @endif
            </section>

            <section class="a-card">
                <div class="a-card-head"><div><h2>Upcoming</h2><p>Next pending or confirmed visits.</p></div></div>
                <ul class="list-compact">
                    @forelse($upcoming as $a)
                        <li><span><strong>{{ $a->customer?->name }}</strong><br><span class="muted">{{ $a->service_name }} · {{ $a->appointment_date->format('D, M j') }} {{ $a->time_label }}</span></span><span class="badge badge-{{ strtolower($a->status) }}">{{ $a->status }}</span></li>
                    @empty
                        <li class="muted">No upcoming appointments.</li>
                    @endforelse
                </ul>
            </section>

            <section class="a-card">
                <div class="a-card-head"><div><h2>Quick actions</h2></div></div>
                <div class="status-actions">
                    <a class="a-btn" href="{{ route('admin.customers.create') }}">+ New customer</a>
                    <a class="a-btn" href="{{ route('admin.inquiries.index', ['status' => 'New']) }}">New inquiries</a>
                    <a class="a-btn" href="{{ route('admin.automation-logs') }}">Automation logs</a>
                    <a class="a-btn" href="{{ route('admin.settings') }}">Business hours</a>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" defer></script>
@endpush
