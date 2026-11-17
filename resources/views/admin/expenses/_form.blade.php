<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" value="{{ old('title', $expense->title) }}" class="form-control @error('title') is-invalid @enderror" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
            <option value="">— Select category —</option>
            @foreach (\App\Models\Expense::CATEGORIES as $cat)
                <option value="{{ $cat }}" @selected(old('category', $expense->category) === $cat)>{{ ucfirst($cat) }}</option>
            @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Amount ({{ config('gym.currency') }}) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $expense->amount) }}" class="form-control @error('amount') is-invalid @enderror" required>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Spent On <span class="text-danger">*</span></label>
        <input type="date" name="spent_on" value="{{ old('spent_on', $expense->spent_on?->format('Y-m-d')) }}" class="form-control @error('spent_on') is-invalid @enderror" required>
        @error('spent_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $expense->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="mt-4 d-flex flex-wrap gap-2">
    <button class="btn btn-primary"><i class="bi bi-check2"></i> Save Expense</button>
    <a href="{{ route('admin.expenses.index') }}" class="btn btn-light">Cancel</a>
</div>
