<?php

namespace App\Http\Requests;

use App\Enums\InvoiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'budget_id' => ['nullable', 'exists:budgets,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'invoice_id' => [
                'nullable',
                Rule::exists('invoices', 'id')->where('type', InvoiceType::Purchase->value),
            ],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
