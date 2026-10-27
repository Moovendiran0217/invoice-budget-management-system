<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(): View
    {
        $budgets = Budget::query()
            ->with('expenses')
            ->latest()
            ->paginate(12);

        return view('budgets.index', compact('budgets'));
    }

    public function create(): View
    {
        return view('budgets.create');
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['month'] = $data['period_type'] === 'yearly' ? null : ($data['month'] ?? null);

        Budget::query()->create($data);

        return redirect()->route('budgets.index')->with('success', 'Budget created.');
    }

    public function show(Budget $budget): View
    {
        $budget->load(['expenses.supplier', 'expenses.invoice']);

        return view('budgets.show', compact('budget'));
    }

    public function edit(Budget $budget): View
    {
        return view('budgets.edit', compact('budget'));
    }

    public function update(StoreBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $data = $request->validated();
        $data['month'] = $data['period_type'] === 'yearly' ? null : ($data['month'] ?? null);
        $budget->update($data);

        return redirect()->route('budgets.show', $budget)->with('success', 'Budget updated.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budget deleted.');
    }
}
