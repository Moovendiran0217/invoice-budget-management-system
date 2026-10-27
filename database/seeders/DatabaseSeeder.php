<?php

namespace Database\Seeders;

use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Models\Budget;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@ledger.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $customers = Customer::factory()->count(6)->create();
        $suppliers = Supplier::factory()->count(5)->create();

        $catalog = [
            ['Office Chair', 'PROD-1001', ProductType::Product, 7499, 18],
            ['Standing Desk', 'PROD-1002', ProductType::Product, 18999, 18],
            ['A4 Paper Ream', 'PROD-1003', ProductType::Product, 420, 12],
            ['Bookkeeping Service', 'SRV-2001', ProductType::Service, 12000, 18],
            ['Cloud Hosting', 'SRV-2002', ProductType::Service, 3500, 18],
            ['Warehouse Handling', 'SRV-2003', ProductType::Service, 8500, 5],
        ];

        $products = collect($catalog)->map(fn (array $item) => Product::query()->create([
            'name' => $item[0],
            'sku' => $item[1],
            'type' => $item[2],
            'unit_price' => $item[3],
            'tax_rate' => $item[4],
            'description' => $item[0].' listed in the office catalog.',
            'is_active' => true,
        ]));

        $invoices = app(InvoiceService::class);

        $sales = $invoices->create(InvoiceType::Sales, [
            'customer_id' => $customers[0]->id,
            'invoice_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(6)->toDateString(),
            'discount_percent' => 2,
            'discount_amount' => 0,
            'notes' => 'Q3 furniture refresh',
        ], [
            ['product_id' => $products[0]->id, 'description' => $products[0]->name, 'quantity' => 4, 'unit_price' => 7499, 'discount_percent' => 5, 'tax_rate' => 18],
            ['product_id' => $products[1]->id, 'description' => $products[1]->name, 'quantity' => 2, 'unit_price' => 18999, 'discount_percent' => 0, 'tax_rate' => 18],
        ], $admin->id);

        $sales->payments()->create([
            'amount' => 15000,
            'payment_date' => now()->subDays(10)->toDateString(),
            'method' => PaymentMethod::BankTransfer,
            'reference' => 'NEFT-10441',
        ]);
        $invoices->recalculate($sales);

        $paidSales = $invoices->create(InvoiceType::Sales, [
            'customer_id' => $customers[1]->id,
            'invoice_date' => now()->subDays(8)->toDateString(),
            'due_date' => now()->addDays(6)->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 500,
            'notes' => 'Monthly bookkeeping',
        ], [
            ['product_id' => $products[3]->id, 'description' => $products[3]->name, 'quantity' => 1, 'unit_price' => 12000, 'discount_percent' => 0, 'tax_rate' => 18],
        ], $admin->id);

        $paidSales->payments()->create([
            'amount' => $paidSales->fresh()->total,
            'payment_date' => now()->subDays(1)->toDateString(),
            'method' => PaymentMethod::Upi,
            'reference' => 'UPI-8821',
        ]);
        $invoices->recalculate($paidSales);

        $purchase = $invoices->create(InvoiceType::Purchase, [
            'supplier_id' => $suppliers[0]->id,
            'invoice_date' => now()->subDays(12)->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'discount_percent' => 0,
            'discount_amount' => 0,
            'notes' => 'Stationery restock',
        ], [
            ['product_id' => $products[2]->id, 'description' => $products[2]->name, 'quantity' => 40, 'unit_price' => 420, 'discount_percent' => 0, 'tax_rate' => 12],
            ['product_id' => $products[5]->id, 'description' => $products[5]->name, 'quantity' => 1, 'unit_price' => 8500, 'discount_percent' => 0, 'tax_rate' => 5],
        ], $admin->id);

        $officeBudget = Budget::query()->create([
            'name' => 'October office spend',
            'period_type' => 'monthly',
            'year' => now()->year,
            'month' => now()->month,
            'category' => 'Office',
            'allocated_amount' => 50000,
            'notes' => 'Furniture, stationery, and workspace tools.',
            'user_id' => $admin->id,
        ]);

        $softwareBudget = Budget::query()->create([
            'name' => now()->year.' software licences',
            'period_type' => 'yearly',
            'year' => now()->year,
            'month' => null,
            'category' => 'Software',
            'allocated_amount' => 120000,
            'notes' => 'Hosting, SaaS, and accounting tools.',
            'user_id' => $admin->id,
        ]);

        Expense::query()->create([
            'budget_id' => $officeBudget->id,
            'supplier_id' => $suppliers[0]->id,
            'invoice_id' => $purchase->id,
            'user_id' => $admin->id,
            'category' => 'Office',
            'description' => 'Stationery and handling from purchase invoice',
            'amount' => $purchase->total,
            'expense_date' => $purchase->invoice_date,
        ]);

        Expense::query()->create([
            'budget_id' => $softwareBudget->id,
            'user_id' => $admin->id,
            'category' => 'Software',
            'description' => 'Cloud hosting subscription',
            'amount' => 3500,
            'expense_date' => now()->subDays(4)->toDateString(),
        ]);

        Expense::query()->create([
            'budget_id' => $officeBudget->id,
            'user_id' => $admin->id,
            'category' => 'Utilities',
            'description' => 'Office electricity',
            'amount' => 8200,
            'expense_date' => now()->subDays(2)->toDateString(),
        ]);
    }
}
