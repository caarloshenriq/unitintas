<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', [OrderController::class, 'index'])->name('dashboard');
    Route::resource('orders', OrderController::class)->only(['index','create','store','show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('/orders/{order}/payment', [PaymentController::class, 'create'])->name('orders.payment.create');
    Route::post('/orders/{order}/payment', [PaymentController::class, 'store'])->name('orders.payment.store');
});

