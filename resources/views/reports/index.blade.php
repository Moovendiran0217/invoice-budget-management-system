@component('layouts.app', ['title' => 'Reports'])
    <h1 class="page-title">Reports & analytics</h1>
    <p class="page-subtitle">Year-to-date sales, purchases, expenses, and budget performance for {{ $year }}.</p>

    <div class="mt-6 grid gap-4 md:grid-cols-4">
        <article class="card p-5"><p class="text-sm text-slate-500">Sales</p><p class="stat-value">@money($salesTotal)</p></article>
        <article class="card p-5"><p class="text-sm text-slate-500">Purchases</p><p class="stat-value">@money($purchaseTotal)</p></article>
        <article class="card p-5"><p class="text-sm text-slate-500">Expenses</p><p class="stat-value">@money($expenseTotal)</p></article>
        <article class="card p-5"><p class="text-sm text-slate-500">Operating result</p><p class="stat-value">@money($profit)</p></article>
    </div>

    <div class="card mt-6 p-5">
        <h2 class="font-semibold">Monthly activity</h2>
        <div class="mt-4 space-y-3">
            @for ($month = 1; $month <= 12; $month++)
                @php
                    $max = max(1, max($monthlySales), max($monthlyPurchases), max($monthlyExpenses));
                @endphp
                <div>
                    <div class="mb-1 flex justify-between text-xs text-slate-500">
                        <span>{{ date('M', mktime(0, 0, 0, $month, 1)) }}</span>
                        <span>Sales @money($monthlySales[$month]) · Purchases @money($monthlyPurchases[$month]) · Expenses @money($monthlyExpenses[$month])</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="h-2 rounded-full bg-stone-200"><span class="block h-2 rounded-full bg-teal-700" style="width: {{ ($monthlySales[$month] / $max) * 100 }}%"></span></div>
                        <div class="h-2 rounded-full bg-stone-200"><span class="block h-2 rounded-full bg-amber-700" style="width: {{ ($monthlyPurchases[$month] / $max) * 100 }}%"></span></div>
                        <div class="h-2 rounded-full bg-stone-200"><span class="block h-2 rounded-full bg-rose-700" style="width: {{ ($monthlyExpenses[$month] / $max) * 100 }}%"></span></div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-2">
        <article class="card overflow-hidden">
            <div class="px-5 py-4 font-semibold">Invoice status mix</div>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Status</th><th>Count</th><th>Value</th></tr></thead>
                    <tbody>
                        @foreach ($statusBreakdown as $row)
                            <tr>
                                <td class="capitalize">{{ $row->status }}</td>
                                <td>{{ $row->total }}</td>
                                <td>@money($row->amount)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>
        <article class="card overflow-hidden">
            <div class="px-5 py-4 font-semibold">Expenses by category</div>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Category</th><th>Amount</th></tr></thead>
                    <tbody>
                        @forelse ($expensesByCategory as $row)
                            <tr>
                                <td>{{ $row->category }}</td>
                                <td>@money($row->amount)</td>
                            </tr>
                        @empty
                            <tr><td colspan="2">No expenses this year.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    <div class="card mt-6 overflow-hidden">
        <div class="px-5 py-4 font-semibold">Outstanding invoices</div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Number</th><th>Party</th><th>Due date</th><th>Status</th><th>Amount due</th></tr></thead>
                <tbody>
                    @forelse ($outstanding as $invoice)
                        <tr>
                            <td><a class="text-teal-800" href="{{ route('invoices.show', [$invoice->type, $invoice]) }}">{{ $invoice->invoice_number }}</a></td>
                            <td>{{ $invoice->partyName() }}</td>
                            <td>{{ $invoice->due_date->format('d M Y') }}</td>
                            <td><span class="badge {{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span></td>
                            <td>@money($invoice->amountDue())</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No outstanding invoices.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-6 overflow-hidden">
        <div class="px-5 py-4 font-semibold">Budget vs actual</div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Budget</th><th>Period</th><th>Allocated</th><th>Spent</th><th>Remaining</th><th>%</th></tr></thead>
                <tbody>
                    @foreach ($budgets as $budget)
                        <tr>
                            <td>{{ $budget->name }}</td>
                            <td>{{ $budget->periodLabel() }}</td>
                            <td>@money($budget->allocated_amount)</td>
                            <td>@money($budget->spentAmount())</td>
                            <td>@money($budget->remainingAmount())</td>
                            <td>{{ $budget->utilizationPercent() }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endcomponent
