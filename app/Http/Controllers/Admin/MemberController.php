<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::with(['trainer', 'activeSubscription.plan'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->q . '%';
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', $q)
                        ->orWhere('phone', 'like', $q)
                        ->orWhere('email', 'like', $q)
                        ->orWhere('member_code', 'like', $q);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('trainer_id'), fn ($query) => $query->where('trainer_id', $request->trainer_id))
            ->latest()
            ->paginate(15);

        $trainers = Trainer::orderBy('name')->get();

        return view('admin.members.index', compact('members', 'trainers'));
    }

    public function create()
    {
        return view('admin.members.create', [
            'member' => new Member(['status' => 'active', 'gender' => 'male', 'joined_at' => now()]),
            'trainers' => Trainer::where('status', 'active')->orderBy('name')->get(),
            'plans' => MembershipPlan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateMember($request);
        $extra = $request->validate([
            'membership_plan_id' => ['nullable', 'exists:membership_plans,id'],
            'start_date' => ['nullable', 'required_with:membership_plan_id', 'date'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::in(array_keys(Payment::METHODS))],
        ]);
        $data['joined_at'] = $data['joined_at'] ?? now()->toDateString();

        $member = DB::transaction(function () use ($data, $extra) {
            $member = Member::create($data);

            if (! empty($extra['membership_plan_id'])) {
                $plan = MembershipPlan::findOrFail($extra['membership_plan_id']);
                $start = Carbon::parse($extra['start_date'] ?? now());
                $sub = Subscription::create([
                    'member_id' => $member->id,
                    'membership_plan_id' => $plan->id,
                    'start_date' => $start->toDateString(),
                    'end_date' => $start->copy()->addDays($plan->duration_days)->toDateString(),
                    'amount' => $plan->price,
                    'discount' => 0,
                    'status' => 'active',
                ]);

                if (($extra['payment_amount'] ?? 0) > 0) {
                    Payment::create([
                        'member_id' => $member->id,
                        'subscription_id' => $sub->id,
                        'amount' => $extra['payment_amount'],
                        'method' => $extra['payment_method'] ?? 'cash',
                        'paid_at' => now()->toDateString(),
                        'received_by' => auth()->id(),
                    ]);
                }
            }

            return $member;
        });

        return redirect()->route('admin.members.show', $member)->with('success', 'Member created successfully.');
    }

    public function show(Member $member)
    {
        $member->load(['trainer', 'activeSubscription.plan']);
        $subscriptions = $member->subscriptions()->with('plan')->withSum('payments', 'amount')->get();
        $payments = $member->payments()->with('subscription.plan')->get();
        $attendances = $member->attendances()->latest('check_in')->take(15)->get();

        return view('admin.members.show', compact('member', 'subscriptions', 'payments', 'attendances'));
    }

    public function edit(Member $member)
    {
        return view('admin.members.edit', [
            'member' => $member,
            'trainers' => Trainer::where('status', 'active')->orWhere('id', $member->trainer_id)->orderBy('name')->get(),
            'plans' => MembershipPlan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $data = $this->validateMember($request, $member);
        $data['joined_at'] = $data['joined_at'] ?? $member->joined_at ?? now()->toDateString();
        $member->update($data);

        return redirect()->route('admin.members.show', $member)->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('admin.members.index')->with('success', 'Member deleted.');
    }

    private function validateMember(Request $request, ?Member $member = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('members', 'email')->ignore($member?->id)],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'dob' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:255'],
            'emergency_contact' => ['nullable', 'string', 'max:60'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'trainer_id' => ['nullable', 'exists:trainers,id'],
            'joined_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
