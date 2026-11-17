<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }
        $from = "{$year}-01-01";
        $to = "{$year}-12-31";

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = date('M', mktime(0, 0, 0, $m, 1));
        }
        $blank = array_fill(1, 12, 0);

        $payments = Payment::whereBetween('paid_at', [$from, $to])->get(['amount', 'method', 'paid_at']);
        $expenses = Expense::whereBetween('spent_on', [$from, $to])->get(['amount', 'category', 'spent_on']);
        $members = Member::whereBetween('joined_at', [$from, $to])->get(['id', 'joined_at']);
        $visits = Attendance::whereBetween('date', [$from, $to])->get(['id', 'date']);

        $income = $blank;
        foreach ($payments->groupBy(fn ($p) => (int) $p->paid_at->format('n')) as $m => $rows) {
            $income[$m] = (float) $rows->sum('amount');
        }
        $expense = $blank;
        foreach ($expenses->groupBy(fn ($e) => (int) $e->spent_on->format('n')) as $m => $rows) {
            $expense[$m] = (float) $rows->sum('amount');
        }
        $newMembers = $blank;
        foreach ($members->groupBy(fn ($r) => (int) $r->joined_at->format('n')) as $m => $rows) {
            $newMembers[$m] = $rows->count();
        }
        $attendance = $blank;
        foreach ($visits->groupBy(fn ($r) => (int) $r->date->format('n')) as $m => $rows) {
            $attendance[$m] = $rows->count();
        }

        $totals = [
            'income' => array_sum($income),
            'expenses' => array_sum($expense),
            'profit' => array_sum($income) - array_sum($expense),
            'members' => array_sum($newMembers),
            'visits' => array_sum($attendance),
        ];

        $incomeByMethod = [];
        foreach (Payment::METHODS as $key => $label) {
            $incomeByMethod[$label] = (float) $payments->where('method', $key)->sum('amount');
        }

        $expensesByCategory = $expenses->groupBy('category')
            ->map(fn ($rows) => (float) $rows->sum('amount'))
            ->sortDesc();

        $topPlans = MembershipPlan::withCount(['subscriptions as year_count' => fn ($q) => $q->whereBetween('start_date', [$from, $to])])
            ->withSum(['subscriptions as year_revenue' => fn ($q) => $q->whereBetween('start_date', [$from, $to])], 'amount')
            ->orderByDesc('year_count')
            ->take(5)
            ->get();

        $membersByStatus = Member::query()->get(['status'])->countBy('status');

        $years = range(now()->year, now()->year - 5);
        if (! in_array($year, $years)) {
            $years[] = $year;
            rsort($years);
        }

        return view('admin.reports.index', compact(
            'year', 'years', 'months', 'income', 'expense', 'newMembers', 'attendance', 'totals',
            'incomeByMethod', 'expensesByCategory', 'topPlans', 'membersByStatus'
        ));
    }
}
