<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    public function index()
    {
        $plans = MembershipPlan::withCount(['subscriptions as active_count' => fn ($q) => $q->where('status', 'active')])
            ->orderByDesc('is_active')
            ->orderBy('price')
            ->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create', ['plan' => new MembershipPlan(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        MembershipPlan::create($this->validated($request));

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(MembershipPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, MembershipPlan $plan)
    {
        $plan->update($this->validated($request));

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(MembershipPlan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'Plan in use, deactivate instead.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1|max:3650',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'features' => 'nullable|string|max:5000',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
