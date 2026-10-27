<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ExchangeRateService;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_sales_invoice_with_tax_and_discount_totals(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $product = Product::factory()->create([
            'unit_price' => 1000,
            'tax_rate' => 18,
        ]);

        $response = $this->actingAs($user)->post(route('invoices.store', 'sales'), [
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 0,
            'items' => [[
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => 2,
                'unit_price' => 1000,
                'discount_percent' => 10,
                'tax_rate' => 18,
            ]],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'type' => InvoiceType::Sales->value,
            'customer_id' => $customer->id,
            'subtotal' => 2000,
            'tax_total' => 324,
            'total' => 2124,
            'status' => InvoiceStatus::Pending->value,
        ]);
    }

    #[Test]
    public function recording_full_payment_marks_an_invoice_paid(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        $invoice = app(InvoiceService::class)->create(InvoiceType::Sales, [
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 0,
        ], [[
            'product_id' => null,
            'description' => 'Consulting',
            'quantity' => 1,
            'unit_price' => 1000,
            'discount_percent' => 0,
            'tax_rate' => 0,
        ]], $user->id);

        $this->actingAs($user)->post(route('payments.store', $invoice), [
            'amount' => 1000,
            'payment_date' => now()->toDateString(),
            'method' => PaymentMethod::Upi->value,
            'reference' => 'UPI-1',
        ])->assertRedirect();

        $this->assertSame(InvoiceStatus::Paid, $invoice->fresh()->status);
    }

    #[Test]
    public function payment_cannot_exceed_the_remaining_invoice_balance(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = app(InvoiceService::class)->create(InvoiceType::Sales, [
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 0,
        ], [[
            'product_id' => null,
            'description' => 'Consulting',
            'quantity' => 1,
            'unit_price' => 1000,
            'discount_percent' => 0,
            'tax_rate' => 0,
        ]], $user->id);
        $invoice->payments()->create([
            'amount' => 600,
            'payment_date' => now()->toDateString(),
            'method' => PaymentMethod::Upi,
        ]);

        $this->actingAs($user)->post(route('payments.store', $invoice), [
            'amount' => 401,
            'payment_date' => now()->toDateString(),
            'method' => PaymentMethod::Upi->value,
        ])->assertSessionHasErrors('amount');

        $this->assertSame(400.0, $invoice->fresh('payments')->amountDue());
    }

    #[Test]
    public function dashboard_outstanding_value_subtracts_partial_payments(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = app(InvoiceService::class)->create(InvoiceType::Sales, [
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 0,
        ], [[
            'product_id' => null,
            'description' => 'Consulting',
            'quantity' => 1,
            'unit_price' => 1000,
            'discount_percent' => 0,
            'tax_rate' => 0,
        ]], $user->id);
        $invoice->payments()->create([
            'amount' => 400,
            'payment_date' => now()->toDateString(),
            'method' => PaymentMethod::Upi,
        ]);
        Http::fake(['api.frankfurter.app/*' => Http::response([
            'base' => 'INR',
            'date' => now()->toDateString(),
            'rates' => ['USD' => 0.012],
        ])]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('outstanding', 600.0);
    }

    #[Test]
    public function dashboard_refreshes_pending_invoices_that_have_become_overdue(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $today = now()->startOfDay();
        $this->travelTo($today);
        $invoice = app(InvoiceService::class)->create(InvoiceType::Sales, [
            'customer_id' => $customer->id,
            'invoice_date' => $today->toDateString(),
            'due_date' => $today->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 0,
        ], [[
            'product_id' => null,
            'description' => 'Consulting',
            'quantity' => 1,
            'unit_price' => 1000,
            'discount_percent' => 0,
            'tax_rate' => 0,
        ]], $user->id);
        $this->assertSame(InvoiceStatus::Pending, $invoice->status);
        $this->travelTo($today->copy()->addDay());
        Http::fake(['api.frankfurter.app/*' => Http::response([
            'base' => 'INR',
            'date' => now()->toDateString(),
            'rates' => ['USD' => 0.012],
        ])]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $this->assertSame(InvoiceStatus::Overdue, $invoice->fresh()->status);
    }

    #[Test]
    public function an_expense_cannot_reference_a_sales_invoice(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = app(InvoiceService::class)->create(InvoiceType::Sales, [
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 0,
        ], [[
            'product_id' => null,
            'description' => 'Consulting',
            'quantity' => 1,
            'unit_price' => 1000,
            'discount_percent' => 0,
            'tax_rate' => 0,
        ]], $user->id);

        $this->actingAs($user)->post(route('expenses.store'), [
            'invoice_id' => $invoice->id,
            'category' => 'Office',
            'description' => 'Incorrectly linked expense',
            'amount' => 100,
            'expense_date' => now()->toDateString(),
        ])->assertSessionHasErrors('invoice_id');
    }

    #[Test]
    public function exchange_rate_service_returns_last_known_rates_when_refresh_fails(): void
    {
        Cache::put('exchange-rates.last-known.INR', [
            'base' => 'INR',
            'date' => now()->subDay()->toDateString(),
            'rates' => ['USD' => 0.012],
            'source' => 'Frankfurter API (European Central Bank)',
            'error' => null,
        ], now()->addDays(30));
        Http::fake(['api.frankfurter.app/*' => Http::response([], 503)]);

        $rates = app(ExchangeRateService::class)->latest();

        $this->assertSame(0.012, $rates['rates']['USD']);
        $this->assertNotNull($rates['error']);
    }

    #[Test]
    public function unpaid_invoices_past_due_date_are_overdue(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $invoice = app(InvoiceService::class)->create(InvoiceType::Purchase, [
            'supplier_id' => $supplier->id,
            'invoice_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 0,
        ], [[
            'product_id' => null,
            'description' => 'Supplies',
            'quantity' => 1,
            'unit_price' => 2500,
            'discount_percent' => 0,
            'tax_rate' => 0,
        ]], $user->id);

        $this->assertSame(InvoiceStatus::Overdue, $invoice->fresh()->status);
    }
}
