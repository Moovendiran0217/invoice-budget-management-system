@component('layouts.app', ['title' => $type->label().' invoices'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">{{ $type->label() }} invoices</h1>
            <p class="page-subtitle">Create, review, and track payment status for {{ strtolower($type->label()) }} invoices.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('invoices.create', $type) }}">New {{ strtolower($type->label()) }} invoice</a>
    </div>

    <form class="mt-6 flex flex-wrap gap-3" method="GET">
        <input class="field max-w-xs" type="search" name="search" value="{{ request('search') }}" placeholder="Search number or party">
        <select class="field max-w-40" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary">Filter</button>
    </form>

    <div class="card mt-6 overflow-hidden">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Due</th>
                        <th>Party</th>
                        <th>Total</th>
                        <th>Due amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="font-medium">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td>{{ $invoice->due_date->format('d M Y') }}</td>
                            <td>{{ $invoice->partyName() }}</td>
                            <td>@money($invoice->total)</td>
                            <td>@money($invoice->amountDue())</td>
                            <td><span class="badge {{ $invoice->status->badgeClass() }}">{{ $invoice->status->label() }}</span></td>
                            <td class="text-right">
                                <a class="text-sm text-teal-800" href="{{ route('invoices.show', [$type, $invoice]) }}">View</a>
                                <a class="ml-3 text-sm text-slate-500" href="{{ route('invoices.edit', [$type, $invoice]) }}">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No invoices match this filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $invoices->links() }}</div>
    </div>
@endcomponent
