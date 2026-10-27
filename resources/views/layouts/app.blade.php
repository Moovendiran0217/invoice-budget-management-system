<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} · LedgerDesk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f1ea] font-sans text-slate-900 antialiased">
    <div class="lg:flex">
        <aside class="no-print bg-[#10233f] text-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-64 lg:flex-col">
            <div class="flex items-center justify-between px-5 py-5">
                <div>
                    <p class="text-lg font-semibold">LedgerDesk</p>
                    <p class="text-xs text-slate-400">Invoice & Budget Office</p>
                </div>
                <button type="button" class="rounded-md border border-white/10 px-2 py-1 text-xs lg:hidden" onclick="document.getElementById('nav-links').classList.toggle('hidden')">Menu</button>
            </div>
            <nav id="nav-links" class="hidden space-y-1 px-3 pb-6 lg:block">
                <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="sidebar-link {{ request()->routeIs('invoices.*') && request()->route('type')?->value === 'purchase' ? 'active' : '' }}" href="{{ route('invoices.index', 'purchase') }}">Purchase invoices</a>
                <a class="sidebar-link {{ request()->routeIs('invoices.*') && request()->route('type')?->value === 'sales' ? 'active' : '' }}" href="{{ route('invoices.index', 'sales') }}">Sales invoices</a>
                <a class="sidebar-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}" href="{{ route('budgets.index') }}">Budgets</a>
                <a class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}">Expenses</a>
                <a class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">Customers</a>
                <a class="sidebar-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Suppliers</a>
                <a class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a>
                <a class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">Reports</a>
                <a class="sidebar-link {{ request()->routeIs('exchange-rates.*') ? 'active' : '' }}" href="{{ route('exchange-rates.index') }}">Currency rates</a>
            </nav>
            <div class="mt-auto hidden border-t border-white/10 px-5 py-4 lg:block">
                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-400">{{ auth()->user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button class="text-xs text-teal-200 hover:text-white">Sign out</button>
                </form>
            </div>
        </aside>

        <main class="min-h-screen flex-1 px-4 py-6 sm:px-8">
            @if (session('success'))
                <div class="no-print mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="no-print mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $errors->first() }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
