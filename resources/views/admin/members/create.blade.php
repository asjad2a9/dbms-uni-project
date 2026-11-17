@extends('layouts.admin')
@section('title', 'Add Member')
@section('subtitle', 'Register a new gym member')
@section('actions')
    <a href="{{ route('admin.members.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Back</span></a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.members.store') }}">
    @csrf
    @include('admin.members._form')

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-card-checklist me-1"></i> Assign Plan Now <small class="text-muted fw-normal">(optional)</small></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Membership Plan</label>
                    <select name="membership_plan_id" id="planSelect" class="form-select @error('membership_plan_id') is-invalid @enderror">
                        <option value="">— Skip for now —</option>
                        @foreach ($plans as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" @selected((string) old('membership_plan_id') === (string) $p->id)>
                                {{ $p->name }} · {{ $p->durationLabel() }} · {{ config('gym.currency') }} {{ number_format($p->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('membership_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror">
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Received ({{ config('gym.currency') }})</label>
                    <input type="number" step="0.01" min="0" name="payment_amount" id="paymentAmount" value="{{ old('payment_amount') }}" class="form-control @error('payment_amount') is-invalid @enderror" placeholder="0.00">
                    @error('payment_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                        @foreach (\App\Models\Payment::METHODS as $k => $label)
                            <option value="{{ $k }}" @selected(old('payment_method', 'cash') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <a href="{{ route('admin.members.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Save Member</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        const plan = document.getElementById('planSelect'), amt = document.getElementById('paymentAmount');
        plan.addEventListener('change', () => {
            const price = plan.selectedOptions[0].dataset.price;
            if (price && !amt.value) amt.value = parseFloat(price).toFixed(2);
        });
    })();
</script>
@endpush
