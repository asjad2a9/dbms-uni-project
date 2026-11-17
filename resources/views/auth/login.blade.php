<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <style>
        .auth-wrap { min-height: 100vh; display: grid; grid-template-columns: 1.1fr 1fr; }
        .auth-hero { background: linear-gradient(160deg, rgba(17,24,39,.92), rgba(17,24,39,.75)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1400&q=70') center/cover; color: #fff; padding: 3rem; display: flex; flex-direction: column; justify-content: space-between; }
        .auth-hero h2 { font-weight: 800; font-size: 2.6rem; line-height: 1.1; }
        .auth-form { display: grid; place-items: center; padding: 2rem 1.25rem; background: #fff; }
        .auth-card { width: 100%; max-width: 400px; }
        @media (max-width: 991.98px) { .auth-wrap { grid-template-columns: 1fr; } .auth-hero { display: none; } }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-hero">
        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none fw-bold fs-5">
            <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>{{ config('app.name') }}
        </a>
        <div>
            <h2>Run your gym<br><span class="text-brand">like a pro.</span></h2>
            <p class="text-white-50 mt-3 mb-0" style="max-width:440px">Members, subscriptions, payments, attendance, trainers and reports — all in one simple dashboard.</p>
        </div>
        <small class="text-white-50">&copy; {{ date('Y') }} {{ config('app.name') }}</small>
    </div>
    <div class="auth-form">
        <div class="auth-card">
            <div class="d-lg-none text-center mb-4">
                <span class="brand-icon mx-auto mb-2" style="width:48px;height:48px;font-size:1.4rem"><i class="bi bi-lightning-charge-fill"></i></span>
                <div class="fw-bold fs-5">{{ config('app.name') }}</div>
            </div>
            <h3 class="fw-bold mb-1">Welcome back 👋</h3>
            <p class="text-muted mb-4">Sign in to the admin panel</p>
            @include('partials.flash')
            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="admin@gym.test" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('home') }}" class="small">← Back to website</a>
                </div>
                <button class="btn btn-primary w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i> Sign In</button>
            </form>
            <div class="alert alert-light border small mt-4 mb-0">
                <strong>Demo:</strong> admin@gym.test / password<br>
                <strong>Staff:</strong> staff@gym.test / password
            </div>
        </div>
    </div>
</div>
</body>
</html>
