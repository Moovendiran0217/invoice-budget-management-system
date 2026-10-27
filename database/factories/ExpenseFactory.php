<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use App\Support\FinanceCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'budget_id' => null,
            'supplier_id' => null,
            'invoice_id' => null,
            'user_id' => User::factory(),
            'category' => fake()->randomElement(FinanceCategories::all()),
            'description' => fake()->sentence(4),
            'amount' => fake()->randomFloat(2, 500, 25000),
            'expense_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'notes' => null,
        ];
    }
}
