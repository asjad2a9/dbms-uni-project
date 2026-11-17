@extends('layouts.admin')
@section('title', 'Trainers')
@section('actions')
    <a href="{{ route('admin.trainers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Add Trainer</span></a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="filter-bar">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, email, phone, specialization...">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (['active', 'inactive'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.trainers.index') }}" class="btn btn-light">Reset</a>
            @endif
        </form>
    </div>
    @if ($trainers->isEmpty())
        <div class="empty-state"><i class="bi bi-inbox"></i>No records found</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Trainer</th>
                        <th>Phone</th>
                        <th>Specialization</th>
                        <th class="text-center">Experience</th>
                        <th class="text-center">Members</th>
                        <th class="text-end">Salary</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trainers as $trainer)
                        <tr>
                            <td>
                                <a href="{{ route('admin.trainers.show', $trainer) }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                                    <span class="avatar avatar-sm">{{ mb_substr($trainer->name, 0, 1) }}</span>
                                    <span>
                                        <span class="fw-semibold d-block">{{ $trainer->name }}</span>
                                        <small class="text-muted">{{ $trainer->email ?? '—' }}</small>
                                    </span>
                                </a>
                            </td>
                            <td>{{ $trainer->phone ?? '—' }}</td>
                            <td>{{ $trainer->specialization ?? '—' }}</td>
                            <td class="text-center">{{ $trainer->experience_years }} yrs</td>
                            <td class="text-center"><span class="badge badge-soft-info">{{ $trainer->members_count }}</span></td>
                            <td class="text-end text-nowrap">{{ config('gym.currency') }} {{ number_format($trainer->salary, 2) }}</td>
                            <td>@include('partials.status-badge', ['status' => $trainer->status])</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.trainers.show', $trainer) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.trainers.edit', $trainer) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.trainers.destroy', $trainer) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if ($trainers->hasPages())
        <div class="card-footer bg-white">{{ $trainers->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
