@component('layouts.app', ['title' => 'Dashboard'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Financial summary</h1>
            <p class="page-subtitle">Sales, purchases, outstanding invoices, and budget burn in one place.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('invoices.create', 'sales') }}">New sales invoice</a>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="card p-5">
            <p class="text-sm text-slate-500">Sales invoiced</p>
            <p class="stat-value">@money($salesTotal)</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-slate-500">Purchases invoiced</p>
            <p class="stat-value">@money($purchaseTotal)</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-slate-500">Outstanding invoice value</p>
            <p class="stat-value">@money($outstanding)</p>
        </article>
        <article class="card p-5">
            <p class="text-sm text-slate-500">Net position</p>
            <p class="stat-value">@money($netPosition)</p>
            <p class="mt-1 text-xs text-slate-500">Sales minus purchases and expenses</p>
        </article>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <article class="card p-5">
            <h2 class="font-semibold">Invoice status</h2>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><span>Pending</span><strong>{{ $invoiceCounts['pending'] }}</strong></div>
                <div class="flex justify-between"><span>Paid</span><strong>{{ $invoiceCounts['paid'] }}</strong></div>
                <div class="flex justify-between"><span>Overdue</span><strong class="text-rose-700">{{ $invoiceCounts['overdue'] }}</strong></div>
            </div>
        </article>
        <article class="card p-5 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Currency rates</h2>
                <a class="text-sm text-teal-800" href="{{ route('exchange-rates.index') }}">Open converter</a>
            </div>
            <p class="mt-1 text-xs text-slate-500">{{ $exchangeRates['source'] }} · {{ $exchangeRates['date'] ?? 'Unavailable' }}</p>
            @if ($exchangeRates['error'])
                <p class="mt-2 text-sm text-amber-700">{{ $exchangeRates['error'] }}</p>
            @endif
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @forelse ($exchangeRates['rates'] as $code => $rate)
                    <div class="rounded-xl bg-stone-50 px-3 py-3">
                        <p class="text-xs text-slate-500">INR → {{ $code }}</p>
                        <p class="mt-1 font-semibold">{{ number_format($rate, 4) }}</p>
                    </div>
                @empty
                    <p class="col-span-4 text-sm text-slate-500">No live rates to display.</p>
                @endforelse
            </div>
        </article>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-2">
        <article class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="font-semibold">Recent invoices</h2>
                <a class="text-sm text-teal-800" href="{{ route('invoices.index', 'sales') }}">View all</a>
            </div>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>Number</th><th>Party</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($recentInvoices as $invoice)
                            <tr>
                                <td><a class="font-medium text-teal-800" href="{{ route('invoices.show', [$invoice->type, $invoice]) }}">{{ $invoice->invoice_number }}</a></td>
                                <td>{{ $invoice->partyName() }}</td>
                                <td>@money($invoice->total)</td>
                                <td><span class="badge {{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
        <article class="card overflow-hidden">
            <div class="px-5 py-4">
                <h2 class="font-semibold">Budget utilization</h2>
            </div>
            <div class="space-y-4 px-5 pb-5">
                @forelse ($budgets as $budget)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ $budget->name }}</span>
                            <span>{{ $budget->utilizationPercent() }}%</span>
                        </div>
                        <div class="progress mt-2 {{ $budget->isOverBudget() ? 'over' : '' }}"><span style="width: {{ min(100, $budget->utilizationPercent()) }}%"></span></div>
                        <p class="mt-1 text-xs text-slate-500">Spent @money($budget->spentAmount()) of @money($budget->allocated_amount)</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No budgets created yet.</p>
                @endforelse
            </div>
        </article>
    </div>
@endcomponent
