@component('layouts.app', ['title' => 'Edit supplier'])
    <h1 class="page-title">Edit {{ $supplier->name }}</h1>
    @include('parties._form', ['action' => route('suppliers.update', $supplier), 'method' => 'PUT', 'cancel' => route('suppliers.index'), 'record' => $supplier])
@endcomponent
