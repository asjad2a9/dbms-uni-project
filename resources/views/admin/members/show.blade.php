@extends('layouts.admin')
@section('title', $member->name)
@section('subtitle', 'Member profile · ' . $member->member_code)
@section('actions')
    <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Edit</span></a>
@endsection

@php
    $cur = config('gym.currency');
    $sub = $member->activeSubscription;
    $bmi = $member->bmi();
    $bmiLabel = $bmi === null ? null : ($bmi < 18.5 ? ['Underweight', 'info'] : ($bmi < 25 ? ['Normal', 'success'] : ($bmi < 30 ? ['Overweight', 'warning'] : ['Obese', 'danger'])));
@endphp

@section('content')
<div class="row g-3">
    {{-- Left column: profile --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                <span class="avatar avatar-lg mb-2">{{ $member->initials() }}</span>
                <h2 class="h5 mb-0">{{ $member->name }}</h2>
                <div class="text-muted small mb-2">{{ $member->member_code }}</div>
                @include('partials.status-badge', ['status' => $member->status])

                <div class="d-grid gap-2 mt-3 no-print">
                    <a href="{{ route('admin.subscriptions.create', ['member_id' => $member->id]) }}" class="btn btn-primary"><i class="bi bi-card-checklist"></i> New Subscription</a>
                    <a href="{{ route('admin.payments.create', ['member_id' => $member->id]) }}" class="btn btn-outline-primary"><i class="bi bi-cash-coin"></i> Record Payment</a>
                    <form method="POST" action="{{ route('admin.attendance.checkin') }}" class="d-grid">
                        @csrf
                        <input type="hidden" name="member_id" value="{{ $member->id }}">
                        <button class="btn btn-outline-success"><i class="bi bi-box-arrow-in-right"></i> Check In Now</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i> Details</div>
            <div class="card-body">
                <dl class="detail-list mb-0">
                    <dt>Phone</dt><dd>{{ $member->phone }}</dd>
                    <dt>Email</dt><dd>{{ $member->email ?: '—' }}</dd>
                    <dt>Gender</dt><dd class="text-capitalize">{{ $member->gender }}</dd>
                    <dt>Date of Birth</dt><dd>{{ $member->dob ? $member->dob->format('d M Y') . ' (' . $member->dob->age . ' yrs)' : '—' }}</dd>
                    <dt>Address</dt><dd>{{ $member->address ?: '—' }}</dd>
                    <dt>Emergency Contact</dt><dd>{{ $member->emergency_contact ?: '—' }}</dd>
                    <dt>Joined On</dt><dd>{{ $member->joined_at?->format('d M Y') ?? '—' }}</dd>
                    <dt>Trainer</dt>
                    <dd>
                        @if ($member->trainer)
                            <a href="{{ route('admin.trainers.show', $member->trainer) }}">{{ $member->trainer->name }}</a>
                        @else — @endif
                    </dd>
                    <dt>Height / Weight</dt>
                    <dd>{{ $member->height_cm ? $member->height_cm . ' cm' : '—' }} / {{ $member->weight_kg ? $member->weight_kg . ' kg' : '—' }}</dd>
                    <dt>BMI</dt>
                    <dd class="mb-0">
                        @if ($bmi !== null)
                            {{ $bmi }} <span class="badge badge-soft-{{ $bmiLabel[1] }}">{{ $bmiLabel[0] }}</span>
                        @else — @endif
                    </dd>
                    @if ($member->notes)
                        <dt class="mt-3">Notes</dt><dd class="fw-normal">{{ $member->notes }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div class="col-lg-8">
        {{-- Current subscription --}}
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-patch-check me-1"></i> Current Subscription</div>
            <div class="card-body">
                @if ($sub)
                    @php $due = $sub->dueAmount(); $left = $sub->daysLeft(); @endphp
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="stat-label">Plan</div>
                            <div class="fw-bold">{{ $sub->plan?->name }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-label">Valid Until</div>
                            <div class="fw-bold">{{ $sub->end_date->format('d M Y') }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-label">Days Left</div>
                            <div class="stat-value {{ $left <= 7 ? 'text-warning' : 'text-success' }}">{{ $left }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-label">Due Amount</div>
                            <div class="stat-value {{ $due > 0 ? 'text-danger' : 'text-success' }}">{{ $cur }} {{ number_format($due, 2) }}</div>
                        </div>
                    </div>
                @else
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span class="text-muted"><i class="bi bi-exclamation-circle text-danger"></i> No active subscription.</span>
                        <a href="{{ route('admin.subscriptions.create', ['member_id' => $member->id]) }}" class="btn btn-sm btn-primary no-print">Assign a plan</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="card">
            <div class="card-header pb-0 border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs flex-nowrap overflow-auto" role="tablist">
                    <li class="nav-item"><button class="nav-link active text-nowrap" data-bs-toggle="tab" data-bs-target="#tab-subs" type="button">Subscriptions <span class="badge bg-secondary-subtle text-secondary">{{ $subscriptions->count() }}</span></button></li>
                    <li class="nav-item"><button class="nav-link text-nowrap" data-bs-toggle="tab" data-bs-target="#tab-pay" type="button">Payments <span class="badge bg-secondary-subtle text-secondary">{{ $payments->count() }}</span></button></li>
                    <li class="nav-item"><button class="nav-link text-nowrap" data-bs-toggle="tab" data-bs-target="#tab-att" type="button">Attendance</button></li>
                </ul>
            </div>
            <div class="tab-content">
                {{-- Subscriptions --}}
                <div class="tab-pane fade show active" id="tab-subs">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Plan</th><th>Period</th><th>Net</th><th>Paid</th><th>Status</th><th class="text-end"></th></tr></thead>
                            <tbody>
                            @forelse ($subscriptions as $s)
                                @php $paid = (float) ($s->payments_sum_amount ?? 0); $net = $s->netAmount(); @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $s->plan?->name ?? '—' }}</td>
                                    <td class="text-nowrap small">{{ $s->start_date->format('d M Y') }} → {{ $s->end_date->format('d M Y') }}</td>
                                    <td class="text-nowrap">{{ $cur }} {{ number_format($net, 2) }}</td>
                                    <td class="text-nowrap">
                                        {{ $cur }} {{ number_format($paid, 2) }}
                                        @if ($net - $paid > 0)<div class="small text-danger">Due {{ number_format($net - $paid, 2) }}</div>@endif
                                    </td>
                                    <td>@include('partials.status-badge', ['status' => $s->status])</td>
                                    <td class="text-end text-nowrap">
                                        @if ($s->status === 'active')
                                            <form method="POST" action="{{ route('admin.subscriptions.cancel', $s) }}" class="d-inline" data-confirm="Cancel this subscription?">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning" title="Cancel"><i class="bi bi-x-circle"></i></button></form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Payments --}}
                <div class="tab-pane fade" id="tab-pay">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Invoice</th><th>Date</th><th>For</th><th>Method</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                            @forelse ($payments as $p)
                                <tr>
                                    <td><a href="{{ route('admin.payments.show', $p) }}">{{ $p->invoice_no }}</a></td>
                                    <td class="text-nowrap">{{ $p->paid_at->format('d M Y') }}</td>
                                    <td>{{ $p->subscription?->plan?->name ?? 'General' }}</td>
                                    <td>{{ \App\Models\Payment::METHODS[$p->method] ?? $p->method }}</td>
                                    <td class="text-end text-nowrap fw-semibold">{{ $cur }} {{ number_format($p->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
                            @endforelse
                            </tbody>
                            @if ($payments->isNotEmpty())
                                <tfoot><tr><th colspan="4" class="text-end">Total Paid</th><th class="text-end text-nowrap">{{ $cur }} {{ number_format($payments->sum('amount'), 2) }}</th></tr></tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- Attendance --}}
                <div class="tab-pane fade" id="tab-att">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th></tr></thead>
                            <tbody>
                            @forelse ($attendances as $a)
                                @php
                                    $in = \Illuminate\Support\Carbon::parse($a->check_in);
                                    $out = $a->check_out ? \Illuminate\Support\Carbon::parse($a->check_out) : null;
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $a->date->format('D, d M Y') }}</td>
                                    <td>{{ $in->format('h:i A') }}</td>
                                    <td>{!! $out ? e($out->format('h:i A')) : '<span class="badge badge-soft-success">In gym</span>' !!}</td>
                                    <td>{{ $out ? $in->diff($out)->format('%hh %Im') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white small text-muted">Showing last 15 visits.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
