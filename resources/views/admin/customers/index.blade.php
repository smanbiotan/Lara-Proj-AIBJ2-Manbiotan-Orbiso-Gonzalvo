@extends('layouts.admin')
@section('title', 'Customers')
@section('actions')
    <a class="a-btn a-btn-primary" href="{{ route('admin.customers.create') }}">+ Add customer</a>
@endsection

@section('content')
    <section class="a-card">
        <form class="filters" method="GET" action="{{ route('admin.customers.index') }}" role="search">
            <label class="admin-field grow"><span>Search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Name, email, or phone"></label>
            <label class="admin-field"><span>Status</span>
                <select name="status" data-auto-submit><option value="">All statuses</option>@foreach(\App\Models\Customer::STATUSES as $s)<option @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select>
            </label>
            <label class="admin-field"><span>Account</span>
                <select name="account" data-auto-submit><option value="">All</option><option value="registered" @selected(request('account') === 'registered')>Registered</option><option value="guest" @selected(request('account') === 'guest')>Guest</option></select>
            </label>
            <button class="a-btn a-btn-primary" type="submit">Search</button>
            @if(request()->hasAny(['q', 'status', 'account']))<a class="a-btn" href="{{ route('admin.customers.index') }}">Clear</a>@endif
        </form>

        <div class="table-wrap">
            <table class="a-table">
                <thead><tr><th>Customer</th><th>Phone</th><th>Account</th><th>Appointments</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td><div class="person"><span class="person-avatar">{{ strtoupper(substr($c->name, 0, 1)) }}</span><span><strong><a href="{{ route('admin.customers.show', $c) }}">{{ $c->name }}</a></strong><small>{{ $c->email }}</small></span></div></td>
                        <td>{{ $c->phone ?: '—' }}</td>
                        <td><span class="muted">{{ $c->hasAccount() ? 'Registered' : 'Guest' }}</span></td>
                        <td>{{ $c->appointments_count }}</td>
                        <td><span class="badge badge-{{ strtolower($c->status) }}">{{ $c->status }}</span></td>
                        <td class="muted">{{ $c->created_at?->format('M j, Y') }}</td>
                        <td>
                            <div class="actions-cell">
                                <a class="a-btn a-btn-sm" href="{{ route('admin.customers.show', $c) }}">View</a>
                                <a class="a-btn a-btn-sm" href="{{ route('admin.customers.edit', $c) }}">Edit</a>
                                <form class="inline-form" method="POST" action="{{ route('admin.customers.destroy', $c) }}" data-confirm="Delete {{ $c->name }}? This also deletes their {{ $c->appointments_count }} appointment(s). This cannot be undone.">@csrf @method('DELETE')<button class="a-btn a-btn-danger a-btn-sm" type="submit">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty"><strong>No customers found</strong>Customers are added automatically when they book or register.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.admin-pagination', ['paginator' => $customers])
    </section>
@endsection
