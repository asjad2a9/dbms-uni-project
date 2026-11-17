@extends('layouts.admin')
@section('title', 'Expenses')
@section('subtitle', 'Track gym running costs')
@section('actions')
    <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Add Expense</span></a>
@endsection

@php $cur = config('gym.currency'); @endphp

@section('content')
<div class="row g-3 mb-3">
    <div class="col-sm-6">
        <div class="card"><div class="card-body stat-card">
            <div class="stat-icon bg-soft-danger"><i class="bi bi-funnel"></i></div>
            <div><div class="stat-value">{{ $cur }} {{ number_format($filteredTotal, 2) }}</div><div class="stat-label">Filtered Total</div></div>
        </div></div>
    </div>
    <div class="col-sm-6">
        <div class="card"><div class="card-body stat-card">
            <div class="stat-icon bg-soft-warning"><i class="bi bi-calendar-month"></i></div>
            <div><div class="stat-value">{{ $cur }} {{ number_format($monthTotal, 2) }}</div><div class="stat-label">This Month</div></div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="filter-bar">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search title or notes">
            <select name="category" class="form-select">
                <option value="">All categories</option>
                @foreach (\App\Models\Expense::CATEGORIES as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control" title="From">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control" title="To">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['q', 'category', 'from', 'to']))
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-light">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th class="text-end">Amount</th>
                    <th>Date</th>
                    <th>Notes</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($expenses as $expense)
                <tr>
                    <td class="fw-semibold">{{ $expense->title }}</td>
                    <td><span class="badge badge-soft-secondary text-capitalize">{{ $expense->category }}</span></td>
                    <td class="text-end fw-semibold">{{ $cur }} {{ number_format($expense->amount, 2) }}</td>
                    <td class="text-nowrap">{{ $expense->spent_on?->format('d M Y') }}</td>
                    <td class="text-muted small" title="{{ $expense->notes }}">{{ \Illuminate\Support\Str::limit($expense->notes, 40) ?: '—' }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($expenses->hasPages())
        <div class="card-footer bg-white">{{ $expenses->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
