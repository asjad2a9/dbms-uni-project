@extends('layouts.admin')
@section('title', 'New Subscription')
@section('subtitle', 'Assign or renew a membership plan')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Back</span></a>
@endsection

@php $cur = config('gym.currency'); @endphp

@section('content')
<form method="POST" action="{{ route('admin.subscriptions.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-card-checklist me-1"></i> Subscription Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Member <span class="text-danger">*</span></label>
                            <select name="member_id" class="form-select @error('member_id') is-invalid @enderror" required>
                                <option value="">— Select member —</option>
                                @foreach ($members as $m)
                                    <option value="{{ $m->id }}" @selected((string) old('member_id', $selectedMember) === (string) $m->id)>{{ $m->name }} ({{ $m->member_code }})</option>
                                @endforeach
                            </select>
                            @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plan <span class="text-danger">*</span></label>
                            <select name="membership_plan_id" id="planSelect" class="form-select @error('membership_plan_id') is-invalid @enderror" required>
                                <option value="">— Select plan —</option>
                                @foreach ($plans as $p)
                                    <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-days="{{ $p->duration_days }}" @selected((string) old('membership_plan_id') === (string) $p->id)>
                                        {{ $p->name }} · {{ $p->durationLabel() }} · {{ $cur }} {{ number_format($p->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('membership_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="startDate" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror" required>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount ({{ $cur }})</label>
                            <input type="number" step="0.01" min="0" name="discount" id="discount" value="{{ old('discount', 0) }}" class="form-control @error('discount') is-invalid @enderror">
                            @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12"><hr class="my-1"><div class="fw-semibold">Initial Payment <small class="text-muted fw-normal">(optional)</small></div></div>
                        <div class="col-md-6">
                            <label class="form-label">Amount Received ({{ $cur }})</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" name="payment_amount" id="paymentAmount" value="{{ old('payment_amount') }}" class="form-control @error('payment_amount') is-invalid @enderror" placeholder="0.00">
                                <button type="button" class="btn btn-outline-secondary" id="payFull">Full</button>
                                @error('payment_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
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
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-calculator me-1"></i> Summary</div>
                <div class="card-body">
                    <dl class="detail-list mb-0">
                        <dt>Plan Price</dt><dd id="sumPrice">—</dd>
                        <dt>Discount</dt><dd id="sumDiscount">—</dd>
                        <dt>Net Total</dt><dd class="fs-5 text-brand" id="sumNet">—</dd>
                        <dt>Valid Until</dt><dd id="sumEnd">—</dd>
                        <dt>Balance Due</dt><dd class="mb-0" id="sumDue">—</dd>
                    </dl>
                </div>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button class="btn btn-primary"><i class="bi bi-check2"></i> Create Subscription</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        const cur = @json($cur);
        const plan = document.getElementById('planSelect'), start = document.getElementById('startDate'),
              disc = document.getElementById('discount'), pay = document.getElementById('paymentAmount');
        const $ = id => document.getElementById(id);
        const fmt = n => cur + ' ' + n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        let net = 0;

        function update() {
            const opt = plan.selectedOptions[0];
            if (!opt || !opt.value) {
                ['sumPrice', 'sumDiscount', 'sumNet', 'sumEnd', 'sumDue'].forEach(id => $(id).textContent = '—');
                net = 0; return;
            }
            const price = parseFloat(opt.dataset.price) || 0, days = parseInt(opt.dataset.days) || 0;
            const d = Math.min(parseFloat(disc.value) || 0, price);
            net = price - d;
            $('sumPrice').textContent = fmt(price);
            $('sumDiscount').textContent = fmt(d);
            $('sumNet').textContent = fmt(net);
            if (start.value) {
                const end = new Date(start.value + 'T00:00:00');
                end.setDate(end.getDate() + days);
                $('sumEnd').textContent = end.toLocaleDateString(undefined, {day: '2-digit', month: 'short', year: 'numeric'});
            } else {
                $('sumEnd').textContent = '—';
            }
            const due = Math.max(0, net - (parseFloat(pay.value) || 0));
            $('sumDue').innerHTML = due > 0 ? '<span class="text-danger">' + fmt(due) + '</span>' : '<span class="text-success">Fully paid</span>';
        }

        [plan, start, disc, pay].forEach(el => { el.addEventListener('input', update); el.addEventListener('change', update); });
        $('payFull').addEventListener('click', () => { pay.value = net.toFixed(2); update(); });
        update();
    })();
</script>
@endpush
