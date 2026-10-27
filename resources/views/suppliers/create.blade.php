@component('layouts.app', ['title' => 'Add supplier'])
    <h1 class="page-title">Add supplier</h1>
    @include('parties._form', ['action' => route('suppliers.store'), 'cancel' => route('suppliers.index'), 'record' => null])
@endcomponent
