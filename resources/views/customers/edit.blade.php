@component('layouts.app', ['title' => 'Edit customer'])
    <h1 class="page-title">Edit {{ $customer->name }}</h1>
    @include('parties._form', ['action' => route('customers.update', $customer), 'method' => 'PUT', 'cancel' => route('customers.index'), 'record' => $customer])
@endcomponent
