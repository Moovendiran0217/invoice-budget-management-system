@component('layouts.guest', ['title' => 'Admin login'])
    <div class="card p-8">
        <p class="text-sm uppercase tracking-[0.25em] text-teal-800">Admin access</p>
        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Sign in to LedgerDesk</h2>
        <p class="mt-2 text-sm text-slate-500">Use the seeded administrator account to manage invoices, budgets, and expenses.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="field-label" for="email">Email</label>
                <input id="email" class="field" type="email" name="email" value="{{ old('email', 'admin@ledger.test') }}" required autofocus>
            </div>
            <div>
                <label class="field-label" for="password">Password</label>
                <input id="password" class="field" type="password" name="password" required>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-stone-300 text-teal-700">
                Remember me
            </label>
            <button class="btn btn-primary w-full">Sign in</button>
        </form>
        <p class="mt-5 text-xs text-slate-500">Demo login: admin@ledger.test / password</p>
    </div>
@endcomponent
