<?php

namespace App\Models;

use App\Enums\BudgetPeriod;
use App\Support\Money;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'period_type', 'year', 'month', 'category', 'allocated_amount', 'notes', 'user_id'])]
class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_type' => BudgetPeriod::class,
            'year' => 'integer',
            'month' => 'integer',
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function spentAmount(): float
    {
        $start = Carbon::create(
            (int) $this->year,
            $this->period_type === BudgetPeriod::Yearly ? 1 : (int) $this->month,
            1,
        )->startOfDay();
        $end = $this->period_type === BudgetPeriod::Yearly
            ? $start->copy()->endOfYear()
            : $start->copy()->endOfMonth();

        return Money::round((float) $this->expenses()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount'));
    }

    public function remainingAmount(): float
    {
        return Money::round((float) $this->allocated_amount - $this->spentAmount());
    }

    public function utilizationPercent(): float
    {
        if ((float) $this->allocated_amount <= 0) {
            return 0;
        }

        return Money::round(($this->spentAmount() / (float) $this->allocated_amount) * 100);
    }

    public function isOverBudget(): bool
    {
        return $this->spentAmount() > (float) $this->allocated_amount;
    }

    public function periodLabel(): string
    {
        if ($this->period_type === BudgetPeriod::Yearly) {
            return (string) $this->year;
        }

        return sprintf('%s %s', date('F', mktime(0, 0, 0, (int) $this->month, 1)), $this->year);
    }
}
