<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoices) {}

    public function index(Request $request, InvoiceType $type): View
    {
        $this->invoices->refreshOverdueStatuses();

        $invoices = Invoice::query()
            ->ofType($type)
            ->with(['customer', 'supplier', 'payments'])
            ->when($request->string('status')->isNotEmpty(), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', $search)
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', $search))
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', $search));
                });
            })
            ->latest('invoice_date')
            ->paginate(12)
            ->withQueryString();

        return view('invoices.index', [
            'invoices' => $invoices,
            'type' => $type,
            'statuses' => InvoiceStatus::cases(),
        ]);
    }

    public function create(InvoiceType $type): View
    {
        return view('invoices.create', $this->formData($type));
    }

    public function store(StoreInvoiceRequest $request, InvoiceType $type): RedirectResponse
    {
        $invoice = $this->invoices->create(
            $type,
            $request->invoiceData(),
            $request->itemsData(),
            $request->user()->id,
        );

        return redirect()
            ->route('invoices.show', [$type, $invoice])
            ->with('success', $type->label().' invoice '.$invoice->invoice_number.' was created.');
    }

    public function show(InvoiceType $type, Invoice $invoice): View
    {
        $this->ensureType($type, $invoice);

        $invoice->load(['items.product', 'customer', 'supplier', 'payments', 'user']);

        return view('invoices.show', [
            'invoice' => $invoice,
            'type' => $type,
        ]);
    }

    public function edit(InvoiceType $type, Invoice $invoice): View
    {
        $this->ensureType($type, $invoice);
        $invoice->load('items');

        return view('invoices.edit', array_merge($this->formData($type), [
            'invoice' => $invoice,
        ]));
    }

    public function update(StoreInvoiceRequest $request, InvoiceType $type, Invoice $invoice): RedirectResponse
    {
        $this->ensureType($type, $invoice);

        $invoice = $this->invoices->update($invoice, $request->invoiceData(), $request->itemsData());

        return redirect()
            ->route('invoices.show', [$type, $invoice])
            ->with('success', 'Invoice '.$invoice->invoice_number.' was updated.');
    }

    public function destroy(InvoiceType $type, Invoice $invoice): RedirectResponse
    {
        $this->ensureType($type, $invoice);
        $invoice->delete();

        return redirect()
            ->route('invoices.index', $type)
            ->with('success', 'Invoice '.$invoice->invoice_number.' was deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(InvoiceType $type): array
    {
        return [
            'type' => $type,
            'customers' => Customer::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function ensureType(InvoiceType $type, Invoice $invoice): void
    {
        abort_unless($invoice->type === $type, 404);
    }
}
