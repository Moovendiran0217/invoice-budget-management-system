<form method="POST" action="{{ $action }}" class="card mt-6 grid gap-4 p-5 md:grid-cols-2">
    @csrf
    @isset($method)
        @method($method)
    @endisset
    <div>
        <label class="field-label">Name</label>
        <input class="field" name="name" value="{{ old('name', $product?->name ?? '') }}" required>
    </div>
    <div>
        <label class="field-label">SKU</label>
        <input class="field" name="sku" value="{{ old('sku', $product?->sku ?? '') }}" required>
    </div>
    <div>
        <label class="field-label">Type</label>
        <select class="field" name="type" required>
            @foreach (\App\Enums\ProductType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $product?->type?->value ?? '') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label">Unit price</label>
        <input class="field" type="number" min="0" step="0.01" name="unit_price" value="{{ old('unit_price', $product?->unit_price ?? 0) }}" required>
    </div>
    <div>
        <label class="field-label">Tax rate %</label>
        <input class="field" type="number" min="0" max="100" step="0.01" name="tax_rate" value="{{ old('tax_rate', $product?->tax_rate ?? 18) }}" required>
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))>
            Active
        </label>
    </div>
    <div class="md:col-span-2">
        <label class="field-label">Description</label>
        <textarea class="field" name="description" rows="3">{{ old('description', $product?->description ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <button class="btn btn-primary">Save product</button>
        <a class="btn btn-secondary" href="{{ route('products.index') }}">Cancel</a>
    </div>
</form>
