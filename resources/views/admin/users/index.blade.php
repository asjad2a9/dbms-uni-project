@extends('layouts.admin')
@section('title', 'Staff Users')
@section('subtitle', 'People who can log in to the admin panel')
@section('actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Add User</span></a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="filter-bar">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name, email, phone...">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                @foreach (['admin' => 'Admin', 'staff' => 'Staff'] as $k => $label)
                    <option value="{{ $k }}" @selected(request('role') === $k)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            @if (request()->hasAny(['q', 'role']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-light">Reset</a>
            @endif
        </form>
    </div>
    @if ($users->isEmpty())
        <div class="empty-state"><i class="bi bi-inbox"></i>No records found</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>User</th><th>Phone</th><th>Role</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm">{{ mb_substr($user->name, 0, 1) }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }} @if ($user->id === auth()->id())<span class="badge badge-soft-primary ms-1">You</span>@endif</div>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td><span class="badge {{ $user->role === 'admin' ? 'badge-soft-danger' : 'badge-soft-info' }} text-capitalize">{{ $user->role }}</span></td>
                            <td>@include('partials.status-badge', ['status' => $user->is_active ? 'active' : 'inactive'])</td>
                            <td class="text-nowrap">{{ $user->created_at?->format('d M Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if ($users->hasPages())
        <div class="card-footer bg-white">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
