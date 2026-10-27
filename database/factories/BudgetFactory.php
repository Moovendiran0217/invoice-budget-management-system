<?php

namespace Database\Factories;

use App\Enums\BudgetPeriod;
use App\Models\Budget;
use App\Models\User;
use App\Support\FinanceCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' budget',
            'period_type' => BudgetPeriod::Monthly,
            'year' => now()->year,
            'month' => now()->month,
            'category' => fake()->randomElement(FinanceCategories::all()),
            'allocated_amount' => fake()->randomFloat(2, 20000, 200000),
            'notes' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
