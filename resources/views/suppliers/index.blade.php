@component('layouts.app', ['title' => 'Suppliers'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Suppliers</h1>
            <p class="page-subtitle">Vendors used on purchase invoices and expenses.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('suppliers.create') }}">Add supplier</a>
    </div>
    <form class="mt-6" method="GET">
        <input class="field max-w-xs" type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, GSTIN">
    </form>
    <div class="card mt-6 overflow-hidden">
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Name</th><th>Email</th><th>GSTIN</th><th>City</th><th>Invoices</th><th></th></tr></thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="font-medium">{{ $supplier->name }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td>{{ $supplier->gstin }}</td>
                            <td>{{ $supplier->city }}</td>
                            <td>{{ $supplier->invoices_count }}</td>
                            <td class="text-right">
                                <a class="text-sm text-teal-800" href="{{ route('suppliers.edit', $supplier) }}">Edit</a>
                                <form class="inline" method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ml-3 text-sm text-rose-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $suppliers->links() }}</div>
    </div>
@endcomponent
