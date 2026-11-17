@extends('layouts.admin')
@section('title', 'Payments')
@section('subtitle', 'Invoices and received payments')
@section('actions')
    <a href="{{ route('admin.payments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Record Payment</span></a>
@endsection

@php $cur = config('gym.currency'); @endphp

@section('content')
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-4">
        <div class="card"><div class="card-body stat-card">
            <div class="stat-icon bg-soft-primary"><i class="bi bi-funnel"></i></div>
            <div><div class="stat-value">{{ $cur }} {{ number_format($filteredTotal, 2) }}</div><div class="stat-label">Filtered Total</div></div>
        </div></div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card"><div class="card-body stat-card">
            <div class="stat-icon bg-soft-success"><i class="bi bi-calendar-month"></i></div>
            <div><div class="stat-value">{{ $cur }} {{ number_format($monthTotal, 2) }}</div><div class="stat-label">This Month</div></div>
        </div></div>
    </div>
    <div class="col-sm-12 col-xl-4">
        <div class="card"><div class="card-body stat-card">
            <div class="stat-icon bg-soft-info"><i class="bi bi-cash-coin"></i></div>
            <div><div class="stat-value">{{ $cur }} {{ number_format($todayTotal, 2) }}</div><div class="stat-label">Today</div></div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="filter-bar">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Invoice, member name or code">
            <select name="method" class="form-select">
                <option value="">All methods</option>
                @foreach (\App\Models\Payment::METHODS as $key => $label)
                    <option value="{{ $key }}" @selected(request('method') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control" title="From">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['q', 'method', 'from', 'to']))
                <a href="{{ route('admin.payments.index') }}" class="btn btn-light">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Member</th>
                    <th>Plan</th>
                    <th class="text-end">Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td><a href="{{ route('admin.payments.show', $payment) }}" class="fw-semibold text-decoration-none">{{ $payment->invoice_no }}</a></td>
                    <td>
                        @if ($payment->member)
                            <a href="{{ route('admin.members.show', $payment->member) }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                                <span class="avatar avatar-sm">{{ $payment->member->initials() }}</span>
                                <span>
                                    <span class="d-block fw-semibold">{{ $payment->member->name }}</span>
                                    <small class="text-muted">{{ $payment->member->member_code }}</small>
                                </span>
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $payment->subscription?->plan?->name ?? '—' }}</td>
                    <td class="text-end fw-semibold">{{ $cur }} {{ number_format($payment->amount, 2) }}</td>
                    <td><span class="badge badge-soft-info">{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</span></td>
                    <td class="text-nowrap">{{ $payment->paid_at?->format('d M Y') }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-secondary" title="View invoice"><i class="bi bi-receipt"></i></a>
                        <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($payments->hasPages())
        <div class="card-footer bg-white">{{ $payments->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
