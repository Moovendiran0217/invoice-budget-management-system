<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(InvoiceService $invoices): View
    {
        $invoices->refreshOverdueStatuses();
        $year = now()->year;
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = $yearStart->copy()->endOfYear();

        $monthlySales = [];
        $monthlyPurchases = [];
        $monthlyExpenses = [];

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $monthlySales[$month] = Money::round(
                Invoice::query()
                    ->ofType(InvoiceType::Sales)
                    ->where('status', '!=', InvoiceStatus::Cancelled)
                    ->whereBetween('invoice_date', [$start, $end])
                    ->sum('total')
            );
            $monthlyPurchases[$month] = Money::round(
                Invoice::query()
                    ->ofType(InvoiceType::Purchase)
                    ->where('status', '!=', InvoiceStatus::Cancelled)
                    ->whereBetween('invoice_date', [$start, $end])
                    ->sum('total')
            );
            $monthlyExpenses[$month] = Money::round(
                Expense::query()->whereBetween('expense_date', [$start, $end])->sum('amount')
            );
        }

        $salesTotal = array_sum($monthlySales);
        $purchaseTotal = array_sum($monthlyPurchases);
        $expenseTotal = array_sum($monthlyExpenses);

        $statusBreakdown = Invoice::query()
            ->whereBetween('invoice_date', [$yearStart, $yearEnd])
            ->selectRaw('status, count(*) as total, sum(total) as amount')
            ->groupBy('status')
            ->get();

        $expensesByCategory = Expense::query()
            ->whereBetween('expense_date', [$yearStart, $yearEnd])
            ->selectRaw('category, sum(amount) as amount')
            ->groupBy('category')
            ->orderByDesc('amount')
            ->get();

        $outstanding = Invoice::query()
            ->with(['customer', 'supplier', 'payments'])
            ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])
            ->orderBy('due_date')
            ->get();

        $budgets = Budget::query()->with('expenses')->where('year', $year)->orderByDesc('year')->get();

        return view('reports.index', [
            'year' => $year,
            'monthlySales' => $monthlySales,
            'monthlyPurchases' => $monthlyPurchases,
            'monthlyExpenses' => $monthlyExpenses,
            'salesTotal' => $salesTotal,
            'purchaseTotal' => $purchaseTotal,
            'expenseTotal' => $expenseTotal,
            'profit' => Money::round($salesTotal - $purchaseTotal - $expenseTotal),
            'statusBreakdown' => $statusBreakdown,
            'expensesByCategory' => $expensesByCategory,
            'outstanding' => $outstanding,
            'budgets' => $budgets,
        ]);
    }
}
