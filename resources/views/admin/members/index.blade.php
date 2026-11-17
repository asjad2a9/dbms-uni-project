@extends('layouts.admin')
@section('title', 'Members')
@section('subtitle', 'Manage gym members')
@section('actions')
    <a href="{{ route('admin.members.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> <span class="d-none d-sm-inline">Add Member</span></a>
@endsection

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="filter-bar">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, phone, email, code...">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (['active', 'inactive', 'suspended'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="trainer_id" class="form-select">
                <option value="">All trainers</option>
                @foreach ($trainers as $t)
                    <option value="{{ $t->id }}" @selected((string) request('trainer_id') === (string) $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['q', 'status', 'trainer_id']))
                <a href="{{ route('admin.members.index') }}" class="btn btn-light">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Member</th>
                <th>Phone</th>
                <th>Trainer</th>
                <th>Plan / Expiry</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($members as $m)
                @php $sub = $m->activeSubscription; @endphp
                <tr>
                    <td>
                        <a href="{{ route('admin.members.show', $m) }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                            <span class="avatar avatar-sm">{{ $m->initials() }}</span>
                            <span>
                                <span class="fw-semibold d-block">{{ $m->name }}</span>
                                <small class="text-muted">{{ $m->member_code }}</small>
                            </span>
                        </a>
                    </td>
                    <td class="text-nowrap">{{ $m->phone }}</td>
                    <td>{{ $m->trainer?->name ?? '—' }}</td>
                    <td>
                        @if ($sub)
                            <div class="fw-semibold">{{ $sub->plan?->name }}</div>
                            @php $left = $sub->daysLeft(); @endphp
                            <span class="badge {{ $left <= 7 ? 'badge-soft-warning' : 'badge-soft-success' }}">
                                {{ $sub->end_date->format('d M Y') }} · {{ $left }}d left
                            </span>
                        @else
                            <span class="badge badge-soft-danger">No active plan</span>
                        @endif
                    </td>
                    <td>@include('partials.status-badge', ['status' => $m->status])</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.members.show', $m) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.members.edit', $m) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.members.destroy', $m) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($members->hasPages())
        <div class="card-footer bg-white">{{ $members->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
