<?php

namespace Tests\Feature;

use App\Enums\BudgetPeriod;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BudgetExpenseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function expenses_reduce_remaining_budget_and_can_push_it_over(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'period_type' => BudgetPeriod::Monthly,
            'allocated_amount' => 10000,
            'category' => 'Office',
        ]);

        $this->actingAs($user)->post(route('expenses.store'), [
            'budget_id' => $budget->id,
            'category' => 'Office',
            'description' => 'Desks',
            'amount' => 6500,
            'expense_date' => now()->toDateString(),
        ])->assertRedirect(route('expenses.index'));

        $budget->refresh()->load('expenses');

        $this->assertSame(6500.0, $budget->spentAmount());
        $this->assertSame(3500.0, $budget->remainingAmount());
        $this->assertFalse($budget->isOverBudget());

        Expense::factory()->create([
            'budget_id' => $budget->id,
            'user_id' => $user->id,
            'amount' => 4000,
            'category' => 'Office',
            'expense_date' => now()->toDateString(),
        ]);

        $budget->refresh()->load('expenses');

        $this->assertTrue($budget->isOverBudget());
        $this->assertSame(-500.0, $budget->remainingAmount());
        $this->assertSame(105.0, $budget->utilizationPercent());
    }

    #[Test]
    public function monthly_budget_spending_excludes_expenses_outside_its_month(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'period_type' => BudgetPeriod::Monthly,
            'year' => now()->year,
            'month' => now()->month,
            'allocated_amount' => 10000,
        ]);

        Expense::factory()->create([
            'budget_id' => $budget->id,
            'user_id' => $user->id,
            'amount' => 2500,
            'expense_date' => now()->startOfMonth()->toDateString(),
        ]);
        Expense::factory()->create([
            'budget_id' => $budget->id,
            'user_id' => $user->id,
            'amount' => 4000,
            'expense_date' => now()->subMonth()->toDateString(),
        ]);

        $this->assertSame(2500.0, $budget->spentAmount());
    }
}
