@component('layouts.app', ['title' => 'Expenses'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Expense tracking</h1>
            <p class="page-subtitle">Record spend and attach it to a budget so utilization stays accurate.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('expenses.create') }}">Add expense</a>
    </div>
    <form class="mt-6" method="GET">
        <select class="field max-w-xs" name="category" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
    </form>
    <div class="card mt-6 overflow-hidden">
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Budget</th><th>Amount</th><th></th></tr></thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>{{ $expense->category }}</td>
                            <td>{{ $expense->budget?->name ?? 'Unassigned' }}</td>
                            <td>@money($expense->amount)</td>
                            <td class="text-right">
                                <a class="text-sm text-teal-800" href="{{ route('expenses.edit', $expense) }}">Edit</a>
                                <form class="inline" method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ml-3 text-sm text-rose-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $expenses->links() }}</div>
    </div>
@endcomponent
