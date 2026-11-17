@extends('layouts.app')
@section('title', 'Page not found')
@section('content')
    <section class="page-hero" style="padding:120px 0">
        <div class="container">
            <span class="eyebrow">Error 404</span>
            <h1>This page has <em>wilted</em></h1>
            <p>The page you were looking for doesn't exist or has moved.</p>
            <p style="margin-top:24px"><a class="btn btn-primary" href="{{ route('home') }}">Back to Home</a> <a class="btn btn-outline" href="{{ route('salon.index') }}">View services</a></p>
        </div>
    </section>
@endsection
