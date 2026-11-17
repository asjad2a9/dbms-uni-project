@extends('layouts.admin')
@section('title', 'Subscriptions')
@section('subtitle', 'Member plans, renewals and dues')
@section('actions')
    <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">New Subscription</span></a>
@endsection

@php $cur = config('gym.currency'); @endphp

@section('content')
<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <a href="{{ route('admin.subscriptions.index', ['status' => 'active']) }}" class="card card-body stat-card text-decoration-none text-reset h-100">
            <div class="stat-icon bg-soft-success"><i class="bi bi-patch-check"></i></div>
            <div><div class="stat-value">{{ $stats['active'] }}</div><div class="stat-label">Active</div></div>
        </a>
    </div>
    <div class="col-sm-4">
        <a href="{{ route('admin.subscriptions.index', ['expiring' => 1]) }}" class="card card-body stat-card text-decoration-none text-reset h-100">
            <div class="stat-icon bg-soft-warning"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="stat-value">{{ $stats['expiring'] }}</div><div class="stat-label">Expiring in 7 days</div></div>
        </a>
    </div>
    <div class="col-sm-4">
        <a href="{{ route('admin.subscriptions.index', ['status' => 'expired']) }}" class="card card-body stat-card text-decoration-none text-reset h-100">
            <div class="stat-icon bg-soft-danger"><i class="bi bi-calendar-x"></i></div>
            <div><div class="stat-value">{{ $stats['expired'] }}</div><div class="stat-label">Expired</div></div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="filter-bar">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search member name or code...">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (['active', 'expired', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="plan" class="form-select">
                <option value="">All plans</option>
                @foreach ($plans as $p)
                    <option value="{{ $p->id }}" @selected((string) request('plan') === (string) $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="expiring" class="form-select">
                <option value="">Any expiry</option>
                <option value="1" @selected(request('expiring') == '1')>Expiring in 7 days</option>
            </select>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['q', 'status', 'plan', 'expiring']))
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-light">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Member</th>
                <th>Plan</th>
                <th>Start</th>
                <th>End</th>
                <th>Days Left</th>
                <th>Net Amount</th>
                <th>Paid / Due</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($subscriptions as $s)
                @php
                    $net = $s->netAmount();
                    $paid = (float) ($s->payments_sum_amount ?? 0);
                    $due = max(0, $net - $paid);
                    $left = $s->status === 'active' ? $s->daysLeft() : null;
                @endphp
                <tr>
                    <td>
                        @if ($s->member)
                            <a href="{{ route('admin.members.show', $s->member) }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                                <span class="avatar avatar-sm">{{ $s->member->initials() }}</span>
                                <span><span class="fw-semibold d-block">{{ $s->member->name }}</span><small class="text-muted">{{ $s->member->member_code }}</small></span>
                            </a>
                        @else — @endif
                    </td>
                    <td>{{ $s->plan?->name ?? '—' }}</td>
                    <td class="text-nowrap">{{ $s->start_date->format('d M Y') }}</td>
                    <td class="text-nowrap">{{ $s->end_date->format('d M Y') }}</td>
                    <td>
                        @if ($left === null)
                            <span class="text-muted">—</span>
                        @else
                            <span class="badge {{ $left <= 7 ? 'badge-soft-warning' : 'badge-soft-success' }}">{{ $left }} days</span>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        {{ $cur }} {{ number_format($net, 2) }}
                        @if ((float) $s->discount > 0)<div class="small text-muted">-{{ number_format($s->discount, 2) }} disc.</div>@endif
                    </td>
                    <td class="text-nowrap">
                        <div class="text-success">{{ number_format($paid, 2) }}</div>
                        @if ($due > 0)<div class="small text-danger">Due {{ number_format($due, 2) }}</div>@else<div class="small text-muted">Settled</div>@endif
                    </td>
                    <td>@include('partials.status-badge', ['status' => $s->status])</td>
                    <td class="text-end text-nowrap">
                        @if ($s->status === 'active')
                            <form method="POST" action="{{ route('admin.subscriptions.cancel', $s) }}" class="d-inline" data-confirm="Cancel this subscription?">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning" title="Cancel"><i class="bi bi-x-circle"></i></button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.subscriptions.destroy', $s) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($subscriptions->hasPages())
        <div class="card-footer bg-white">{{ $subscriptions->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
