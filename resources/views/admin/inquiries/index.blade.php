@extends('layouts.admin')
@section('title', 'Inquiries')
@section('subtitle', $newCount . ' new ' . \Illuminate\Support\Str::plural('inquiry', $newCount))

@section('content')
<div class="card">
    <div class="card-body border-bottom">
        <form method="GET" class="filter-bar">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, email or phone">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach (\App\Models\Inquiry::STATUSES as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.inquiries.index') }}" class="btn btn-light">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Plan</th>
                    <th>Message</th>
                    <th>Received</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($inquiries as $inquiry)
                <tr>
                    <td class="fw-semibold">{{ $inquiry->name }}</td>
                    <td class="small">
                        <div><i class="bi bi-telephone text-muted"></i> {{ $inquiry->phone }}</div>
                        @if ($inquiry->email)<div><i class="bi bi-envelope text-muted"></i> {{ $inquiry->email }}</div>@endif
                    </td>
                    <td>{{ $inquiry->plan?->name ?? '—' }}</td>
                    <td class="small text-muted" style="max-width: 260px;" title="{{ $inquiry->message }}">
                        <span class="d-inline-block text-truncate" style="max-width: 240px;">{{ $inquiry->message ?: '—' }}</span>
                    </td>
                    <td class="text-nowrap small">{{ $inquiry->created_at?->format('d M Y') }}<br><span class="text-muted">{{ $inquiry->created_at?->diffForHumans() }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm" style="min-width: 120px;" onchange="this.form.submit()">
                                @foreach (\App\Models\Inquiry::STATUSES as $st)
                                    <option value="{{ $st }}" @selected($inquiry->status === $st)>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.members.create', ['name' => $inquiry->name, 'phone' => $inquiry->phone, 'email' => $inquiry->email]) }}" class="btn btn-sm btn-outline-success" title="Convert to Member"><i class="bi bi-person-plus"></i> <span class="d-none d-lg-inline">Convert</span></a>
                        <form method="POST" action="{{ route('admin.inquiries.destroy', $inquiry) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($inquiries->hasPages())
        <div class="card-footer bg-white">{{ $inquiries->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
