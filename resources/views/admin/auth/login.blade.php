<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — {{ config('admin.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="admin-body admin-auth-page">
    <div class="admin-auth-card">
        <div class="admin-auth-brand">
            <x-admin.logo class="admin-auth-logo" />
            <p class="admin-auth-subtitle">Sign in to your admin account</p>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error mb-4" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="admin-label" for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username" class="admin-input" placeholder="you@company.com">
            </div>
            <div>
                <label class="admin-label" for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="admin-input" placeholder="••••••••">
            </div>
            <label class="admin-checkbox">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Remember me on this device
            </label>
            <button type="submit" class="admin-btn admin-btn-primary w-full !py-2.5">
                Sign in
            </button>
        </form>

        <p class="admin-auth-back" style="margin-top:1.5rem;text-align:center;font-size:0.875rem">
            <a href="{{ route('home', absolute: false) }}" style="color:var(--admin-primary,#10498c)">← Back to website</a>
        </p>
    </div>
</body>
</html>
