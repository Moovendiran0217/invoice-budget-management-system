<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceType;
use App\Http\Requests\StoreExpenseRequest;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $expenses = Expense::query()
            ->with(['budget', 'supplier'])
            ->when($request->string('category')->isNotEmpty(), fn ($query) => $query->where('category', $request->string('category')))
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();

        $categories = Expense::query()->select('category')->distinct()->orderBy('category')->pluck('category');

        return view('expenses.index', compact('expenses', 'categories'));
    }

    public function create(): View
    {
        return view('expenses.create', $this->formData());
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        Expense::query()->create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', array_merge($this->formData(), compact('expense')));
    }

    public function update(StoreExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'budgets' => Budget::query()->latest()->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'purchaseInvoices' => Invoice::query()->ofType(InvoiceType::Purchase)->latest()->get(),
        ];
    }
}
