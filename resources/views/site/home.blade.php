<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · Train Hard, Live Strong</title>
    <meta name="description" content="{{ config('app.name') }} — modern fitness club with expert trainers and flexible membership plans.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <style>
        body { background: #fff; scroll-behavior: smooth; }
        .site-nav { background: rgba(17,24,39,.85); backdrop-filter: blur(10px); }
        .site-nav .nav-link { color: #d1d5db; font-weight: 500; }
        .site-nav .nav-link:hover { color: #fff; }
        .hero { min-height: 92vh; color: #fff; display: flex; align-items: center; background: linear-gradient(100deg, rgba(17,24,39,.95) 30%, rgba(17,24,39,.45)), url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=1800&q=70') center/cover; padding-top: 80px; }
        .hero h1 { font-weight: 900; font-size: clamp(2.4rem, 6vw, 4.5rem); line-height: 1.02; letter-spacing: -.02em; }
        .hero .lead { color: #d1d5db; max-width: 560px; }
        .hero-stats .num { font-size: 2rem; font-weight: 800; color: var(--brand); }
        section { padding: 5rem 0; }
        .eyebrow { color: var(--brand); font-weight: 700; text-transform: uppercase; letter-spacing: .12em; font-size: .8rem; }
        .section-title { font-weight: 800; font-size: clamp(1.8rem, 4vw, 2.6rem); }
        .feature { padding: 1.75rem; border-radius: 16px; border: 1px solid var(--card-border); height: 100%; transition: .2s; }
        .feature:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(16,24,40,.08); }
        .feature .stat-icon { margin-bottom: 1rem; }
        .price-card { border-radius: 18px; border: 1px solid var(--card-border); padding: 2rem; height: 100%; display: flex; flex-direction: column; background: #fff; transition: .2s; }
        .price-card.popular { background: #111827; color: #fff; border-color: #111827; transform: scale(1.03); box-shadow: 0 20px 40px rgba(17,24,39,.25); }
        .price-card .price { font-size: 2.4rem; font-weight: 800; }
        .price-card ul { list-style: none; padding: 0; margin: 1.25rem 0 1.75rem; flex: 1; }
        .price-card li { padding: .35rem 0; }
        .price-card li i { color: var(--brand); margin-right: .4rem; }
        .trainer-card { text-align: center; border-radius: 18px; border: 1px solid var(--card-border); padding: 2rem 1.25rem; height: 100%; }
        .trainer-card .avatar { width: 88px; height: 88px; font-size: 2rem; margin-bottom: 1rem; }
        .bg-dark-section { background: #111827; color: #fff; }
        .join-card { border-radius: 20px; background: #fff; color: #1f2937; padding: 2rem; }
        footer.site-footer { background: #0b1220; color: #9ca3af; padding: 2.5rem 0; }
        @media (max-width: 767.98px) { .price-card.popular { transform: none; } section { padding: 3.5rem 0; } }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top site-nav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="#">
            <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>{{ config('app.name') }}
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="siteNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#plans">Plans</a></li>
                <li class="nav-item"><a class="nav-link" href="#trainers">Trainers</a></li>
                <li class="nav-item"><a class="nav-link" href="#join">Contact</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm px-3" href="{{ auth()->check() ? route('admin.dashboard') : route('login') }}"><i class="bi bi-person-lock me-1"></i>{{ auth()->check() ? 'Dashboard' : 'Staff Login' }}</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <span class="badge rounded-pill bg-white bg-opacity-10 text-white px-3 py-2 mb-3"><i class="bi bi-fire text-brand me-1"></i> New members get a free trainer session</span>
                <h1>Train hard.<br><span class="text-brand">Live strong.</span></h1>
                <p class="lead mt-3 mb-4">State-of-the-art equipment, certified coaches and membership plans that fit your goals and your budget.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#plans" class="btn btn-primary btn-lg px-4">View Plans</a>
                    <a href="#join" class="btn btn-outline-light btn-lg px-4">Book a Free Visit</a>
                </div>
                <div class="row hero-stats mt-5 g-4" style="max-width:520px">
                    <div class="col-4"><div class="num">{{ $stats['members'] }}+</div><small class="text-white-50">Active members</small></div>
                    <div class="col-4"><div class="num">{{ $stats['trainers'] }}</div><small class="text-white-50">Expert trainers</small></div>
                    <div class="col-4"><div class="num">{{ $stats['plans'] }}</div><small class="text-white-50">Flexible plans</small></div>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="features">
    <div class="container">
        <div class="text-center mb-5">
            <div class="eyebrow">Why choose us</div>
            <h2 class="section-title">Everything you need to get fit</h2>
        </div>
        <div class="row g-4">
            @foreach ([
                ['bi-heart-pulse', 'primary', 'Cardio Zone', 'Treadmills, bikes and rowers with heart-rate tracking.'],
                ['bi-trophy', 'success', 'Strength Training', 'Free weights, racks and machines for every level.'],
                ['bi-person-arms-up', 'info', 'Personal Coaching', 'Certified trainers build a plan around your goals.'],
                ['bi-clock-history', 'purple', 'Open Early & Late', config('gym.hours')],
            ] as [$icon, $c, $t, $d])
                <div class="col-sm-6 col-lg-3">
                    <div class="feature">
                        <div class="stat-icon bg-soft-{{ $c }}"><i class="bi {{ $icon }}"></i></div>
                        <h5 class="fw-bold">{{ $t }}</h5>
                        <p class="text-muted mb-0">{{ $d }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="plans" style="background:#f8fafc">
    <div class="container">
        <div class="text-center mb-5">
            <div class="eyebrow">Membership</div>
            <h2 class="section-title">Simple, transparent pricing</h2>
            <p class="text-muted">No hidden fees. Cancel any time.</p>
        </div>
        <div class="row g-4 justify-content-center align-items-stretch">
            @foreach ($plans as $i => $plan)
                @php $popular = $plans->count() > 2 && $i === 1; @endphp
                <div class="col-sm-6 col-lg-3">
                    <div class="price-card {{ $popular ? 'popular' : '' }}">
                        @if ($popular)<span class="badge bg-primary align-self-start mb-2" style="background:var(--brand)!important">Most popular</span>@endif
                        <h5 class="fw-bold mb-1">{{ $plan->name }}</h5>
                        <small class="{{ $popular ? 'text-white-50' : 'text-muted' }}">{{ $plan->durationLabel() }}</small>
                        <div class="price mt-3">{{ config('gym.currency') }} {{ number_format($plan->price) }}</div>
                        <ul>
                            @foreach ($plan->featureList() as $f)<li><i class="bi bi-check-circle-fill"></i>{{ $f }}</li>@endforeach
                        </ul>
                        <a href="#join" data-plan="{{ $plan->id }}" class="btn {{ $popular ? 'btn-primary' : 'btn-outline-primary' }} w-100 choose-plan">Choose Plan</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="trainers">
    <div class="container">
        <div class="text-center mb-5">
            <div class="eyebrow">Our team</div>
            <h2 class="section-title">Meet the coaches</h2>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach ($trainers as $t)
                <div class="col-sm-6 col-lg-3">
                    <div class="trainer-card">
                        <span class="avatar mx-auto">{{ collect(explode(' ', $t->name))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}</span>
                        <h5 class="fw-bold mb-1">{{ $t->name }}</h5>
                        <div class="text-brand fw-semibold small mb-2">{{ $t->specialization }}</div>
                        <p class="text-muted small mb-0">{{ $t->experience_years }} years experience</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="join" class="bg-dark-section">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <div class="eyebrow">Get started</div>
                <h2 class="section-title">Book your free visit today</h2>
                <p class="text-white-50">Leave your details and our team will call you to schedule a free tour and trial workout.</p>
                <ul class="list-unstyled mt-4 text-white-50">
                    <li class="mb-2"><i class="bi bi-geo-alt text-brand me-2"></i>{{ config('gym.address') }}</li>
                    <li class="mb-2"><i class="bi bi-telephone text-brand me-2"></i>{{ config('gym.phone') }}</li>
                    <li class="mb-2"><i class="bi bi-envelope text-brand me-2"></i>{{ config('gym.email') }}</li>
                    <li><i class="bi bi-clock text-brand me-2"></i>{{ config('gym.hours') }}</li>
                </ul>
            </div>
            <div class="col-lg-7">
                <div class="join-card">
                    @include('partials.flash')
                    <form method="POST" action="{{ route('join') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6"><label class="form-label">Full name *</label><input name="name" value="{{ old('name') }}" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Phone *</label><input name="phone" value="{{ old('phone') }}" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Interested plan</label>
                            <select name="membership_plan_id" id="planSelect" class="form-select">
                                <option value="">Not sure yet</option>
                                @foreach ($plans as $p)<option value="{{ $p->id }}" @selected(old('membership_plan_id') == $p->id)>{{ $p->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">Message</label><textarea name="message" rows="3" class="form-control" placeholder="Your goals, preferred timing…">{{ old('message') }}</textarea></div>
                        <div class="col-12"><button class="btn btn-primary btn-lg w-100"><i class="bi bi-send me-1"></i> Send Request</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
        <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
        <span><a href="{{ route('login') }}" class="text-reset">Staff login</a></span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.choose-plan').forEach(b => b.addEventListener('click', () => { document.getElementById('planSelect').value = b.dataset.plan; }));
    document.querySelectorAll('#siteNav .nav-link').forEach(l => l.addEventListener('click', () => { const c = bootstrap.Collapse.getInstance(document.getElementById('siteNav')); c && c.hide(); }));
</script>
</body>
</html>
