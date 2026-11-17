{{--
    CONTACT PAGE — Member 3: Anthony Josh Orbiso
    Script: client-side form validation (empty fields, email format)
    with error messages and a success message → public/js/contact.js
    Styles → public/css/contact.css
    The form is also validated by Laravel and saved to the `inquiries` table.
--}}
@extends('layouts.app')

@section('title', 'Contact')
@section('handles_errors', '1')


@section('content')
    @php
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $openDays = count($hours['open_days']) === 7 ? 'Monday – Sunday' : collect($hours['open_days'])->map(fn ($d) => $dayNames[$d])->implode(', ');
    @endphp

    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Contact us</span>
            <h1>We'd love to <em>hear from you</em></h1>
            <p>Have a question about a service, pricing, or your appointment? Send us a message and our team will get back to you.</p>
        </div>
    </section>

@endsection
