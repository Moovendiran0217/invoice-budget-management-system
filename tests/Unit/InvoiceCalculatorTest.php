<?php

namespace Tests\Unit;

use App\Services\InvoiceCalculator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    #[Test]
    public function it_calculates_line_tax_and_discount(): void
    {
        $line = (new InvoiceCalculator)->line(2, 1000, 10, 18);

        $this->assertSame(2000.0, $line['line_subtotal']);
        $this->assertSame(200.0, $line['discount_amount']);
        $this->assertSame(1800.0, $line['taxable_amount']);
        $this->assertSame(324.0, $line['tax_amount']);
        $this->assertSame(2124.0, $line['line_total']);
    }

    #[Test]
    public function it_applies_invoice_level_discounts_after_line_totals(): void
    {
        $result = (new InvoiceCalculator)->invoice([
            ['quantity' => 2, 'unit_price' => 1000, 'discount_percent' => 10, 'tax_rate' => 18],
            ['quantity' => 1, 'unit_price' => 500, 'discount_percent' => 0, 'tax_rate' => 0],
        ], 5, 100);

        $this->assertSame(2500.0, $result['subtotal']);
        $this->assertSame(425.0, $result['discount_total']);
        $this->assertSame(324.0, $result['tax_total']);
        $this->assertSame(2399.0, $result['total']);
    }
}
