<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\StockApiController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TransactionSyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/login', [AuthController::class, 'login']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/revoke-all-tokens', [AuthController::class, 'revokeAllTokens']);
});

// API CRUD routes - using auth:sanctum for API authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // Categories API
    Route::apiResource('categories', CategoryApiController::class);

    // Products API - specific routes first to avoid conflicts with resource routes
    Route::get('products/search-barcode/{barcode}', [ProductApiController::class, 'searchByBarcode']);
    Route::patch('products/{product}/toggle-status', [ProductApiController::class, 'toggleStatus']);
    Route::apiResource('products', ProductApiController::class);

    // Stock Management API - specific routes first
    Route::get('stock/overview', [StockApiController::class, 'overview']);
    Route::get('stock/alerts', [StockApiController::class, 'alerts']);
    Route::get('stock/products/{product}/movements', [StockApiController::class, 'productMovements']);
    Route::post('stock/bulk-adjustment', [StockApiController::class, 'bulkAdjustment']);
    Route::get('stock', [StockApiController::class, 'index']);
    Route::post('stock', [StockApiController::class, 'store']);
});

// Sync API routes - using auth:sanctum for API authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // General sync endpoints
    Route::prefix('sync')->group(function () {
        Route::get('/status', [SyncController::class, 'status']);
        Route::get('/pending', [SyncController::class, 'pending']);
        Route::post('/complete', [SyncController::class, 'markCompleted']);
        Route::post('/log-failure', [SyncController::class, 'logFailure']);
        Route::get('/history', [SyncController::class, 'history']);
        Route::delete('/cleanup', [SyncController::class, 'cleanup']);
    });

    // Transaction specific sync endpoints
    Route::prefix('transactions')->group(function () {
        Route::post('/sync', [TransactionSyncController::class, 'sync']);
        Route::get('/download', [TransactionSyncController::class, 'download']);
        Route::get('/sync-status', [TransactionSyncController::class, 'status']);
        Route::post('/resolve-conflict', [TransactionSyncController::class, 'resolveConflict']);
    });
});

// Alternative sync routes for web-based authentication (stateful)
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('transactions')->group(function () {
        Route::post('/sync-web', [TransactionSyncController::class, 'sync'])->name('transactions.sync-web');
    });
});
