<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(private InvoiceService $invoices) {}

    public function store(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        DB::transaction(function () use ($request, $invoice): void {
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $invoice->load('payments');

            abort_if($invoice->status === InvoiceStatus::Cancelled, 422, 'Payments cannot be added to a cancelled invoice.');

            $amount = (float) $request->validated('amount');
            if ($amount > $invoice->amountDue()) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment cannot exceed the remaining invoice balance.',
                ]);
            }

            $invoice->payments()->create($request->validated());
            $this->invoices->refreshStatus($invoice->fresh('payments'));
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Invoice $invoice, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice_id === $invoice->id, 404);

        $payment->delete();
        $this->invoices->refreshStatus($invoice->fresh('payments'));

        return back()->with('success', 'Payment removed.');
    }
}
