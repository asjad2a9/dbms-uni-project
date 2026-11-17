@php
    $cur = config('gym.currency');
    $initials = fn ($name) => collect(explode(' ', $name))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
    $popularIndex = $plans->count() > 2 ? 1 : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · Strength &amp; Fitness Club</title>
    <meta name="description" content="{{ config('app.name') }} is a modern strength and fitness club with certified coaches, premium equipment and flexible membership plans.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}" rel="stylesheet">
</head>
<body>

{{-- Top info bar --}}
<div class="topbar-info d-none d-md-block">
    <div class="container d-flex justify-content-between py-2">
        <div class="d-flex gap-4">
            <span><i class="bi bi-geo-alt me-1"></i>{{ config('gym.address') }}</span>
            <span><i class="bi bi-clock me-1"></i>{{ config('gym.hours') }}</span>
        </div>
        <div class="d-flex gap-4">
            <span><i class="bi bi-telephone me-1"></i>{{ config('gym.phone') }}</span>
            <span><i class="bi bi-envelope me-1"></i>{{ config('gym.email') }}</span>
        </div>
    </div>
</div>

{{-- Navigation --}}
<nav class="navbar navbar-expand-lg navbar-dark site-nav sticky-top" id="siteNavbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#top">
            <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>{{ config('app.name') }}
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="siteNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#programs">Programs</a></li>
                <li class="nav-item"><a class="nav-link" href="#plans">Membership</a></li>
                <li class="nav-item"><a class="nav-link" href="#trainers">Coaches</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
            </ul>
            <div class="d-flex gap-2 mt-2 mt-lg-0">
                <a class="btn btn-outline-light btn-sm px-3" href="{{ auth()->check() ? route('admin.dashboard') : route('login') }}"><i class="bi bi-person-lock me-1"></i>{{ auth()->check() ? 'Dashboard' : 'Staff Login' }}</a>
                <a class="btn btn-brand btn-sm px-3" href="#contact">Join Now</a>
            </div>
        </div>
    </div>
</nav>

{{-- Hero --}}
<header class="hero" id="top">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col-xl-6">
                <span class="eyebrow">Strength &amp; Fitness Club</span>
                <h1 class="display">Build strength.<br><span class="text-brand">Earn results.</span></h1>
                <p class="lead">Professional coaching, premium equipment and a training floor built for serious progress. Start with a free visit and a personal fitness assessment.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="#plans" class="btn btn-brand btn-lg">View Membership Plans</a>
                    <a href="#contact" class="btn btn-outline-light btn-lg">Book a Free Visit</a>
                </div>
                <div class="hero-badges">
                    <span><i class="bi bi-patch-check-fill"></i>Certified coaches</span>
                    <span><i class="bi bi-shield-check"></i>No joining fee</span>
                    <span><i class="bi bi-clock-history"></i>Open 7 days</span>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Stats --}}
<div class="container stats-strip">
    <div class="inner">
        <div class="stat"><div class="num">{{ $stats['members'] }}<span>+</span></div><div class="label">Active members</div></div>
        <div class="stat"><div class="num">{{ $stats['trainers'] }}</div><div class="label">Certified coaches</div></div>
        <div class="stat"><div class="num">{{ $stats['plans'] }}</div><div class="label">Membership plans</div></div>
        <div class="stat"><div class="num">120<span>+</span></div><div class="label">Machines &amp; stations</div></div>
    </div>
</div>

{{-- About --}}
<section class="section" id="about">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-5">
                <div class="about-img">
                    <img src="{{ asset('images/about.jpg') }}" alt="Member training shoulder press" loading="lazy">
                    <div class="exp"><b>10+</b>Years of coaching</div>
                </div>
            </div>
            <div class="col-lg-7">
                <span class="eyebrow">About us</span>
                <h2 class="section-title">More than a gym. A place to get stronger.</h2>
                <p class="section-lead">{{ config('app.name') }} combines a fully equipped strength floor with coaching that tracks your progress. Every new member starts with a body assessment and a training plan built around their goals.</p>
                <ul class="check-list">
                    <li><i class="bi bi-check-circle-fill"></i>Free body composition check</li>
                    <li><i class="bi bi-check-circle-fill"></i>Personal training plans</li>
                    <li><i class="bi bi-check-circle-fill"></i>Clean, air-conditioned floor</li>
                    <li><i class="bi bi-check-circle-fill"></i>Lockers &amp; showers</li>
                    <li><i class="bi bi-check-circle-fill"></i>Nutrition guidance</li>
                    <li><i class="bi bi-check-circle-fill"></i>Flexible membership plans</li>
                </ul>
                <a href="#contact" class="btn btn-brand btn-lg">Start Your Free Visit</a>
            </div>
        </div>
    </div>
</section>

{{-- Programs --}}
<section class="section bg-soft" id="programs">
    <div class="container">
        <div class="row align-items-end mb-5 g-3">
            <div class="col-lg-7">
                <span class="eyebrow">Training programs</span>
                <h2 class="section-title mb-0">Programs for every goal</h2>
            </div>
            <div class="col-lg-5"><p class="section-lead mb-0">From your first session to competition prep, our coaches structure every program with clear progressions.</p></div>
        </div>
        <div class="row g-4">
            @foreach ([
                ['program-strength', 'Strength Training', 'Free weights and machines with structured progressive overload.', 'Popular'],
                ['program-powerlifting', 'Powerlifting', 'Squat, bench and deadlift technique with coached max days.', 'Coached'],
                ['program-calisthenics', 'Calisthenics', 'Pull-ups, dips and bodyweight strength from beginner to advanced.', 'All levels'],
                ['program-functional', 'Functional & HIIT', 'Battle ropes, sleds and circuits for conditioning and fat loss.', 'Fat loss'],
                ['program-bodyweight', 'Fitness Foundations', 'Mobility, core and form basics for new members.', 'Beginner'],
                ['program-conditioning', 'Body Transformation', '12-week guided plan with diet tracking and monthly check-ins.', '12 weeks'],
            ] as [$img, $title, $desc, $tag])
                <div class="col-sm-6 col-lg-4">
                    <a href="#contact" class="program">
                        <img src="{{ asset('images/'.$img.'.jpg') }}" alt="{{ $title }}" loading="lazy">
                        <div class="body"><span class="tag">{{ $tag }}</span><h3>{{ $title }}</h3><p>{{ $desc }}</p></div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Why us --}}
<section class="section bg-ink">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-4">
                <span class="eyebrow">Why choose us</span>
                <h2 class="section-title">Everything you need under one roof</h2>
                <p class="section-lead">Simple memberships, real coaching and a floor that is never too crowded to train.</p>
            </div>
            <div class="col-lg-8">
                <div class="row g-4">
                    @foreach ([
                        ['bi-trophy', 'Premium equipment', 'Commercial-grade racks, platforms, dumbbells up to 60 kg and modern cardio.'],
                        ['bi-person-check', 'Certified coaches', 'Qualified trainers on the floor during all opening hours.'],
                        ['bi-graph-up-arrow', 'Progress tracking', 'Regular weight, BMI and strength check-ins with your coach.'],
                        ['bi-calendar2-check', 'Flexible plans', 'Monthly to annual plans with easy renewals and no joining fee.'],
                    ] as [$icon, $t, $d])
                        <div class="col-md-6">
                            <div class="feature">
                                <div class="ico"><i class="bi {{ $icon }}"></i></div>
                                <div><h5>{{ $t }}</h5><p class="mb-0">{{ $d }}</p></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Pricing --}}
<section class="section" id="plans">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow">Membership</span>
            <h2 class="section-title">Choose your plan</h2>
            <p class="section-lead mx-auto">Transparent pricing with no hidden fees. Every plan includes full gym floor access and a free fitness assessment.</p>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach ($plans as $i => $plan)
                @php $popular = $i === $popularIndex; @endphp
                <div class="col-sm-6 col-lg-3">
                    <div class="price-card {{ $popular ? 'popular' : '' }}">
                        @if ($popular)<span class="ribbon">Best value</span>@endif
                        <div class="plan-name">{{ $plan->name }}</div>
                        <div class="duration">{{ $plan->durationLabel() }} membership</div>
                        <div class="price"><small>{{ $cur }}</small>{{ number_format($plan->price) }}</div>
                        <ul>
                            @foreach ($plan->featureList() as $f)<li><i class="bi bi-check2"></i>{{ $f }}</li>@endforeach
                        </ul>
                        <a href="#contact" data-plan="{{ $plan->id }}" class="btn {{ $popular ? 'btn-brand' : 'btn-outline-brand' }} w-100 choose-plan">Choose Plan</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Coaches --}}
<section class="section bg-soft" id="trainers">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow">Our coaches</span>
            <h2 class="section-title">Train with experts</h2>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach ($trainers as $t)
                <div class="col-sm-6 col-lg-3">
                    <div class="trainer">
                        <div class="mono">{{ $initials($t->name) }}</div>
                        <h5>{{ $t->name }}</h5>
                        <div class="spec">{{ $t->specialization }}</div>
                        <div class="exp"><i class="bi bi-award me-1"></i>{{ $t->experience_years }} years experience</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Testimonials --}}
<section class="section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow">Member stories</span>
            <h2 class="section-title">What our members say</h2>
        </div>
        <div class="row g-4">
            @foreach ([
                ['Lost 14 kg in five months. The coaches kept my plan simple and checked in every week.', 'Hamza Ali', 'Member since 2025'],
                ['Best equipped gym in the area. Proper platforms and plates for deadlifts, which is rare.', 'Faisal Qureshi', 'Powerlifting program'],
                ['Clean, well managed and never too crowded. Renewing my plan at the front desk takes a minute.', 'Omar Sheikh', 'Annual member'],
            ] as [$q, $n, $r])
                <div class="col-md-4">
                    <div class="quote">
                        <div class="stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                        <p>“{{ $q }}”</p>
                        <div class="who"><span class="mono">{{ $initials($n) }}</span><div><strong>{{ $n }}</strong><small>{{ $r }}</small></div></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="cta">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h2 class="display mb-2">Your first visit is free</h2>
                <p class="mb-0 fs-5 opacity-75">Tour the gym, meet a coach and get a free fitness assessment. No commitment.</p>
            </div>
            <div class="col-lg-4 text-lg-end"><a href="#contact" class="btn btn-light btn-lg fw-bold px-4">Book Now <i class="bi bi-arrow-right ms-1"></i></a></div>
        </div>
    </div>
</section>

{{-- Contact --}}
<section class="section bg-soft" id="contact">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <span class="eyebrow">Contact</span>
                <h2 class="section-title">Book your free visit</h2>
                <p class="section-lead mb-4">Send your details and our front desk will call you to schedule a tour and trial session.</p>
                <div class="info-item"><div class="ico"><i class="bi bi-geo-alt"></i></div><div><h6>Address</h6><p>{{ config('gym.address') }}</p></div></div>
                <div class="info-item"><div class="ico"><i class="bi bi-telephone"></i></div><div><h6>Phone</h6><p>{{ config('gym.phone') }}</p></div></div>
                <div class="info-item"><div class="ico"><i class="bi bi-envelope"></i></div><div><h6>Email</h6><p>{{ config('gym.email') }}</p></div></div>
                <div class="info-item"><div class="ico"><i class="bi bi-clock"></i></div><div><h6>Opening hours</h6><p>{{ config('gym.hours') }}</p></div></div>
            </div>
            <div class="col-lg-7">
                <div class="contact-card">
                    @if (session('success'))
                        <div class="alert alert-success d-flex gap-2 align-items-center"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <h4 class="fw-bold mb-4">Request a call back</h4>
                    <form method="POST" action="{{ route('join') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6"><label class="form-label" for="f-name">Full name *</label><input id="f-name" name="name" value="{{ old('name') }}" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label" for="f-phone">Phone *</label><input id="f-phone" name="phone" value="{{ old('phone') }}" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label" for="f-email">Email</label><input id="f-email" type="email" name="email" value="{{ old('email') }}" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label" for="planSelect">Interested plan</label>
                            <select name="membership_plan_id" id="planSelect" class="form-select">
                                <option value="">Not sure yet</option>
                                @foreach ($plans as $p)<option value="{{ $p->id }}" @selected(old('membership_plan_id') == $p->id)>{{ $p->name }} – {{ $cur }} {{ number_format($p->price) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label" for="f-msg">Message</label><textarea id="f-msg" name="message" rows="4" class="form-control" placeholder="Your goals and preferred training time">{{ old('message') }}</textarea></div>
                        <div class="col-12"><button type="submit" class="btn btn-brand btn-lg w-100"><i class="bi bi-send me-1"></i> Send Request</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="site-footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <a href="#top" class="d-flex align-items-center gap-2 text-white text-decoration-none mb-3" style="font-family:var(--display);font-size:1.6rem;font-weight:800;text-transform:uppercase">
                    <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>{{ config('app.name') }}
                </a>
                <p>A strength and fitness club with certified coaches, premium equipment and memberships for every goal.</p>
                <div class="social mt-3">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Explore</h6>
                <ul><li><a href="#about">About</a></li><li><a href="#programs">Programs</a></li><li><a href="#plans">Membership</a></li><li><a href="#trainers">Coaches</a></li></ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Opening hours</h6>
                <ul><li>Mon – Sat: 6:00 AM – 11:00 PM</li><li>Sunday: 8:00 AM – 2:00 PM</li><li>Public holidays: 9:00 AM – 1:00 PM</li></ul>
            </div>
            <div class="col-lg-3">
                <h6>Contact</h6>
                <ul><li><i class="bi bi-geo-alt me-2 text-brand"></i>{{ config('gym.address') }}</li><li><i class="bi bi-telephone me-2 text-brand"></i>{{ config('gym.phone') }}</li><li><i class="bi bi-envelope me-2 text-brand"></i>{{ config('gym.email') }}</li></ul>
            </div>
        </div>
        <div class="bottom d-flex flex-column flex-md-row justify-content-between gap-2">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
            <a href="{{ route('login') }}">Staff login</a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.choose-plan').forEach(b => b.addEventListener('click', () => { document.getElementById('planSelect').value = b.dataset.plan; }));
    const nav = document.getElementById('siteNavbar');
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 20);
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
    document.querySelectorAll('#siteNav a[href^="#"]').forEach(l => l.addEventListener('click', () => { const c = bootstrap.Collapse.getInstance(document.getElementById('siteNav')); c && c.hide(); }));
</script>
</body>
</html>
