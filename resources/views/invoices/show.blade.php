@component('layouts.app', ['title' => $invoice->invoice_number])
    <div class="no-print flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm uppercase tracking-[0.2em] text-teal-800">{{ $type->label() }} invoice</p>
            <h1 class="page-title">{{ $invoice->invoice_number }}</h1>
            <p class="page-subtitle">{{ $invoice->partyName() }} · {{ $invoice->invoice_date->format('d M Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="badge {{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span>
            <a class="btn btn-secondary" href="{{ route('invoices.edit', [$type, $invoice]) }}">Edit</a>
            <button class="btn btn-secondary" type="button" onclick="window.print()">Print</button>
            <form method="POST" action="{{ route('invoices.destroy', [$type, $invoice]) }}" onsubmit="return confirm('Delete this invoice?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <article class="card p-5 text-sm">
            <h2 class="font-semibold">Bill to</h2>
            <p class="mt-2">{{ $invoice->partyName() }}</p>
            @if ($invoice->customer)
                <p class="text-slate-500">{{ $invoice->customer->address }}</p>
                <p class="text-slate-500">GSTIN: {{ $invoice->customer->gstin ?: '—' }}</p>
            @endif
            @if ($invoice->supplier)
                <p class="text-slate-500">{{ $invoice->supplier->address }}</p>
                <p class="text-slate-500">GSTIN: {{ $invoice->supplier->gstin ?: '—' }}</p>
            @endif
        </article>
        <article class="card p-5 text-sm">
            <h2 class="font-semibold">Dates</h2>
            <p class="mt-2">Invoice date: {{ $invoice->invoice_date->format('d M Y') }}</p>
            <p>Due date: {{ $invoice->due_date->format('d M Y') }}</p>
            <p>Prepared by: {{ $invoice->user?->name }}</p>
        </article>
        <article class="card p-5 text-sm">
            <h2 class="font-semibold">Balance</h2>
            <p class="mt-2">Total: @money($invoice->total)</p>
            <p>Paid: @money($invoice->amountPaid())</p>
            <p class="font-semibold">Amount due: @money($invoice->amountDue())</p>
        </article>
    </div>

    <div class="card mt-6 overflow-hidden">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Disc %</th>
                        <th>Tax %</th>
                        <th>Tax</th>
                        <th>Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>@money($item->unit_price)</td>
                            <td>{{ $item->discount_percent }}%</td>
                            <td>{{ $item->tax_rate }}%</td>
                            <td>@money($item->tax_amount)</td>
                            <td>@money($item->line_total)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ml-auto max-w-sm space-y-2 px-5 py-4 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><strong>@money($invoice->subtotal)</strong></div>
            <div class="flex justify-between"><span>Discount</span><strong>@money($invoice->discount_total)</strong></div>
            <div class="flex justify-between"><span>Tax</span><strong>@money($invoice->tax_total)</strong></div>
            <div class="flex justify-between border-t border-stone-200 pt-2 text-base"><span>Total</span><strong>@money($invoice->total)</strong></div>
        </div>
    </div>

    @if ($invoice->notes)
        <p class="mt-4 text-sm text-slate-600">Notes: {{ $invoice->notes }}</p>
    @endif

    <div class="no-print mt-8 grid gap-4 lg:grid-cols-2">
        <article class="card overflow-hidden">
            <div class="px-5 py-4">
                <h2 class="font-semibold">Payments</h2>
            </div>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Date</th><th>Method</th><th>Amount</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td>{{ $payment->method->label() }}</td>
                                <td>@money($payment->amount)</td>
                                <td>
                                    <form method="POST" action="{{ route('payments.destroy', [$invoice, $payment]) }}" onsubmit="return confirm('Remove this payment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm text-rose-700">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
        <article class="card p-5">
            <h2 class="font-semibold">Record payment</h2>
            <form class="mt-4 grid gap-3 sm:grid-cols-2" method="POST" action="{{ route('payments.store', $invoice) }}">
                @csrf
                <div>
                    <label class="field-label">Amount</label>
                    <input class="field" type="number" min="0.01" step="0.01" name="amount" value="{{ $invoice->amountDue() }}" required>
                </div>
                <div>
                    <label class="field-label">Date</label>
                    <input class="field" type="date" name="payment_date" value="{{ now()->toDateString() }}" required>
                </div>
                <div>
                    <label class="field-label">Method</label>
                    <select class="field" name="method" required>
                        @foreach (\App\Enums\PaymentMethod::cases() as $method)
                            <option value="{{ $method->value }}">{{ $method->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Reference</label>
                    <input class="field" name="reference">
                </div>
                <div class="sm:col-span-2">
                    <button class="btn btn-primary">Save payment</button>
                </div>
            </form>
        </article>
    </div>
@endcomponent
