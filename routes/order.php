<?php

use App\Http\Controllers\OrderController;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', [OrderController::class, 'index'])->name('dashboard');
    Route::resource('orders', OrderController::class)->only(['index','create','store','show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

});

