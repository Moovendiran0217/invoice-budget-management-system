@component('layouts.app', ['title' => 'New '.$type->label().' invoice'])
    <h1 class="page-title">New {{ strtolower($type->label()) }} invoice</h1>
    <p class="page-subtitle">Tax, discount, and totals are calculated from each line as you type.</p>
    @include('invoices._form', ['action' => route('invoices.store', $type)])
@endcomponent
