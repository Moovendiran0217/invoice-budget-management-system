<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sign in' }} · LedgerDesk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#10233f] font-sans antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        <section class="relative hidden overflow-hidden px-12 py-16 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(13,148,136,0.35),_transparent_42%),radial-gradient(circle_at_bottom_right,_rgba(180,83,9,0.28),_transparent_36%)]"></div>
            <div class="relative">
                <p class="text-sm uppercase tracking-[0.3em] text-teal-200">Invoice & Budget System</p>
                <h1 class="mt-6 max-w-md text-4xl font-semibold leading-tight">Keep invoices, budgets, and expenses in one ledger.</h1>
            </div>
            <p class="relative max-w-sm text-sm leading-6 text-slate-300">
                Track purchase and sales invoices, match spend against monthly and yearly budgets, and watch payment status as it moves from pending to paid.
            </p>
        </section>
        <section class="flex items-center justify-center bg-[#f4f1ea] px-6 py-12">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </section>
    </div>
</body>
</html>
