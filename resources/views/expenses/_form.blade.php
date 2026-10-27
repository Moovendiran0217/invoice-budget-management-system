<form method="POST" action="{{ $action }}" class="card mt-6 grid gap-4 p-5 md:grid-cols-2">
    @csrf
    @isset($method)
        @method($method)
    @endisset
    <div>
        <label class="field-label">Description</label>
        <input class="field" name="description" value="{{ old('description', $expense?->description ?? '') }}" required>
    </div>
    <div>
        <label class="field-label">Amount</label>
        <input class="field" type="number" min="0.01" step="0.01" name="amount" value="{{ old('amount', $expense?->amount ?? '') }}" required>
    </div>
    <div>
        <label class="field-label">Date</label>
        <input class="field" type="date" name="expense_date" value="{{ old('expense_date', optional($expense?->expense_date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}" required>
    </div>
    <div>
        <label class="field-label">Category</label>
        <select class="field" name="category" required>
            @foreach (\App\Support\FinanceCategories::all() as $category)
                <option value="{{ $category }}" @selected(old('category', $expense?->category ?? '') === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Budget</label>
        <select class="field" name="budget_id">
            <option value="">Unassigned</option>
            @foreach ($budgets as $budget)
                <option value="{{ $budget->id }}" @selected(old('budget_id', $expense?->budget_id ?? '') == $budget->id)>{{ $budget->name }} ({{ $budget->periodLabel() }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Supplier</label>
        <select class="field" name="supplier_id">
            <option value="">None</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $expense?->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="field-label">Linked purchase invoice</label>
        <select class="field" name="invoice_id">
            <option value="">None</option>
            @foreach ($purchaseInvoices as $invoice)
                <option value="{{ $invoice->id }}" @selected(old('invoice_id', $expense?->invoice_id ?? '') == $invoice->id)>{{ $invoice->invoice_number }} · @money($invoice->total)</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="field-label">Notes</label>
        <textarea class="field" name="notes" rows="3">{{ old('notes', $expense?->notes ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <button class="btn btn-primary">Save expense</button>
        <a class="btn btn-secondary" href="{{ route('expenses.index') }}">Cancel</a>
    </div>
</form>
