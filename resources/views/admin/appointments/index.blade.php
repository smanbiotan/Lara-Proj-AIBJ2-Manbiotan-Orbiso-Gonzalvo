@extends('layouts.admin')
@section('title', 'Appointments')

@section('content')
    <div class="status-tabs" aria-label="Filter by status">
        <a href="{{ route('admin.appointments.index', request()->except('status', 'page')) }}" class="{{ !request('status') ? 'is-active' : '' }}">All ({{ $counts->sum() }})</a>
        @foreach(\App\Models\Appointment::STATUSES as $status)
            <a href="{{ route('admin.appointments.index', array_merge(request()->except('page'), ['status' => $status])) }}" class="{{ request('status') === $status ? 'is-active' : '' }}">{{ $status }} ({{ $counts[$status] ?? 0 }})</a>
        @endforeach
    </div>

    <section class="a-card">
        <form class="filters" method="GET" action="{{ route('admin.appointments.index') }}" role="search">
            @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            <label class="admin-field grow"><span>Search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Reference, customer name, email, or service"></label>
            <label class="admin-field"><span>Date</span><input type="date" name="date" value="{{ request('date') }}"></label>
            <button class="a-btn a-btn-primary" type="submit">Filter</button>
            @if(request()->hasAny(['q', 'date', 'status']))<a class="a-btn" href="{{ route('admin.appointments.index') }}">Clear</a>@endif
        </form>

        <div class="table-wrap">
            <table class="a-table">
                <thead><tr><th>Reference</th><th>Customer</th><th>Service</th><th>Date &amp; time</th><th>Source</th><th>Status</th><th>Quick actions</th></tr></thead>
                <tbody>
                @forelse($appointments as $a)
                    <tr>
                        <td><a class="mono" href="{{ route('admin.appointments.show', $a) }}">{{ $a->reference }}</a></td>
                        <td><div class="person"><span class="person-avatar">{{ strtoupper(substr($a->customer?->name ?? '?', 0, 1)) }}</span><span><strong>{{ $a->customer?->name ?? 'Deleted customer' }}</strong><small>{{ $a->customer?->email }}</small></span></div></td>
                        <td>{{ $a->service_name }}<br><span class="muted">{{ $a->duration_minutes }} min</span></td>
                        <td>{{ $a->appointment_date->format('M j, Y') }}<br><span class="muted">{{ $a->time_label }}</span></td>
                        <td><span class="muted">{{ ucfirst($a->source) }}</span></td>
                        <td><span class="badge badge-{{ strtolower($a->status) }}">{{ $a->status }}</span></td>
                        <td>
                            <div class="actions-cell">
                                @if($a->status === 'Pending')
                                    <form class="inline-form" method="POST" action="{{ route('admin.appointments.status', $a) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="Confirmed"><button class="a-btn a-btn-sage a-btn-sm" type="submit">Confirm</button></form>
                                    <form class="inline-form" method="POST" action="{{ route('admin.appointments.status', $a) }}" data-confirm="Reject this booking request? The customer will be notified if email automation is configured.">@csrf @method('PATCH')<input type="hidden" name="status" value="Rejected"><button class="a-btn a-btn-danger a-btn-sm" type="submit">Reject</button></form>
                                @endif
                                <a class="a-btn a-btn-sm" href="{{ route('admin.appointments.show', $a) }}">Manage</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty"><strong>No appointments found</strong>Try a different search or filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.admin-pagination', ['paginator' => $appointments])
    </section>
@endsection
