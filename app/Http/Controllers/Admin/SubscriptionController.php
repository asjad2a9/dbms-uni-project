<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        Subscription::expireOld();

        $today = now()->toDateString();
        $weekAhead = now()->addDays(7)->toDateString();

        $subscriptions = Subscription::with(['member', 'plan'])
            ->withSum('payments', 'amount')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->q . '%';
                $query->whereHas('member', fn ($m) => $m->where('name', 'like', $q)->orWhere('member_code', 'like', $q));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('plan'), fn ($query) => $query->where('membership_plan_id', $request->plan))
            ->when($request->input('expiring') == '1', fn ($query) => $query->where('status', 'active')
                ->whereDate('end_date', '>=', $today)->whereDate('end_date', '<=', $weekAhead))
            ->latest('end_date')
            ->paginate(15);

        $stats = [
            'active' => Subscription::where('status', 'active')->count(),
            'expiring' => Subscription::where('status', 'active')->whereDate('end_date', '>=', $today)->whereDate('end_date', '<=', $weekAhead)->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
        ];

        $plans = MembershipPlan::orderBy('name')->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'stats', 'plans'));
    }

    public function create(Request $request)
    {
        return view('admin.subscriptions.create', [
            'members' => Member::where('status', 'active')->orderBy('name')->get(),
            'plans' => MembershipPlan::where('is_active', true)->orderBy('price')->get(),
            'selectedMember' => $request->input('member_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'membership_plan_id' => ['required', 'exists:membership_plans,id'],
            'start_date' => ['required', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::in(array_keys(Payment::METHODS))],
        ]);

        $plan = MembershipPlan::findOrFail($data['membership_plan_id']);
        $discount = min((float) ($data['discount'] ?? 0), (float) $plan->price);
        $start = Carbon::parse($data['start_date']);

        DB::transaction(function () use ($data, $plan, $discount, $start) {
            $sub = Subscription::create([
                'member_id' => $data['member_id'],
                'membership_plan_id' => $plan->id,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addDays($plan->duration_days)->toDateString(),
                'amount' => $plan->price,
                'discount' => $discount,
                'status' => 'active',
            ]);

            if (($data['payment_amount'] ?? 0) > 0) {
                Payment::create([
                    'member_id' => $data['member_id'],
                    'subscription_id' => $sub->id,
                    'amount' => $data['payment_amount'],
                    'method' => $data['payment_method'] ?? 'cash',
                    'paid_at' => now()->toDateString(),
                    'received_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('admin.members.show', $data['member_id'])->with('success', 'Subscription created successfully.');
    }

    public function cancel(Subscription $subscription)
    {
        $subscription->update(['status' => 'cancelled']);

        return back()->with('success', 'Subscription cancelled.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return back()->with('success', 'Subscription deleted.');
    }
}
