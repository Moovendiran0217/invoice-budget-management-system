@component('layouts.app', ['title' => 'Budgets'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Budget management</h1>
            <p class="page-subtitle">Monthly and yearly allocations with live spend against each budget.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('budgets.create') }}">Create budget</a>
    </div>
    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @forelse ($budgets as $budget)
            <article class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $budget->name }}</h2>
                        <p class="text-sm text-slate-500">{{ $budget->category }} · {{ $budget->periodLabel() }}</p>
                    </div>
                    <a class="text-sm text-teal-800" href="{{ route('budgets.show', $budget) }}">Details</a>
                </div>
                <div class="mt-4 flex justify-between text-sm">
                    <span>Spent @money($budget->spentAmount())</span>
                    <span>Allocated @money($budget->allocated_amount)</span>
                </div>
                <div class="progress mt-2 {{ $budget->isOverBudget() ? 'over' : '' }}"><span style="width: {{ min(100, $budget->utilizationPercent()) }}%"></span></div>
                <p class="mt-2 text-sm {{ $budget->isOverBudget() ? 'text-rose-700' : 'text-slate-500' }}">
                    Remaining @money($budget->remainingAmount()) · {{ $budget->utilizationPercent() }}% used
                </p>
            </article>
        @empty
            <p class="text-sm text-slate-500">No budgets yet. Create a monthly or yearly budget to start tracking spend.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $budgets->links() }}</div>
@endcomponent
