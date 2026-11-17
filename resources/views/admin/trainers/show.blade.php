@extends('layouts.admin')
@section('title', $trainer->name)
@section('subtitle', 'Trainer profile')
@section('actions')
    <a href="{{ route('admin.trainers.edit', $trainer) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Edit</span></a>
    <a href="{{ route('admin.trainers.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Back</span></a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <span class="avatar avatar-lg mb-2">{{ mb_substr($trainer->name, 0, 1) }}</span>
                <h5 class="mb-1">{{ $trainer->name }}</h5>
                <div class="text-muted mb-2">{{ $trainer->specialization ?? 'General Trainer' }}</div>
                @include('partials.status-badge', ['status' => $trainer->status])
            </div>
            <div class="card-body border-top">
                <dl class="detail-list mb-0">
                    <dt>Email</dt><dd>{{ $trainer->email ?? '—' }}</dd>
                    <dt>Phone</dt><dd>{{ $trainer->phone ?? '—' }}</dd>
                    <dt>Gender</dt><dd class="text-capitalize">{{ $trainer->gender }}</dd>
                    <dt>Experience</dt><dd>{{ $trainer->experience_years }} years</dd>
                    <dt>Salary</dt><dd>{{ config('gym.currency') }} {{ number_format($trainer->salary, 2) }}</dd>
                    <dt>Joined On</dt><dd>{{ $trainer->joined_at?->format('d M Y') ?? '—' }}</dd>
                    <dt>Assigned Members</dt><dd>{{ $trainer->members_count }}</dd>
                </dl>
                @if ($trainer->bio)
                    <hr>
                    <p class="small text-muted mb-0">{!! nl2br(e($trainer->bio)) !!}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-people me-1"></i> Assigned Members ({{ $members->count() }})</div>
            @if ($members->isEmpty())
                <div class="empty-state"><i class="bi bi-inbox"></i>No records found</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr><th>Member</th><th>Phone</th><th>Current Plan</th><th>Expires</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($members as $member)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.members.show', $member) }}" class="text-decoration-none fw-semibold">{{ $member->name }}</a>
                                        <div class="small text-muted">{{ $member->member_code }}</div>
                                    </td>
                                    <td>{{ $member->phone }}</td>
                                    <td>{{ $member->activeSubscription?->plan?->name ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $member->activeSubscription?->end_date?->format('d M Y') ?? '—' }}</td>
                                    <td>@include('partials.status-badge', ['status' => $member->status])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
