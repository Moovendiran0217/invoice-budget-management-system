<?php

namespace App\Http\Requests;

use App\Enums\InvoiceType;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
        $type = $this->invoiceType();

        return [
            'customer_id' => [$type === InvoiceType::Sales ? 'required' : 'nullable', 'exists:customers,id'],
            'supplier_id' => [$type === InvoiceType::Purchase ? 'required' : 'nullable', 'exists:suppliers,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function invoiceType(): InvoiceType
    {
        $type = $this->route('type');

        if ($type instanceof InvoiceType) {
            return $type;
        }

        return InvoiceType::from((string) $type);
    }

    /**
     * @return array<string, mixed>
     */
    public function invoiceData(): array
    {
        return [
            'customer_id' => $this->input('customer_id'),
            'supplier_id' => $this->input('supplier_id'),
            'invoice_date' => $this->input('invoice_date'),
            'due_date' => $this->input('due_date'),
            'discount_percent' => $this->input('discount_percent', 0) ?: 0,
            'discount_amount' => $this->input('discount_amount', 0) ?: 0,
            'notes' => $this->input('notes'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function itemsData(): array
    {
        return collect($this->input('items', []))
            ->map(fn (array $item) => [
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'tax_rate' => $item['tax_rate'] ?? 0,
            ])
            ->values()
            ->all();
    }
}
