@component('layouts.app', ['title' => $budget->name])
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="page-title">{{ $budget->name }}</h1>
            <p class="page-subtitle">{{ $budget->category }} · {{ $budget->period_type->label() }} · {{ $budget->periodLabel() }}</p>
        </div>
        <div class="flex gap-2">
            <a class="btn btn-secondary" href="{{ route('budgets.edit', $budget) }}">Edit</a>
            <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('Delete this budget?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <article class="card p-5"><p class="text-sm text-slate-500">Allocated</p><p class="stat-value">@money($budget->allocated_amount)</p></article>
        <article class="card p-5"><p class="text-sm text-slate-500">Spent</p><p class="stat-value">@money($budget->spentAmount())</p></article>
        <article class="card p-5"><p class="text-sm text-slate-500">Remaining</p><p class="stat-value">@money($budget->remainingAmount())</p></article>
    </div>
    <div class="progress mt-4 {{ $budget->isOverBudget() ? 'over' : '' }}"><span style="width: {{ min(100, $budget->utilizationPercent()) }}%"></span></div>
    <div class="card mt-6 overflow-hidden">
        <div class="px-5 py-4 font-semibold">Linked expenses</div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Date</th><th>Description</th><th>Supplier</th><th>Amount</th></tr></thead>
                <tbody>
                    @forelse ($budget->expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>{{ $expense->supplier?->name ?? '—' }}</td>
                            <td>@money($expense->amount)</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No expenses linked to this budget yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endcomponent
