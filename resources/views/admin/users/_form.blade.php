@php $isSelf = $user->exists && $user->id === auth()->id(); @endphp
<div class="card">
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
            <div class="col-md-6">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" @disabled($isSelf)>
                    @foreach (['staff' => 'Staff', 'admin' => 'Admin'] as $k => $label)
                        <option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $label }}</option>
                    @endforeach
                </select>
                @if ($isSelf)<input type="hidden" name="role" value="{{ $user->role }}"><div class="form-text">You cannot change your own role.</div>@endif
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Password @unless ($user->exists)<span class="text-danger">*</span>@endunless</label>
                <input type="password" name="password" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror" @required(! $user->exists)>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if ($user->exists)<div class="form-text">Leave blank to keep the current password.</div>@endif
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password" class="form-control">
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active)) @disabled($isSelf)>
                    <label class="form-check-label" for="is_active">Active (can log in)</label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-end">
        <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save User</button>
    </div>
</div>
