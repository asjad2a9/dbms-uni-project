@extends('layouts.admin')
@section('title', 'Invoice ' . $payment->invoice_no)
@section('actions')
    <a href="{{ route('admin.payments.index') }}" class="btn btn-light no-print"><i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Back</span></a>
    <button type="button" onclick="window.print()" class="btn btn-primary no-print"><i class="bi bi-printer"></i> <span class="d-none d-sm-inline">Print</span></button>
@endsection

@php
    $cur = config('gym.currency');
    $sub = $payment->subscription;
@endphp

@push('styles')
<style>
    .invoice { max-width: 820px; margin: 0 auto; }
    .invoice-total { background: #fff7ed; border-radius: 12px; padding: 1rem 1.25rem; }
    @media print {
        .sidebar, .topbar, .footer, .alert, .no-print { display: none !important; }
        .main { margin-left: 0 !important; }
        .content { padding: 0 !important; }
        .invoice { box-shadow: none !important; border: 0 !important; max-width: 100%; }
        body { background: #fff !important; }
    }
</style>
@endpush

@section('content')
<div class="card invoice">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1"><i class="bi bi-lightning-charge-fill text-brand"></i> {{ config('app.name') }}</h2>
                @if (config('gym.address'))<div class="text-muted small">{{ config('gym.address') }}</div>@endif
                @if (config('gym.phone'))<div class="text-muted small"><i class="bi bi-telephone"></i> {{ config('gym.phone') }}</div>@endif
            </div>
            <div class="text-sm-end">
                <div class="text-uppercase text-muted small fw-semibold">Payment Receipt</div>
                <div class="h5 fw-bold mb-1">{{ $payment->invoice_no }}</div>
                <div class="small text-muted">Date: {{ $payment->paid_at?->format('d M Y') }}</div>
            </div>
        </div>

        <hr>

        <div class="row g-4 my-1">
            <div class="col-sm-6">
                <div class="text-uppercase text-muted small fw-semibold mb-1">Billed To</div>
                @if ($payment->member)
                    <div class="fw-semibold">{{ $payment->member->name }}</div>
                    <div class="small text-muted">{{ $payment->member->member_code }}</div>
                    @if ($payment->member->phone)<div class="small">{{ $payment->member->phone }}</div>@endif
                    @if ($payment->member->email)<div class="small">{{ $payment->member->email }}</div>@endif
                @else
                    <div class="text-muted">Member removed</div>
                @endif
            </div>
            <div class="col-sm-6 text-sm-end">
                <div class="text-uppercase text-muted small fw-semibold mb-1">Payment Info</div>
                <div>Method: <span class="fw-semibold">{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</span></div>
                @if ($payment->reference)<div>Reference: <span class="fw-semibold">{{ $payment->reference }}</span></div>@endif
                <div>Received by: <span class="fw-semibold">{{ $payment->receiver?->name ?? '—' }}</span></div>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table align-middle">
                <thead>
                    <tr><th>Description</th><th>Period</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $sub?->plan?->name ? $sub->plan->name . ' Membership' : 'Gym Payment' }}</div>
                            @if ($payment->notes)<small class="text-muted">{{ $payment->notes }}</small>@endif
                        </td>
                        <td class="text-nowrap">
                            @if ($sub)
                                {{ $sub->start_date?->format('d M Y') }} – {{ $sub->end_date?->format('d M Y') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end fw-semibold">{{ $cur }} {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end mt-3">
            <div class="col-md-6">
                <div class="invoice-total">
                    @if ($sub)
                        <div class="d-flex justify-content-between small mb-1"><span>Plan amount</span><span>{{ $cur }} {{ number_format($sub->netAmount(), 2) }}</span></div>
                        <div class="d-flex justify-content-between small mb-2"><span>Balance due</span><span>{{ $cur }} {{ number_format($sub->dueAmount(), 2) }}</span></div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold fs-5"><span>Amount Paid</span><span class="text-brand">{{ $cur }} {{ number_format($payment->amount, 2) }}</span></div>
                </div>
            </div>
        </div>

        <p class="text-center text-muted small mt-5 mb-0">Thank you for training with {{ config('app.name') }}! This is a computer-generated receipt.</p>
    </div>
</div>
@endsection
