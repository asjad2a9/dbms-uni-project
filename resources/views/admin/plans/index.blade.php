@extends('layouts.admin')
@section('title', 'Membership Plans')
@section('subtitle', 'Pricing packages offered to members')
@section('actions')
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">New Plan</span></a>
@endsection

@section('content')
@if ($plans->isEmpty())
    <div class="card"><div class="card-body"><div class="empty-state"><i class="bi bi-inbox"></i>No records found</div></div></div>
@else
    <div class="row g-3">
        @foreach ($plans as $plan)
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card h-100 {{ $plan->is_active ? '' : 'opacity-75' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-0">{{ $plan->name }}</h5>
                            <span class="badge {{ $plan->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="fs-3 fw-bold text-brand">{{ config('gym.currency') }} {{ number_format($plan->price, 2) }}</span>
                            <span class="text-muted">/ {{ $plan->durationLabel() }}</span>
                        </div>
                        @if ($plan->description)
                            <p class="text-muted small mb-3">{{ $plan->description }}</p>
                        @endif
                        @if (count($plan->featureList()))
                            <ul class="list-unstyled small mb-3">
                                @foreach ($plan->featureList() as $feature)
                                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="mt-auto d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
                            <span class="small text-muted"><i class="bi bi-people me-1"></i>{{ $plan->active_count }} active subscriber{{ $plan->active_count == 1 ? '' : 's' }}</span>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="d-inline" data-confirm="Delete this record?">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
