<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private InvoiceCalculator $calculator) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(InvoiceType $type, array $data, array $items, int $userId): Invoice
    {
        return DB::transaction(function () use ($type, $data, $items, $userId) {
            $invoice = Invoice::query()->create([
                'invoice_number' => $this->nextNumber($type),
                'type' => $type,
                'customer_id' => $type === InvoiceType::Sales ? $data['customer_id'] : null,
                'supplier_id' => $type === InvoiceType::Purchase ? $data['supplier_id'] : null,
                'user_id' => $userId,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'status' => InvoiceStatus::Pending,
                'discount_percent' => $data['discount_percent'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($invoice, $items);
            $this->recalculate($invoice);

            return $invoice->fresh(['items', 'customer', 'supplier', 'payments']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function update(Invoice $invoice, array $data, array $items): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $items) {
            $invoice->update([
                'customer_id' => $invoice->type === InvoiceType::Sales ? $data['customer_id'] : null,
                'supplier_id' => $invoice->type === InvoiceType::Purchase ? $data['supplier_id'] : null,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'discount_percent' => $data['discount_percent'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->delete();
            $this->syncItems($invoice, $items);
            $this->recalculate($invoice->fresh());

            return $invoice->fresh(['items', 'customer', 'supplier', 'payments']);
        });
    }

    public function recalculate(Invoice $invoice): Invoice
    {
        $invoice->load('items', 'payments');

        $totals = $this->calculator->invoice(
            $invoice->items->map(fn (InvoiceItem $item) => [
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percent' => $item->discount_percent,
                'tax_rate' => $item->tax_rate,
            ])->all(),
            (float) $invoice->discount_percent,
            (float) $invoice->discount_amount,
        );

        foreach ($invoice->items as $index => $item) {
            $line = $totals['items'][$index];
            $item->update([
                'line_subtotal' => $line['line_subtotal'],
                'discount_amount' => $line['discount_amount'],
                'tax_amount' => $line['tax_amount'],
                'line_total' => $line['line_total'],
            ]);
        }

        $invoice->update([
            'subtotal' => $totals['subtotal'],
            'discount_total' => $totals['discount_total'],
            'tax_total' => $totals['tax_total'],
            'total' => $totals['total'],
        ]);

        $this->refreshStatus($invoice->fresh('payments'));

        return $invoice->fresh(['items', 'payments']);
    }

    public function refreshStatus(Invoice $invoice): void
    {
        if ($invoice->status === InvoiceStatus::Cancelled) {
            return;
        }

        $paid = Money::round($invoice->payments()->sum('amount'));
        $total = (float) $invoice->total;

        if ($total > 0 && $paid >= $total) {
            $status = InvoiceStatus::Paid;
        } elseif ($invoice->due_date->copy()->endOfDay()->isPast() && $paid < $total) {
            $status = InvoiceStatus::Overdue;
        } else {
            $status = InvoiceStatus::Pending;
        }

        if ($invoice->status !== $status) {
            $invoice->update(['status' => $status]);
        }
    }

    public function refreshOverdueStatuses(): void
    {
        Invoice::query()
            ->where('status', InvoiceStatus::Pending)
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => InvoiceStatus::Overdue]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            $line = $this->calculator->line(
                (float) $item['quantity'],
                (float) $item['unit_price'],
                (float) ($item['discount_percent'] ?? 0),
                (float) ($item['tax_rate'] ?? 0),
            );

            $invoice->items()->create([
                'product_id' => $item['product_id'] ?: null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'tax_rate' => $item['tax_rate'] ?? 0,
                'line_subtotal' => $line['line_subtotal'],
                'discount_amount' => $line['discount_amount'],
                'tax_amount' => $line['tax_amount'],
                'line_total' => $line['line_total'],
            ]);
        }
    }

    private function nextNumber(InvoiceType $type): string
    {
        $year = now()->year;
        $prefix = $type->numberPrefix().'-'.$year.'-';

        $last = Invoice::query()
            ->where('type', $type)
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $sequence = 1;
        if (is_string($last)) {
            $sequence = ((int) substr($last, strrpos($last, '-') + 1)) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
