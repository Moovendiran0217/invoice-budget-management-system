<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Invoice;
use App\Services\ExchangeRateService;
use App\Services\InvoiceService;
use App\Support\Money;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ExchangeRateService $exchangeRates, InvoiceService $invoices): View
    {
        $invoices->refreshOverdueStatuses();

        $sales = Invoice::query()->ofType(InvoiceType::Sales)->where('status', '!=', InvoiceStatus::Cancelled);
        $purchases = Invoice::query()->ofType(InvoiceType::Purchase)->where('status', '!=', InvoiceStatus::Cancelled);

        $salesTotal = Money::round((clone $sales)->sum('total'));
        $purchaseTotal = Money::round((clone $purchases)->sum('total'));
        $outstanding = Money::round((float) Invoice::query()
            ->whereNotIn('status', [InvoiceStatus::Cancelled, InvoiceStatus::Paid])
            ->selectRaw('COALESCE(SUM(total - COALESCE((SELECT SUM(payments.amount) FROM payments WHERE payments.invoice_id = invoices.id), 0)), 0) as outstanding')
            ->value('outstanding'));
        $expenseTotal = Money::round(Expense::query()->sum('amount'));

        $recentInvoices = Invoice::query()
            ->with(['customer', 'supplier', 'payments'])
            ->latest('invoice_date')
            ->limit(6)
            ->get();

        $overdueInvoices = Invoice::query()
            ->with(['customer', 'supplier', 'payments'])
            ->where('status', InvoiceStatus::Overdue)
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $budgets = Budget::query()->with('expenses')->latest()->limit(5)->get();

        return view('dashboard', [
            'salesTotal' => $salesTotal,
            'purchaseTotal' => $purchaseTotal,
            'outstanding' => $outstanding,
            'expenseTotal' => $expenseTotal,
            'netPosition' => Money::round($salesTotal - $purchaseTotal - $expenseTotal),
            'invoiceCounts' => [
                'pending' => Invoice::query()->where('status', InvoiceStatus::Pending)->count(),
                'paid' => Invoice::query()->where('status', InvoiceStatus::Paid)->count(),
                'overdue' => Invoice::query()->where('status', InvoiceStatus::Overdue)->count(),
            ],
            'recentInvoices' => $recentInvoices,
            'overdueInvoices' => $overdueInvoices,
            'budgets' => $budgets,
            'exchangeRates' => $exchangeRates->latest(),
        ]);
    }
}
