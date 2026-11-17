@extends('layouts.admin')
@section('title', 'Record Payment')
@section('actions')
    <a href="{{ route('admin.payments.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
@endsection

@php
    $cur = config('gym.currency');
    $memberId = old('member_id', $selectedMember);
@endphp

@section('content')
<div class="card">
    <div class="card-header">Payment Details</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.payments.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Member <span class="text-danger">*</span></label>
                    <select name="member_id" id="member_id" class="form-select @error('member_id') is-invalid @enderror" required>
                        <option value="">— Select member —</option>
                        @foreach ($members as $m)
                            <option value="{{ $m->id }}" @selected((string) $memberId === (string) $m->id)>{{ $m->name }} ({{ $m->member_code }})</option>
                        @endforeach
                    </select>
                    @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Subscription</label>
                    <select name="subscription_id" id="subscription_id" class="form-select @error('subscription_id') is-invalid @enderror">
                        <option value="">— None / general payment —</option>
                        @foreach ($subscriptions as $s)
                            @php $due = max(0, $s->netAmount() - (float) $s->paid_sum); @endphp
                            <option value="{{ $s->id }}" data-member="{{ $s->member_id }}" data-due="{{ number_format($due, 2, '.', '') }}"
                                @selected((string) old('subscription_id') === (string) $s->id)>
                                {{ $s->member?->name }} – {{ $s->plan?->name }} (due {{ $cur }} {{ number_format($due, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text" id="subHint">Only the selected member's active subscriptions are shown.</div>
                    @error('subscription_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amount ({{ $cur }}) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Method <span class="text-danger">*</span></label>
                    <select name="method" class="form-select @error('method') is-invalid @enderror" required>
                        @foreach (\App\Models\Payment::METHODS as $key => $label)
                            <option value="{{ $key }}" @selected(old('method', 'cash') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Paid On <span class="text-danger">*</span></label>
                    <input type="date" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" class="form-control @error('paid_at') is-invalid @enderror" required>
                    @error('paid_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" class="form-control @error('reference') is-invalid @enderror" placeholder="Txn ID, cheque no, etc.">
                    @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-4 d-flex flex-wrap gap-2">
                <button class="btn btn-primary"><i class="bi bi-check2"></i> Save Payment</button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const member = document.getElementById('member_id');
        const sub = document.getElementById('subscription_id');
        const amount = document.getElementById('amount');

        function filterSubs() {
            const mid = member.value;
            let firstMatch = null;
            Array.from(sub.options).forEach(o => {
                if (!o.value) return;
                const show = !mid || o.dataset.member === mid;
                o.hidden = !show;
                o.disabled = !show;
                if (show && !firstMatch) firstMatch = o;
            });
            const current = sub.selectedOptions[0];
            if (current && current.value && current.disabled) sub.value = '';
            if (mid && !sub.value && firstMatch) { sub.value = firstMatch.value; fillAmount(); }
        }
        function fillAmount() {
            const o = sub.selectedOptions[0];
            if (o && o.dataset.due && !amount.value && parseFloat(o.dataset.due) > 0) amount.value = o.dataset.due;
        }
        member.addEventListener('change', () => { amount.value = ''; sub.value = ''; filterSubs(); });
        sub.addEventListener('change', () => { amount.value = ''; fillAmount(); });
        filterSubs();
    })();
</script>
@endpush
