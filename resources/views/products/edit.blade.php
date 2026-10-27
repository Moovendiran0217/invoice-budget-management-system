@component('layouts.app', ['title' => 'Edit product'])
    <h1 class="page-title">Edit {{ $product->name }}</h1>
    @include('products._form', ['action' => route('products.update', $product), 'method' => 'PUT', 'product' => $product])
@endcomponent
