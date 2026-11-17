<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Inquiry;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Subscription;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        Subscription::expireOld();

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $stats = [
            'members' => Member::count(),
            'active_members' => Member::where('status', 'active')->whereHas('activeSubscription')->count(),
            'today_checkins' => Attendance::whereDate('date', $today)->count(),
            'month_income' => (float) Payment::whereDate('paid_at', '>=', $monthStart)->sum('amount'),
            'month_expense' => (float) Expense::whereDate('spent_on', '>=', $monthStart)->sum('amount'),
            'new_inquiries' => Inquiry::where('status', 'new')->count(),
        ];

        // Last 6 months income vs expense (portable: grouped in PHP)
        $from = now()->subMonths(5)->startOfMonth();
        $months = collect(range(0, 5))->map(fn ($i) => $from->copy()->addMonths($i));
        $pay = Payment::whereDate('paid_at', '>=', $from)->get(['amount', 'paid_at'])->groupBy(fn ($p) => $p->paid_at->format('Y-m'));
        $exp = Expense::whereDate('spent_on', '>=', $from)->get(['amount', 'spent_on'])->groupBy(fn ($e) => $e->spent_on->format('Y-m'));
        $chart = [
            'labels' => $months->map(fn (Carbon $m) => $m->format('M Y'))->values(),
            'income' => $months->map(fn ($m) => round((float) ($pay[$m->format('Y-m')] ?? collect())->sum('amount'), 2))->values(),
            'expense' => $months->map(fn ($m) => round((float) ($exp[$m->format('Y-m')] ?? collect())->sum('amount'), 2))->values(),
        ];

        // Attendance last 7 days
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));
        $att = Attendance::whereDate('date', '>=', $days->first()->toDateString())->get(['date'])->groupBy(fn ($a) => $a->date->toDateString());
        $attendanceChart = [
            'labels' => $days->map(fn ($d) => $d->format('D'))->values(),
            'data' => $days->map(fn ($d) => ($att[$d->toDateString()] ?? collect())->count())->values(),
        ];

        $expiring = Subscription::with(['member', 'plan'])
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, now()->addDays(7)->toDateString()])
            ->orderBy('end_date')->take(6)->get();

        $recentPayments = Payment::with('member')->latest('paid_at')->latest('id')->take(6)->get();
        $recentMembers = Member::with('activeSubscription.plan')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'chart', 'attendanceChart', 'expiring', 'recentPayments', 'recentMembers'));
    }
}
