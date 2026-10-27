@component('layouts.app', ['title' => 'Add product'])
    <h1 class="page-title">Add product or service</h1>
    @include('products._form', ['action' => route('products.store'), 'product' => null])
@endcomponent
