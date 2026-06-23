<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('invoices.create'));

Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
    ->whereNumber('invoice')
    ->name('invoices.show');
Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
    ->whereNumber('invoice')
    ->name('invoices.pdf');

Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
