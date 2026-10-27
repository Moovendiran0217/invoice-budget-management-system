@component('layouts.app', ['title' => 'Add customer'])
    <h1 class="page-title">Add customer</h1>
    @include('parties._form', ['action' => route('customers.store'), 'cancel' => route('customers.index'), 'record' => null])
@endcomponent
