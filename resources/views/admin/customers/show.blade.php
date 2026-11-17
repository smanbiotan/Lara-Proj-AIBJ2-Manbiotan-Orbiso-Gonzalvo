@extends('layouts.admin')
@section('title', $customer->name)
@section('actions')
    <a class="a-btn" href="{{ route('admin.customers.index') }}">← Customers</a>
    <a class="a-btn a-btn-primary" href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
@endsection

@section('content')
    <div class="grid-2">
        <section class="a-card">
            <div class="a-card-head"><div><h2>Appointment history</h2><p>{{ $appointments->count() }} appointment(s)</p></div></div>
            <div class="table-wrap">
                <table class="a-table">
                    <thead><tr><th>Reference</th><th>Service</th><th>Date &amp; time</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($appointments as $a)
                        <tr>
                            <td><a class="mono" href="{{ route('admin.appointments.show', $a) }}">{{ $a->reference }}</a></td>
                            <td>{{ $a->service_name }}</td>
                            <td>{{ $a->appointment_date->format('M j, Y') }} · {{ $a->time_label }}</td>
                            <td><span class="badge badge-{{ strtolower($a->status) }}">{{ $a->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty"><strong>No appointments yet</strong></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="a-card">
            <div class="a-card-head"><div><h2>Customer details</h2></div><span class="badge badge-{{ strtolower($customer->status) }}">{{ $customer->status }}</span></div>
            <dl class="detail-list">
                <dt>Email</dt><dd><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></dd>
                <dt>Phone</dt><dd>{{ $customer->phone ?: '—' }}</dd>
                <dt>Account</dt><dd>{{ $customer->google_id ? 'Registered (Google)' : ($customer->hasAccount() ? 'Registered' : 'Guest (no password)') }}</dd>
                <dt>Last sign in</dt><dd>{{ $customer->last_login_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                <dt>Customer since</dt><dd>{{ $customer->created_at?->format('M j, Y') }}</dd>
            </dl>
            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" data-confirm="Delete {{ $customer->name }} and all their appointments? This cannot be undone." style="margin-top:20px">
                @csrf @method('DELETE')<button class="a-btn a-btn-danger" type="submit">Delete customer</button>
            </form>
        </section>
    </div>
@endsection
