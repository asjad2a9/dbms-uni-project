<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function home()
    {
        return view('site.home', [
            'plans' => MembershipPlan::where('is_active', true)->orderBy('price')->get(),
            'trainers' => Trainer::where('status', 'active')->latest('experience_years')->take(4)->get(),
            'stats' => [
                'members' => Member::where('status', 'active')->count(),
                'trainers' => Trainer::where('status', 'active')->count(),
                'plans' => MembershipPlan::where('is_active', true)->count(),
            ],
        ]);
    }

    public function join(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'membership_plan_id' => ['nullable', 'exists:membership_plans,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        Inquiry::create($data);

        return redirect()->to(route('home') . '#join')->with('success', 'Thanks ' . $data['name'] . '! Our team will contact you shortly.');
    }
}
