@extends('layouts.admin')
@php $cur = config('gym.currency'); @endphp
@section('title', 'Reports')
@section('subtitle', 'Financial & activity summary for ' . $year)
@section('actions')
    <form method="GET" class="d-flex gap-2 no-print">
        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Year">
            @foreach ($years as $y)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endforeach
        </select>
    </form>
    <button type="button" class="btn btn-outline-primary btn-sm no-print" onclick="window.print()"><i class="bi bi-printer"></i> <span class="d-none d-sm-inline">Print</span></button>
@endsection


@section('content')
{{-- Summary cards --}}
<div class="row g-3 mb-3">
    @foreach ([
        ['Total Income', $cur . ' ' . number_format($totals['income'], 2), 'bi-cash-stack', 'primary'],
        ['Total Expenses', $cur . ' ' . number_format($totals['expenses'], 2), 'bi-wallet2', 'danger'],
        ['Net Profit', $cur . ' ' . number_format($totals['profit'], 2), 'bi-graph-up-arrow', $totals['profit'] >= 0 ? 'success' : 'danger'],
        ['New Members', number_format($totals['members']), 'bi-person-plus', 'info'],
    ] as [$label, $value, $icon, $color])
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body stat-card">
                    <div class="stat-icon bg-soft-{{ $color }}"><i class="bi {{ $icon }}"></i></div>
                    <div class="min-w-0">
                        <div class="stat-label">{{ $label }}</div>
                        <div class="stat-value text-truncate">{{ $value }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">Income vs Expenses ({{ $year }})</div>
            <div class="card-body"><div style="position:relative;height:300px"><canvas id="incomeExpenseChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Income by Payment Method</div>
            <div class="card-body">
                @if ($totals['income'] > 0)
                    <div style="position:relative;height:300px"><canvas id="methodChart"></canvas></div>
                @else
                    <div class="empty-state"><i class="bi bi-inbox"></i>No records found</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">New Members per Month</div>
            <div class="card-body"><div style="position:relative;height:260px"><canvas id="membersChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Members by Status</div>
            <div class="card-body">
                @php $statusTotal = max(1, $membersByStatus->sum()); @endphp
                @foreach (['active' => 'success', 'inactive' => 'secondary', 'suspended' => 'warning'] as $status => $color)
                    @php $n = $membersByStatus[$status] ?? 0; @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-capitalize">{{ $status }}</span><span class="fw-semibold">{{ $n }}</span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar bg-{{ $color }}" style="width: {{ round($n / $statusTotal * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
                <div class="small text-muted">Total members: {{ $membersByStatus->sum() }} · Visits in {{ $year }}: {{ number_format($totals['visits']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Monthly table --}}
<div class="card mb-3">
    <div class="card-header">Monthly Breakdown — {{ $year }}</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-end">Income</th>
                    <th class="text-end">Expenses</th>
                    <th class="text-end">Profit</th>
                    <th class="text-end">New Members</th>
                    <th class="text-end">Visits</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m => $label)
                    @php $p = $income[$m] - $expense[$m]; @endphp
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="text-end text-nowrap">{{ $cur }} {{ number_format($income[$m], 2) }}</td>
                        <td class="text-end text-nowrap">{{ $cur }} {{ number_format($expense[$m], 2) }}</td>
                        <td class="text-end text-nowrap {{ $p < 0 ? 'text-danger' : ($p > 0 ? 'text-success' : '') }}">{{ $cur }} {{ number_format($p, 2) }}</td>
                        <td class="text-end">{{ $newMembers[$m] }}</td>
                        <td class="text-end">{{ $attendance[$m] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="fw-bold table-light">
                <tr>
                    <td>Total</td>
                    <td class="text-end text-nowrap">{{ $cur }} {{ number_format($totals['income'], 2) }}</td>
                    <td class="text-end text-nowrap">{{ $cur }} {{ number_format($totals['expenses'], 2) }}</td>
                    <td class="text-end text-nowrap {{ $totals['profit'] < 0 ? 'text-danger' : 'text-success' }}">{{ $cur }} {{ number_format($totals['profit'], 2) }}</td>
                    <td class="text-end">{{ $totals['members'] }}</td>
                    <td class="text-end">{{ $totals['visits'] }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Top Plans ({{ $year }})</div>
            @if ($topPlans->where('year_count', '>', 0)->isEmpty())
                <div class="empty-state"><i class="bi bi-inbox"></i>No records found</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Plan</th><th class="text-end">Subscriptions</th><th class="text-end">Value</th></tr></thead>
                        <tbody>
                            @foreach ($topPlans->where('year_count', '>', 0) as $plan)
                                <tr>
                                    <td>{{ $plan->name }} <small class="text-muted">· {{ $plan->durationLabel() }}</small></td>
                                    <td class="text-end">{{ $plan->year_count }}</td>
                                    <td class="text-end text-nowrap">{{ $cur }} {{ number_format((float) $plan->year_revenue, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Expenses by Category ({{ $year }})</div>
            @if ($expensesByCategory->isEmpty())
                <div class="empty-state"><i class="bi bi-inbox"></i>No records found</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Category</th><th class="text-end">Amount</th><th class="text-end">Share</th></tr></thead>
                        <tbody>
                            @foreach ($expensesByCategory as $category => $amount)
                                <tr>
                                    <td class="text-capitalize">{{ $category }}</td>
                                    <td class="text-end text-nowrap">{{ $cur }} {{ number_format($amount, 2) }}</td>
                                    <td class="text-end">{{ $totals['expenses'] > 0 ? round($amount / $totals['expenses'] * 100, 1) : 0 }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json(array_values($months));
    const currency = @json($cur);
    const money = v => currency + ' ' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#6b7280';

    new Chart(document.getElementById('incomeExpenseChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Income', data: @json(array_values($income)), backgroundColor: '#f97316', borderRadius: 4 },
                { label: 'Expenses', data: @json(array_values($expense)), backgroundColor: '#9ca3af', borderRadius: 4 },
            ],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => c.dataset.label + ': ' + money(c.parsed.y) } } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => money(v) } }, x: { grid: { display: false } } },
        },
    });

    new Chart(document.getElementById('membersChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'New Members', data: @json(array_values($newMembers)),
                borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,.12)', fill: true, tension: .35, pointRadius: 3,
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } },
        },
    });

    const methodEl = document.getElementById('methodChart');
    if (methodEl) {
        new Chart(methodEl, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($incomeByMethod)),
                datasets: [{ data: @json(array_values($incomeByMethod)), backgroundColor: ['#f97316', '#0ea5e9', '#22c55e', '#a855f7'], borderWidth: 2 }],
            },
            options: {
                maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => c.label + ': ' + money(c.parsed) } } },
            },
        });
    }
})();
</script>
@endpush
