<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Duration (days) <span class="text-danger">*</span></label>
                <input type="number" min="1" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" class="form-control @error('duration_days') is-invalid @enderror" required>
                @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">30 = 1 month, 365 = 1 year</div>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label">Price ({{ config('gym.currency') }}) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price) }}" class="form-control @error('price') is-invalid @enderror" required>
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Description</label>
                <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $plan->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Features</label>
                <textarea name="features" rows="5" class="form-control @error('features') is-invalid @enderror" placeholder="One per line">{{ old('features', $plan->features) }}</textarea>
                @error('features')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">One feature per line.</div>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $plan->is_active))>
                    <label class="form-check-label" for="is_active">Active (available for new subscriptions)</label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-end">
        <a href="{{ route('admin.plans.index') }}" class="btn btn-light">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Plan</button>
    </div>
</div>
