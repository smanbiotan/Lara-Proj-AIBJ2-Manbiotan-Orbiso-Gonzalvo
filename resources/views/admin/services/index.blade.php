@extends('layouts.admin')
@section('title', 'Services')
@section('actions')<a class="a-btn a-btn-primary" href="{{ route('admin.services.create') }}">+ Add service</a>@endsection

@section('content')
    <div class="alert alert-info">These services, prices, and durations power the public Salon page, booking form, and AI assistant. Hidden services cannot be booked.</div>
    <section class="a-card">
        <div class="table-wrap">
            <table class="a-table">
                <thead><tr><th>Photo</th><th>Service</th><th>Category</th><th>Starting price</th><th>Duration</th><th>Bookings</th><th>Visibility</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($services as $s)
                    <tr>
                        <td><img class="thumb" src="{{ $s->image_url }}" alt=""></td>
                        <td><strong>{{ $s->name }}</strong><br><span class="muted mono">/salon/{{ $s->slug }}</span></td>
                        <td>{{ $s->category_label }}</td>
                        <td>{{ $s->price_label }}</td>
                        <td>{{ $s->duration_label }}</td>
                        <td>{{ $s->appointments_count }}</td>
                        <td><span class="badge badge-{{ $s->is_active ? 'active' : 'inactive' }}">{{ $s->is_active ? 'Available' : 'Hidden' }}</span></td>
                        <td>
                            <div class="actions-cell">
                                <a class="a-btn a-btn-sm" href="{{ route('admin.services.edit', $s->id) }}">Edit</a>
                                <form class="inline-form" method="POST" action="{{ route('admin.services.toggle', $s->id) }}">@csrf @method('PATCH')<button class="a-btn a-btn-sm" type="submit">{{ $s->is_active ? 'Disable' : 'Enable' }}</button></form>
                                @if($s->is_active)<a class="a-btn a-btn-sm" href="{{ route('salon.show', $s) }}" target="_blank" rel="noopener">View ↗</a>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty"><strong>No services yet</strong>Add your first service to show it on the Salon page.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
