<form method="POST" action="{{ $action }}" class="card mt-6 grid gap-4 p-5 md:grid-cols-2">
    @csrf
    @isset($method)
        @method($method)
    @endisset
    <div class="md:col-span-2">
        <label class="field-label">Budget name</label>
        <input class="field" name="name" value="{{ old('name', $budget?->name ?? '') }}" required>
    </div>
    <div>
        <label class="field-label">Period</label>
        <select class="field" name="period_type" required>
            @foreach (\App\Enums\BudgetPeriod::cases() as $period)
                <option value="{{ $period->value }}" @selected(old('period_type', $budget?->period_type?->value ?? 'monthly') === $period->value)>{{ $period->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Year</label>
        <input class="field" type="number" name="year" value="{{ old('year', $budget?->year ?? now()->year) }}" required>
    </div>
    <div>
        <label class="field-label">Month</label>
        <select class="field" name="month">
            <option value="">Yearly / none</option>
            @for ($month = 1; $month <= 12; $month++)
                <option value="{{ $month }}" @selected((int) old('month', $budget?->month ?? now()->month) === $month)>{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
            @endfor
        </select>
    </div>
    <div>
        <label class="field-label">Category</label>
        <select class="field" name="category" required>
            @foreach (\App\Support\FinanceCategories::all() as $category)
                <option value="{{ $category }}" @selected(old('category', $budget?->category ?? '') === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Allocated amount</label>
        <input class="field" type="number" min="0.01" step="0.01" name="allocated_amount" value="{{ old('allocated_amount', $budget?->allocated_amount ?? '') }}" required>
    </div>
    <div class="md:col-span-2">
        <label class="field-label">Notes</label>
        <textarea class="field" name="notes" rows="3">{{ old('notes', $budget?->notes ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <button class="btn btn-primary">Save budget</button>
        <a class="btn btn-secondary" href="{{ route('budgets.index') }}">Cancel</a>
    </div>
</form>
