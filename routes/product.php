<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
    Route::post('/produtos/new', [ProductController::class, 'store'])->name('product.store');
    Route::put('/produtos/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/produtos/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
});
