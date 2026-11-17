@extends('layouts.admin')
@section('title', 'Dashboard')
@section('subtitle', now()->format('l, d F Y'))
@section('actions')
    <a href="{{ route('admin.members.create') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center gap-1"><i class="bi bi-person-plus"></i> New Member</a>
@endsection

@section('content')
@php $cur = config('gym.currency'); @endphp
<div class="row g-3 mb-3">
    @foreach ([
        ['Total Members', $stats['members'], 'bi-people', 'primary', route('admin.members.index')],
        ['Active Subscribers', $stats['active_members'], 'bi-person-check', 'success', route('admin.subscriptions.index', ['status' => 'active'])],
        ["Today's Check-ins", $stats['today_checkins'], 'bi-calendar-check', 'info', route('admin.attendance.index')],
        ['Income (This Month)', $cur.' '.number_format($stats['month_income']), 'bi-cash-stack', 'purple', route('admin.payments.index')],
        ['Expenses (This Month)', $cur.' '.number_format($stats['month_expense']), 'bi-wallet2', 'danger', route('admin.expenses.index')],
        ['New Inquiries', $stats['new_inquiries'], 'bi-envelope-paper', 'warning', route('admin.inquiries.index', ['status' => 'new'])],
    ] as [$label, $value, $icon, $color, $link])
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ $link }}" class="card h-100 text-decoration-none text-reset">
                <div class="card-body">
                    <div class="stat-icon bg-soft-{{ $color }} mb-3"><i class="bi {{ $icon }}"></i></div>
                    <div class="stat-value">{{ $value }}</div>
                    <div class="stat-label mt-1">{{ $label }}</div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up-arrow me-1 text-brand"></i> Income vs Expenses (6 months)</span>
                <a href="{{ route('admin.reports.index') }}" class="small">Full report →</a>
            </div>
            <div class="card-body"><div style="height:290px"><canvas id="incomeChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-activity me-1 text-brand"></i> Attendance (last 7 days)</div>
            <div class="card-body"><div style="height:290px"><canvas id="attChart"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-hourglass-split me-1 text-danger"></i> Expiring in 7 days</span>
                <a href="{{ route('admin.subscriptions.index', ['expiring' => 1]) }}" class="small">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Member</th><th>Plan</th><th>Expires</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($expiring as $s)
                        <tr>
                            <td><a href="{{ route('admin.members.show', $s->member) }}" class="fw-semibold text-reset">{{ $s->member->name }}</a><div class="small text-muted">{{ $s->member->phone }}</div></td>
                            <td>{{ $s->plan->name }}</td>
                            <td><span class="badge badge-soft-{{ $s->daysLeft() <= 2 ? 'danger' : 'warning' }}">{{ $s->end_date->format('d M') }} · {{ $s->daysLeft() }}d</span></td>
                            <td class="text-end"><a href="{{ route('admin.subscriptions.create', ['member_id' => $s->member_id]) }}" class="btn btn-sm btn-outline-primary">Renew</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state py-4"><i class="bi bi-emoji-smile"></i>No subscriptions expiring soon</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-1 text-success"></i> Recent Payments</span>
                <a href="{{ route('admin.payments.index') }}" class="small">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Invoice</th><th>Member</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                    @forelse ($recentPayments as $p)
                        <tr>
                            <td><a href="{{ route('admin.payments.show', $p) }}">{{ $p->invoice_no }}</a></td>
                            <td>{{ $p->member->name }}</td>
                            <td class="text-muted">{{ $p->paid_at->format('d M Y') }}</td>
                            <td class="text-end fw-semibold">{{ $cur }} {{ number_format($p->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state py-4"><i class="bi bi-inbox"></i>No payments yet</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-plus me-1 text-brand"></i> Newest Members</span>
                <a href="{{ route('admin.members.index') }}" class="small">All members</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Member</th><th>Phone</th><th>Plan</th><th>Joined</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach ($recentMembers as $m)
                        <tr>
                            <td><a href="{{ route('admin.members.show', $m) }}" class="d-flex align-items-center gap-2 text-reset text-decoration-none"><span class="avatar avatar-sm">{{ $m->initials() }}</span><span><span class="fw-semibold">{{ $m->name }}</span><br><small class="text-muted">{{ $m->member_code }}</small></span></a></td>
                            <td>{{ $m->phone }}</td>
                            <td>{{ $m->activeSubscription?->plan?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $m->joined_at->format('d M Y') }}</td>
                            <td>@include('partials.status-badge', ['status' => $m->status])</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = '#6b7280';
    new Chart(document.getElementById('incomeChart'), {
        type: 'bar',
        data: {
            labels: @json($chart['labels']),
            datasets: [
                { label: 'Income', data: @json($chart['income']), backgroundColor: '#f97316', borderRadius: 6, maxBarThickness: 34 },
                { label: 'Expenses', data: @json($chart['expense']), backgroundColor: '#cbd5e1', borderRadius: 6, maxBarThickness: 34 }
            ]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
    });
    new Chart(document.getElementById('attChart'), {
        type: 'line',
        data: { labels: @json($attendanceChart['labels']), datasets: [{ label: 'Check-ins', data: @json($attendanceChart['data']), borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,.12)', fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: '#f97316' }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
    });
</script>
@endpush
