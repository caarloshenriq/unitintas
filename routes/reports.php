<?php

use App\Http\Controllers\ReportsController;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    // (opcional) exports em CSV
    Route::get('/reports/export/transactions', [ReportsController::class, 'exportTransactions'])->name('reports.export.transactions');
    Route::get('/reports/export/orders', [ReportsController::class, 'exportOrders'])->name('reports.export.orders');
});