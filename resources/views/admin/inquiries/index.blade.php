@extends('layouts.admin')
@section('title', 'Inquiries')

@section('content')
    <section class="a-card">
        <form class="filters" method="GET" action="{{ route('admin.inquiries.index') }}" role="search">
            <label class="admin-field grow"><span>Search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Name, email, subject, or message"></label>
            <label class="admin-field"><span>Status</span>
                <select name="status" data-auto-submit><option value="">All statuses</option>@foreach(\App\Models\Inquiry::STATUSES as $s)<option @selected(request('status') === $s)>{{ $s }}</option>@endforeach</select>
            </label>
            <button class="a-btn a-btn-primary" type="submit">Search</button>
            @if(request()->hasAny(['q', 'status']))<a class="a-btn" href="{{ route('admin.inquiries.index') }}">Clear</a>@endif
        </form>
        <div class="table-wrap">
            <table class="a-table">
                <thead><tr><th>From</th><th>Subject</th><th>Source</th><th>Received</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($inquiries as $i)
                    <tr>
                        <td><div class="person"><span class="person-avatar">{{ strtoupper(substr($i->name, 0, 1)) }}</span><span><strong>{{ $i->name }}</strong><small>{{ $i->email }}</small></span></div></td>
                        <td>{{ $i->subject }}<br><span class="muted">{{ \Illuminate\Support\Str::limit($i->message, 70) }}</span></td>
                        <td class="muted">{{ $i->source === 'chatbot' ? 'Chat escalation' : 'Contact form' }}</td>
                        <td class="muted">{{ $i->created_at?->format('M j, Y g:i A') }}</td>
                        <td><span class="badge badge-{{ strtolower($i->status) }}">{{ $i->status }}</span></td>
                        <td><a class="a-btn a-btn-sm" href="{{ route('admin.inquiries.show', $i) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty"><strong>No inquiries found</strong>Messages from the Contact page and chatbot escalations appear here.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.admin-pagination', ['paginator' => $inquiries])
    </section>
@endsection
