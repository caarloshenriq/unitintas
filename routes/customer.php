<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'permission:1'])->group(function () {
    Route::get('/clientes', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/clientes/new', [CustomerController::class, 'store'])->name('customer.store');
    Route::put('/clientes/{id}', [CustomerController::class, 'update'])->name('customer.update');
    Route::delete('/clientes/{id}', [CustomerController::class, 'destroy'])->name('customer.destroy');
});
