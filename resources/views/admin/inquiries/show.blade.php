@extends('layouts.admin')
@section('title', 'Inquiry from '.$inquiry->name)
@section('actions')<a class="a-btn" href="{{ route('admin.inquiries.index') }}">← Inquiries</a>@endsection

@section('content')
    <div class="grid-2">
        <section class="a-card">
            <div class="a-card-head"><div><h2>{{ $inquiry->subject }}</h2><p>{{ $inquiry->created_at?->format('l, F j, Y g:i A') }} · {{ $inquiry->source === 'chatbot' ? 'Chat escalation' : 'Contact form' }}</p></div><span class="badge badge-{{ strtolower($inquiry->status) }}">{{ $inquiry->status }}</span></div>
            <div class="message-box">{{ $inquiry->message }}</div>
            <p style="margin-top:16px"><a class="a-btn" href="mailto:{{ $inquiry->email }}?subject={{ rawurlencode('Re: '.$inquiry->subject) }}">Reply by email ↗</a></p>
        </section>
        <section class="a-card">
            <div class="a-card-head"><div><h2>Manage</h2><p>{{ $inquiry->name }} · {{ $inquiry->email }}</p></div></div>
            <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">
                @csrf @method('PATCH')
                <label class="admin-field"><span>Status</span><select name="status">@foreach(\App\Models\Inquiry::STATUSES as $s)<option @selected($inquiry->status === $s)>{{ $s }}</option>@endforeach</select></label>
                <label class="admin-field"><span>Administrator response / notes</span><textarea name="admin_response" rows="6" maxlength="5000" placeholder="Record how this inquiry was answered">{{ old('admin_response', $inquiry->admin_response) }}</textarea>
                    @if($inquiry->responded_at)<small>Last response recorded {{ $inquiry->responded_at->format('M j, Y g:i A') }}</small>@endif</label>
                <button class="a-btn a-btn-primary" type="submit">Save</button>
            </form>
        </section>
    </div>
@endsection
