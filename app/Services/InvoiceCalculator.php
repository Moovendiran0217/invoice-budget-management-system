<?php

namespace App\Services;

use App\Support\Money;

class InvoiceCalculator
{
    /**
     * @return array{line_subtotal: float, discount_amount: float, taxable_amount: float, tax_amount: float, line_total: float}
     */
    public function line(float $quantity, float $unitPrice, float $discountPercent, float $taxRate): array
    {
        $lineSubtotal = Money::round($quantity * $unitPrice);
        $discountAmount = Money::round($lineSubtotal * ($discountPercent / 100));
        $taxableAmount = Money::round($lineSubtotal - $discountAmount);
        $taxAmount = Money::round($taxableAmount * ($taxRate / 100));
        $lineTotal = Money::round($taxableAmount + $taxAmount);

        return [
            'line_subtotal' => $lineSubtotal,
            'discount_amount' => $discountAmount,
            'taxable_amount' => $taxableAmount,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
        ];
    }

    /**
     * @param  array<int, array{quantity: float|int|string, unit_price: float|int|string, discount_percent?: float|int|string, tax_rate?: float|int|string}>  $items
     * @return array{subtotal: float, discount_total: float, tax_total: float, total: float, items: array<int, array<string, float>>}
     */
    public function invoice(array $items, float $invoiceDiscountPercent = 0, float $invoiceDiscountAmount = 0): array
    {
        $calculatedItems = [];
        $subtotal = 0.0;
        $lineDiscountTotal = 0.0;
        $taxTotal = 0.0;
        $itemsNetTotal = 0.0;

        foreach ($items as $item) {
            $line = $this->line(
                (float) ($item['quantity'] ?? 0),
                (float) ($item['unit_price'] ?? 0),
                (float) ($item['discount_percent'] ?? 0),
                (float) ($item['tax_rate'] ?? 0),
            );

            $calculatedItems[] = $line;
            $subtotal = Money::round($subtotal + $line['line_subtotal']);
            $lineDiscountTotal = Money::round($lineDiscountTotal + $line['discount_amount']);
            $taxTotal = Money::round($taxTotal + $line['tax_amount']);
            $itemsNetTotal = Money::round($itemsNetTotal + $line['line_total']);
        }

        $invoicePercentDiscount = Money::round($subtotal * ($invoiceDiscountPercent / 100));
        $discountTotal = Money::round($lineDiscountTotal + $invoicePercentDiscount + $invoiceDiscountAmount);
        $total = Money::round(max(0, $itemsNetTotal - $invoicePercentDiscount - $invoiceDiscountAmount));

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'items' => $calculatedItems,
        ];
    }
}
