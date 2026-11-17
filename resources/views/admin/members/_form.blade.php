@php
    $val = fn ($field, $default = null) => old($field, $member?->{$field} ?? request()->query($field, $default));
@endphp
<div class="card mb-3">
    <div class="card-header"><i class="bi bi-person me-1"></i> Personal Details</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ $val('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" name="phone" value="{{ $val('phone') }}" class="form-control @error('phone') is-invalid @enderror" required>
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ $val('email') }}" class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Gender <span class="text-danger">*</span></label>
                <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                    @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $k => $label)
                        <option value="{{ $k }}" @selected($val('gender', 'male') === $k)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" value="{{ old('dob', $member->dob?->format('Y-m-d')) }}" class="form-control @error('dob') is-invalid @enderror">
                @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" name="address" value="{{ $val('address') }}" class="form-control @error('address') is-invalid @enderror">
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Emergency Contact</label>
                <input type="text" name="emergency_contact" value="{{ $val('emergency_contact') }}" class="form-control @error('emergency_contact') is-invalid @enderror">
                @error('emergency_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Height (cm)</label>
                <input type="number" step="0.1" min="0" name="height_cm" value="{{ $val('height_cm') }}" class="form-control @error('height_cm') is-invalid @enderror">
                @error('height_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Weight (kg)</label>
                <input type="number" step="0.1" min="0" name="weight_kg" value="{{ $val('weight_kg') }}" class="form-control @error('weight_kg') is-invalid @enderror">
                @error('weight_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-gear me-1"></i> Membership</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Trainer</label>
                <select name="trainer_id" class="form-select @error('trainer_id') is-invalid @enderror">
                    <option value="">— No trainer —</option>
                    @foreach ($trainers as $t)
                        <option value="{{ $t->id }}" @selected((string) $val('trainer_id') === (string) $t->id)>{{ $t->name }}{{ $t->specialization ? ' · ' . $t->specialization : '' }}</option>
                    @endforeach
                </select>
                @error('trainer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Joined On</label>
                <input type="date" name="joined_at" value="{{ old('joined_at', $member->joined_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="form-control @error('joined_at') is-invalid @enderror">
                @error('joined_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach (['active', 'inactive', 'suspended'] as $s)
                        <option value="{{ $s }}" @selected($val('status', 'active') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ $val('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
