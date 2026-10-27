@component('layouts.app', ['title' => 'Edit '.$invoice->invoice_number])
    <h1 class="page-title">Edit {{ $invoice->invoice_number }}</h1>
    <p class="page-subtitle">Updating line items recalculates tax, discounts, and payment status.</p>
    @include('invoices._form', ['action' => route('invoices.update', [$type, $invoice]), 'method' => 'PUT', 'invoice' => $invoice])
@endcomponent
