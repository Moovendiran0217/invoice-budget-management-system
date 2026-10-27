@php
    $invoice = $invoice ?? null;
    $items = old('items', $invoice?->items?->map(fn ($item) => $item->toArray())->all() ?: [[]]);
@endphp

<form method="POST" action="{{ $action }}" data-invoice-form class="mt-6 space-y-6">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="card grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
        @if ($type->value === 'sales')
            <div>
                <label class="field-label">Customer</label>
                <select class="field" name="customer_id" required>
                    <option value="">Select customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $invoice?->customer_id) == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div>
                <label class="field-label">Supplier</label>
                <select class="field" name="supplier_id" required>
                    <option value="">Select supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $invoice?->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label class="field-label">Invoice date</label>
            <input class="field" type="date" name="invoice_date" value="{{ old('invoice_date', optional($invoice?->invoice_date)->format('Y-m-d') ?? now()->toDateString()) }}" required>
        </div>
        <div>
            <label class="field-label">Due date</label>
            <input class="field" type="date" name="due_date" value="{{ old('due_date', optional($invoice?->due_date)->format('Y-m-d') ?? now()->addDays(14)->toDateString()) }}" required>
        </div>
        <div>
            <label class="field-label">Invoice discount %</label>
            <input class="field" type="number" min="0" max="100" step="0.01" name="discount_percent" value="{{ old('discount_percent', $invoice?->discount_percent ?? 0) }}">
        </div>
        <div>
            <label class="field-label">Extra discount amount</label>
            <input class="field" type="number" min="0" step="0.01" name="discount_amount" value="{{ old('discount_amount', $invoice?->discount_amount ?? 0) }}">
        </div>
        <div class="md:col-span-2 xl:col-span-3">
            <label class="field-label">Notes</label>
            <input class="field" name="notes" value="{{ old('notes', $invoice?->notes ?? '') }}">
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4">
            <h2 class="font-semibold">Line items</h2>
            <button class="btn btn-secondary" type="button" data-add-line>Add line</button>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Disc %</th>
                        <th>Tax %</th>
                        <th>Line total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-line-container>
                    @foreach ($items as $index => $item)
                        @include('invoices._line', ['index' => $index, 'item' => $item, 'products' => $products])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <template id="invoice-line-template">
        @include('invoices._line', ['index' => '__INDEX__', 'item' => [], 'products' => $products])
    </template>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="card space-y-2 p-5 text-sm lg:col-start-3">
            <div class="flex justify-between"><span>Subtotal</span><strong data-summary="subtotal">₹0.00</strong></div>
            <div class="flex justify-between"><span>Discount</span><strong data-summary="discount">₹0.00</strong></div>
            <div class="flex justify-between"><span>Tax</span><strong data-summary="tax">₹0.00</strong></div>
            <div class="flex justify-between border-t border-stone-200 pt-2 text-base"><span>Total</span><strong data-summary="total">₹0.00</strong></div>
        </div>
    </div>

    <div class="flex gap-3">
        <button class="btn btn-primary">Save invoice</button>
        <a class="btn btn-secondary" href="{{ route('invoices.index', $type) }}">Cancel</a>
    </div>
</form>
