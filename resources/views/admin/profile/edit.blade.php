@extends('layouts.admin')
@section('title', 'My Profile')
@section('subtitle', 'Update your account details and password')

@section('content')
<form method="POST" action="{{ route('admin.profile.update') }}">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <span class="avatar avatar-lg mb-2">{{ mb_substr($user->name, 0, 1) }}</span>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <div class="text-muted small mb-2">{{ $user->email }}</div>
                    <span class="badge {{ $user->isAdmin() ? 'badge-soft-danger' : 'badge-soft-info' }} text-capitalize">{{ $user->role }}</span>
                    <div class="small text-muted mt-3">Member since {{ $user->created_at?->format('d M Y') }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-person me-1"></i> Account Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><i class="bi bi-key me-1"></i> Change Password <small class="text-muted fw-normal">(optional)</small></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" autocomplete="current-password" class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="w-100 d-none d-md-block m-0"></div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" autocomplete="new-password" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end">
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
