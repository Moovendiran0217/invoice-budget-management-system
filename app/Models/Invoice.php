<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Support\Money;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_number',
    'type',
    'customer_id',
    'supplier_id',
    'user_id',
    'invoice_date',
    'due_date',
    'status',
    'subtotal',
    'discount_percent',
    'discount_amount',
    'discount_total',
    'tax_total',
    'total',
    'notes',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function partyName(): string
    {
        if ($this->type === InvoiceType::Sales) {
            return $this->customer?->name ?? 'Walk-in customer';
        }

        return $this->supplier?->name ?? 'Unknown supplier';
    }

    public function amountPaid(): float
    {
        return Money::round($this->payments->sum('amount'));
    }

    public function amountDue(): float
    {
        return Money::round(max(0, (float) $this->total - $this->amountPaid()));
    }

    public function scopeOfType(Builder $query, InvoiceType $type): Builder
    {
        return $query->where('type', $type);
    }
}
