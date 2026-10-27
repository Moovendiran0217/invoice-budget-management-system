<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'invoice_number' => 'SINV-'.now()->year.'-'.fake()->unique()->numerify('####'),
            'type' => InvoiceType::Sales,
            'customer_id' => Customer::factory(),
            'supplier_id' => null,
            'user_id' => User::factory(),
            'invoice_date' => $date,
            'due_date' => (clone $date)->modify('+14 days'),
            'status' => InvoiceStatus::Pending,
            'subtotal' => 0,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'total' => 0,
            'notes' => null,
        ];
    }

    public function purchase(): static
    {
        return $this->state(fn () => [
            'invoice_number' => 'PINV-'.now()->year.'-'.fake()->unique()->numerify('####'),
            'type' => InvoiceType::Purchase,
            'customer_id' => null,
            'supplier_id' => Supplier::factory(),
        ]);
    }
}
