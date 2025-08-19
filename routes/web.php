<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('dashboard', [TransactionController::class, 'pos'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    // Products
    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('products.toggle-status');

    // Transactions (Reports Page)
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/daily-report', [TransactionController::class, 'dailyReport'])
        ->name('transactions.daily-report');
    Route::post('transactions/export', [TransactionController::class, 'exportReport'])
        ->name('transactions.export');
    Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::patch('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])
        ->name('transactions.cancel');

    // Stock Management
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('stock/create', [StockController::class, 'create'])->name('stock.create');
    Route::post('stock', [StockController::class, 'store'])->name('stock.store');
    Route::get('stock/overview', [StockController::class, 'overview'])->name('stock.overview');
    Route::get('stock/products/{product}/movements', [StockController::class, 'productMovements'])
        ->name('stock.product-movements');
    Route::post('stock/bulk-adjustment', [StockController::class, 'bulkAdjustment'])
        ->name('stock.bulk-adjustment');

    // User Management
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
