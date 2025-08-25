<?php

use App\Http\Controllers\TransactionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/finance/{type}', [TransactionController::class, 'index'])
        ->whereIn('type', ['receivable', 'payable'])
        ->name('finance.index');

    Route::post('/finance', [TransactionController::class, 'store'])->name('finance.store');

    Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])
        ->name('transactions.updateStatus');

    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])
        ->name('transactions.destroy');
});
