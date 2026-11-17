@extends('layouts.admin')
@section('title', 'Attendance')
@section('subtitle', \Illuminate\Support\Carbon::parse($date)->format('l, d M Y'))

@section('content')
<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="card h-100"><div class="card-body stat-card">
            <div class="stat-icon bg-soft-primary"><i class="bi bi-box-arrow-in-right"></i></div>
            <div><div class="stat-value">{{ $todayCount }}</div><div class="stat-label">Today's Check-ins</div></div>
        </div></div>
    </div>
    <div class="col-sm-4">
        <div class="card h-100"><div class="card-body stat-card">
            <div class="stat-icon bg-soft-success"><i class="bi bi-person-walking"></i></div>
            <div><div class="stat-value">{{ $insideCount }}</div><div class="stat-label">Currently Inside</div></div>
        </div></div>
    </div>
    <div class="col-sm-4">
        <div class="card h-100"><div class="card-body stat-card">
            <div class="stat-icon bg-soft-purple"><i class="bi bi-calendar3"></i></div>
            <div><div class="stat-value">{{ $monthCount }}</div><div class="stat-label">Visits This Month</div></div>
        </div></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-lightning-charge text-brand"></i> Quick Check-in</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.attendance.checkin') }}" class="row g-2 align-items-center">
            @csrf
            <div class="col-md">
                <select name="member_id" class="form-select @error('member_id') is-invalid @enderror" required>
                    <option value="">— Select active member —</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->id }}" @selected((string) old('member_id') === (string) $m->id)>{{ $m->name }} ({{ $m->member_code }})</option>
                    @endforeach
                </select>
                @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-auto d-grid">
                <button class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Check In</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="filter-bar">
            <input type="date" name="date" value="{{ $date }}" class="form-control">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search member name, code or phone">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['q', 'date']))
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-light">Today</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Duration</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($attendances as $a)
                @php
                    $in = \Illuminate\Support\Carbon::parse($a->check_in);
                    $out = $a->check_out ? \Illuminate\Support\Carbon::parse($a->check_out) : null;
                    $mins = $out ? (int) abs($in->diffInMinutes($out)) : null;
                @endphp
                <tr>
                    <td>
                        @if ($a->member)
                            <a href="{{ route('admin.members.show', $a->member) }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                                <span class="avatar avatar-sm">{{ $a->member->initials() }}</span>
                                <span>
                                    <span class="d-block fw-semibold">{{ $a->member->name }}</span>
                                    <small class="text-muted">{{ $a->member->member_code }}</small>
                                </span>
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-nowrap">{{ $in->format('h:i A') }}</td>
                    <td class="text-nowrap">
                        @if ($out)
                            {{ $out->format('h:i A') }}
                        @else
                            <span class="badge badge-soft-success">Inside</span>
                        @endif
                    </td>
                    <td class="text-nowrap">{{ $mins !== null ? intdiv($mins, 60) . 'h ' . ($mins % 60) . 'm' : '—' }}</td>
                    <td class="text-end text-nowrap">
                        @unless ($a->check_out)
                            <form method="POST" action="{{ route('admin.attendance.checkout', $a) }}" class="d-inline">@csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Check-out</span></button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('admin.attendance.destroy', $a) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($attendances->hasPages())
        <div class="card-footer bg-white">{{ $attendances->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
