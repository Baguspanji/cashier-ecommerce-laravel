<?php

use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TransactionSyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Sync API routes - using web middleware for stateful authentication
Route::middleware(['web', 'auth'])->group(function () {
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
