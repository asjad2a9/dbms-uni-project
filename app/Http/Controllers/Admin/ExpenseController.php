<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query();

        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('notes', 'like', "%{$q}%"));
        }
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('spent_on', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('spent_on', '<=', $to);
        }

        $filteredTotal = (clone $query)->sum('amount');
        $monthTotal = Expense::whereBetween('spent_on', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('amount');

        $expenses = $query->latest('spent_on')->latest('id')->paginate(15);

        return view('admin.expenses.index', compact('expenses', 'filteredTotal', 'monthTotal'));
    }

    public function create()
    {
        return view('admin.expenses.create', ['expense' => new Expense(['spent_on' => now()])]);
    }

    public function store(Request $request)
    {
        Expense::create($this->validated($request));

        return redirect()->route('admin.expenses.index')->with('success', 'Expense added.');
    }

    public function edit(Expense $expense)
    {
        return view('admin.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validated($request));

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(Expense::CATEGORIES)],
            'amount' => ['required', 'numeric', 'min:0'],
            'spent_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
