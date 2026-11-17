<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $trainer->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $trainer->email) }}" class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $trainer->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Gender <span class="text-danger">*</span></label>
                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                    @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $k => $label)
                        <option value="{{ $k }}" @selected(old('gender', $trainer->gender) === $k)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Specialization</label>
                <input type="text" name="specialization" value="{{ old('specialization', $trainer->specialization) }}" class="form-control @error('specialization') is-invalid @enderror" placeholder="e.g. Strength, Yoga, CrossFit">
                @error('specialization')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Experience (years)</label>
                <input type="number" min="0" name="experience_years" value="{{ old('experience_years', $trainer->experience_years ?? 0) }}" class="form-control @error('experience_years') is-invalid @enderror">
                @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Salary ({{ config('gym.currency') }})</label>
                <input type="number" step="0.01" min="0" name="salary" value="{{ old('salary', $trainer->salary ?? 0) }}" class="form-control @error('salary') is-invalid @enderror">
                @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Joined On</label>
                <input type="date" name="joined_at" value="{{ old('joined_at', optional($trainer->joined_at)->format('Y-m-d')) }}" class="form-control @error('joined_at') is-invalid @enderror">
                @error('joined_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $k => $label)
                        <option value="{{ $k }}" @selected(old('status', $trainer->status) === $k)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Bio</label>
                <textarea name="bio" rows="4" class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $trainer->bio) }}</textarea>
                @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-end">
        <a href="{{ $trainer->exists ? route('admin.trainers.show', $trainer) : route('admin.trainers.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Trainer</button>
    </div>
</div>
