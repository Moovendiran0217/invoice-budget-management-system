@component('layouts.app', ['title' => 'Products'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Products & services</h1>
            <p class="page-subtitle">Catalog items used on invoice lines, including default tax rates.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('products.create') }}">Add product</a>
    </div>
    <form class="mt-6" method="GET">
        <input class="field max-w-xs" type="search" name="search" value="{{ request('search') }}" placeholder="Search name or SKU">
    </form>
    <div class="card mt-6 overflow-hidden">
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Name</th><th>SKU</th><th>Type</th><th>Price</th><th>Tax</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="font-medium">{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->type->label() }}</td>
                            <td>@money($product->unit_price)</td>
                            <td>{{ $product->tax_rate }}%</td>
                            <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                            <td class="text-right">
                                <a class="text-sm text-teal-800" href="{{ route('products.edit', $product) }}">Edit</a>
                                <form class="inline" method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ml-3 text-sm text-rose-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $products->links() }}</div>
    </div>
@endcomponent
