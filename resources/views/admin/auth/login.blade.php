<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="robots" content="noindex, nofollow">
    <title>Administrator Sign In · Blush &amp; Bloom Salon – Admin Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght,SOFT,WONK@0,9..144,300..500,100,0;1,9..144,300..500,100,0&family=Figtree:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-login">
    <main class="login-shell">
        <section class="login-intro" style="background-image:url('{{ \App\Support\SalonImages::url('images/services/makeup-2.jpg', 1400) }}')">
            <div class="admin-brand">
                @include('partials.logo', ['class' => 'admin-brand-mark'])
                <span><strong>Blush &amp; Bloom</strong><small>ADMIN PORTAL</small></span>
            </div>
            <div>
                <h1>Your salon,<br><em>beautifully managed.</em></h1>
                <p>Appointments, customers, services, inquiries, and automation — all in one private workspace.</p>
                <div class="login-badges"><span>✿ Appointments</span><span>✿ Customers</span><span>✿ AI assistant</span></div>
            </div>
            <small>Authorized salon administrators only.</small>
        </section>
        <section class="login-form-wrap">
            <form class="login-card" method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                @include('partials.logo', ['class' => 'login-mark'])
                <h2>Administrator sign in</h2>
                <p>Use your administrator account. Customer accounts cannot sign in here.</p>
                @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="alert alert-error" role="alert">{{ $errors->first() }}</div>@endif
                <label class="admin-field"><span>Email address</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus maxlength="255"></label>
                <label class="admin-field"><span>Password</span><input type="password" name="password" autocomplete="current-password" required></label>
                <button class="a-btn a-btn-primary a-btn-block" type="submit">Sign in to Admin Portal</button>
                <a class="login-back" href="{{ route('home') }}">← Back to the customer website</a>
            </form>
        </section>
    </main>
</body>
</html>
