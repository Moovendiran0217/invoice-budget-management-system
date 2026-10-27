@component('layouts.app', ['title' => 'Customers'])
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Customers</h1>
            <p class="page-subtitle">People and companies billed on sales invoices.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('customers.create') }}">Add customer</a>
    </div>
    <form class="mt-6" method="GET">
        <input class="field max-w-xs" type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, GSTIN">
    </form>
    <div class="card mt-6 overflow-hidden">
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Name</th><th>Email</th><th>GSTIN</th><th>City</th><th>Invoices</th><th></th></tr></thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="font-medium">{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->gstin }}</td>
                            <td>{{ $customer->city }}</td>
                            <td>{{ $customer->invoices_count }}</td>
                            <td class="text-right">
                                <a class="text-sm text-teal-800" href="{{ route('customers.edit', $customer) }}">Edit</a>
                                <form class="inline" method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ml-3 text-sm text-rose-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $customers->links() }}</div>
    </div>
@endcomponent
