@component('layouts.app', ['title' => 'Currency rates'])
    <h1 class="page-title">Currency exchange rates</h1>
    <p class="page-subtitle">Live INR rates from the public Frankfurter API, sourced from the European Central Bank.</p>

    @if ($exchangeRates['error'])
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ $exchangeRates['error'] }}</div>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <article class="card p-5 lg:col-span-2">
            <p class="text-sm text-slate-500">Base currency {{ $exchangeRates['base'] }} · {{ $exchangeRates['date'] ?? 'n/a' }}</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                @forelse ($exchangeRates['rates'] as $code => $rate)
                    <div class="rounded-xl border border-stone-200 px-4 py-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ $code }}</p>
                        <p class="mt-1 text-xl font-semibold">{{ number_format($rate, 6) }}</p>
                        <p class="text-xs text-slate-500">1 INR = {{ number_format($rate, 6) }} {{ $code }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Rates are unavailable right now.</p>
                @endforelse
            </div>
        </article>
        <article class="card p-5">
            <h2 class="font-semibold">Convert from INR</h2>
            <form class="mt-4 space-y-3" onsubmit="event.preventDefault(); convert();">
                <div>
                    <label class="field-label">Amount (INR)</label>
                    <input class="field" id="inr-amount" type="number" min="0" step="0.01" value="1000">
                </div>
                <div>
                    <label class="field-label">To</label>
                    <select class="field" id="target-currency">
                        @foreach ($exchangeRates['rates'] as $code => $rate)
                            <option value="{{ $rate }}" data-code="{{ $code }}">{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Convert</button>
            </form>
            <p class="mt-4 text-2xl font-semibold" id="conversion-result">—</p>
            <p class="mt-2 text-xs text-slate-500">{{ $exchangeRates['source'] }}. Rates are cached for one hour.</p>
        </article>
    </div>
    <script>
        function convert() {
            const amount = Number(document.getElementById('inr-amount').value || 0);
            const select = document.getElementById('target-currency');
            const rate = Number(select.value || 0);
            const code = select.selectedOptions[0]?.dataset.code || '';
            const converted = (amount * rate).toFixed(2);
            document.getElementById('conversion-result').textContent = amount.toLocaleString('en-IN') + ' INR = ' + converted + ' ' + code;
        }
        convert();
    </script>
@endcomponent
