<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query()->with(['member', 'subscription.plan']);

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('invoice_no', 'like', "%{$q}%")
                    ->orWhereHas('member', fn ($m) => $m->where('name', 'like', "%{$q}%")->orWhere('member_code', 'like', "%{$q}%"));
            });
        }
        if ($method = $request->input('method')) {
            $query->where('method', $method);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('paid_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('paid_at', '<=', $to);
        }

        $filteredTotal = (clone $query)->sum('amount');
        $monthTotal = Payment::whereBetween('paid_at', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('amount');
        $todayTotal = Payment::whereDate('paid_at', now()->toDateString())->sum('amount');

        $payments = $query->latest('paid_at')->latest('id')->paginate(15);

        return view('admin.payments.index', compact('payments', 'filteredTotal', 'monthTotal', 'todayTotal'));
    }

    public function create(Request $request)
    {
        $members = Member::orderBy('name')->get(['id', 'name', 'member_code']);

        $subscriptions = Subscription::with(['member', 'plan'])
            ->withSum('payments as paid_sum', 'amount')
            ->where('status', 'active')
            ->orderByDesc('end_date')
            ->get();

        $selectedMember = $request->integer('member_id') ?: null;

        return view('admin.payments.create', compact('members', 'subscriptions', 'selectedMember'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'subscription_id' => [
                'nullable',
                Rule::exists('subscriptions', 'id')->where('member_id', $request->input('member_id')),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['received_by'] = auth()->id();
        $payment = Payment::create($data);

        return redirect()->route('admin.payments.show', $payment)->with('success', "Payment {$payment->invoice_no} recorded.");
    }

    public function show(Payment $payment)
    {
        $payment->load(['member', 'subscription.plan', 'receiver']);

        return view('admin.payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('admin.payments.index')->with('success', 'Payment deleted.');
    }
}
