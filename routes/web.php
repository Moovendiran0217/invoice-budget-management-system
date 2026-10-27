<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/reports', ReportController::class)->name('reports.index');
    Route::get('/exchange-rates', ExchangeRateController::class)->name('exchange-rates.index');

    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('budgets', BudgetController::class);
    Route::resource('expenses', ExpenseController::class)->except('show');

    Route::prefix('invoices/{type}')
        ->whereIn('type', ['purchase', 'sales'])
        ->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/create', [InvoiceController::class, 'create'])->name('invoices.create');
            Route::post('/', [InvoiceController::class, 'store'])->name('invoices.store');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
            Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
            Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
            Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        });

    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/invoices/{invoice}/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
});
