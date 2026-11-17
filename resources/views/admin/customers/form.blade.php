@extends('layouts.admin')
@section('title', $customer->exists ? 'Edit customer' : 'Add customer')
@section('actions')<a class="a-btn" href="{{ route('admin.customers.index') }}">← Customers</a>@endsection

@section('content')
    <section class="a-card" style="max-width:720px">
        <form method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
            @csrf
            @if($customer->exists) @method('PUT') @endif
            <div class="form-grid">
                <label class="admin-field"><span>Full name</span><input name="name" value="{{ old('name', $customer->name) }}" required maxlength="255"></label>
                <label class="admin-field"><span>Email</span><input type="email" name="email" value="{{ old('email', $customer->email) }}" required maxlength="255"></label>
                <label class="admin-field"><span>Phone</span><input name="phone" value="{{ old('phone', $customer->phone) }}" maxlength="30"></label>
                <label class="admin-field"><span>Status</span><select name="status">@foreach(\App\Models\Customer::STATUSES as $s)<option @selected(old('status', $customer->status) === $s)>{{ $s }}</option>@endforeach</select></label>
            </div>
            <p class="muted">Customers set their own password when they register on the website. Admins cannot see or set customer passwords.</p>
            <button class="a-btn a-btn-primary" type="submit">{{ $customer->exists ? 'Save changes' : 'Add customer' }}</button>
        </form>
    </section>
@endsection
