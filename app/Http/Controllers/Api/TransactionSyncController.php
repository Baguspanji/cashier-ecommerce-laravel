<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\SyncLog;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionSyncController extends Controller
{
    /**
     * Sync transactions from offline storage
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'transactions' => 'required|array',
            'transactions.*.offline_id' => 'required|string',
            'transactions.*.customer_name' => 'nullable|string|max:255',
            'transactions.*.total_amount' => 'required|numeric|min:0',
            'transactions.*.payment_method' => 'required|in:cash,bank_transfer,e_wallet,debit,credit,e-wallet',
            'transactions.*.items' => 'required|array|min:1',
            'transactions.*.items.*.product_id' => 'required|exists:products,id',
            'transactions.*.items.*.quantity' => 'required|integer|min:1',
            'transactions.*.items.*.price' => 'required|numeric|min:0',
            'transactions.*.created_at' => 'required|date',
        ]);

        $user = Auth::user();
        $results = [];

        DB::transaction(function () use ($request, $user, &$results) {
            foreach ($request->transactions as $transactionData) {
                try {
                    // Check if this offline transaction was already synced
                    $existingTransaction = Transaction::where('offline_id', $transactionData['offline_id'])
                        ->where('user_id', $user->id)
                        ->first();

                    if ($existingTransaction) {
                        $results[] = [
                            'offline_id' => $transactionData['offline_id'],
                            'status' => 'skipped',
                            'message' => 'Transaction already synced',
                            'transaction_id' => $existingTransaction->id,
                        ];

                        continue;
                    }

                    // Map payment method from frontend to backend format
                    $paymentMethodMapping = [
                        'cash' => 'cash',
                        'bank_transfer' => 'debit',
                        'e_wallet' => 'e-wallet',
                        'e-wallet' => 'e-wallet',
                        'debit' => 'debit',
                        'credit' => 'credit',
                    ];

                    $mappedPaymentMethod = $paymentMethodMapping[$transactionData['payment_method']] ?? $transactionData['payment_method'];

                    // Create the transaction
                    $transaction = Transaction::create([
                        'user_id' => $user->id,
                        'customer_name' => $transactionData['customer_name'],
                        'total_amount' => $transactionData['total_amount'],
                        'payment_method' => $mappedPaymentMethod,
                        'offline_id' => $transactionData['offline_id'],
                        'sync_status' => 'synced',
                        'last_sync_at' => now(),
                        'created_at' => $transactionData['created_at'],
                    ]);

                    // Create transaction items and update stock
                    foreach ($transactionData['items'] as $itemData) {
                        $product = Product::findOrFail($itemData['product_id']);

                        // Create transaction item
                        $transactionItem = TransactionItem::create([
                            'transaction_id' => $transaction->id,
                            'product_id' => $product->id,
                            'quantity' => $itemData['quantity'],
                            'price' => $itemData['price'],
                            'offline_id' => $itemData['offline_id'] ?? null,
                            'sync_status' => 'synced',
                            'last_sync_at' => now(),
                        ]);

                        // Update product stock
                        $product->decrement('stock', $itemData['quantity']);

                        // Create stock movement
                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => $user->id,
                            'type' => 'sale',
                            'quantity' => $itemData['quantity'],
                            'notes' => "Sale - Transaction #{$transaction->id}",
                            'offline_id' => $itemData['stock_movement_offline_id'] ?? null,
                            'sync_status' => 'synced',
                            'last_sync_at' => now(),
                        ]);
                    }

                    // Log successful sync
                    SyncLog::create([
                        'user_id' => $user->id,
                        'operation' => 'create',
                        'table_name' => 'transactions',
                        'record_id' => $transaction->id,
                        'offline_id' => $transactionData['offline_id'],
                        'data' => $transactionData,
                        'status' => 'completed',
                        'synced_at' => now(),
                    ]);

                    $results[] = [
                        'offline_id' => $transactionData['offline_id'],
                        'status' => 'success',
                        'transaction_id' => $transaction->id,
                        'message' => 'Transaction synced successfully',
                    ];

                } catch (\Exception $e) {
                    // Log failed sync
                    SyncLog::create([
                        'user_id' => $user->id,
                        'operation' => 'create',
                        'table_name' => 'transactions',
                        'offline_id' => $transactionData['offline_id'],
                        'data' => $transactionData,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);

                    $results[] = [
                        'offline_id' => $transactionData['offline_id'],
                        'status' => 'failed',
                        'message' => $e->getMessage(),
                    ];
                }
            }
        });

        $successCount = collect($results)->where('status', 'success')->count();
        $failedCount = collect($results)->where('status', 'failed')->count();
        $skippedCount = collect($results)->where('status', 'skipped')->count();

        return response()->json([
            'status' => 'success',
            'message' => "Sync completed: {$successCount} success, {$failedCount} failed, {$skippedCount} skipped",
            'summary' => [
                'total' => count($results),
                'success' => $successCount,
                'failed' => $failedCount,
                'skipped' => $skippedCount,
            ],
            'results' => $results,
        ]);
    }

    /**
     * Get transactions that need to be synced to offline storage
     */
    public function download(Request $request): JsonResponse
    {
        $request->validate([
            'last_sync_at' => 'nullable|date',
            'limit' => 'integer|min:1|max:1000',
        ]);

        $user = Auth::user();
        $lastSyncAt = $request->get('last_sync_at');
        $limit = $request->get('limit', 100);

        $query = Transaction::with(['items.product'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($lastSyncAt) {
            $query->where('updated_at', '>', $lastSyncAt);
        }

        $transactions = $query->get()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'offline_id' => $transaction->offline_id,
                'customer_name' => $transaction->customer_name,
                'total_amount' => $transaction->total_amount,
                'payment_method' => $transaction->payment_method,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
                'items' => $transaction->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'offline_id' => $item->offline_id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
            'sync_timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get sync status for transactions
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'pending_upload' => SyncLog::forUser($user->id)
                ->where('table_name', 'transactions')
                ->pending()
                ->count(),
            'last_upload' => SyncLog::forUser($user->id)
                ->where('table_name', 'transactions')
                ->completed()
                ->latest('synced_at')
                ->value('synced_at'),
            'total_transactions' => Transaction::where('user_id', $user->id)->count(),
            'offline_transactions' => Transaction::where('user_id', $user->id)
                ->whereNotNull('offline_id')
                ->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }

    /**
     * Resolve sync conflicts for transactions
     */
    public function resolveConflict(Request $request): JsonResponse
    {
        $request->validate([
            'offline_id' => 'required|string',
            'resolution' => 'required|in:keep_server,keep_client,merge',
            'client_data' => 'required_if:resolution,keep_client,merge|array',
        ]);

        $user = Auth::user();

        $serverTransaction = Transaction::where('offline_id', $request->offline_id)
            ->where('user_id', $user->id)
            ->first();

        if (! $serverTransaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found on server',
            ], 404);
        }

        DB::transaction(function () use ($request, $user, $serverTransaction) {
            switch ($request->resolution) {
                case 'keep_server':
                    // No action needed, server version is kept
                    SyncLog::create([
                        'user_id' => $user->id,
                        'operation' => 'conflict_resolved',
                        'table_name' => 'transactions',
                        'record_id' => $serverTransaction->id,
                        'offline_id' => $request->offline_id,
                        'data' => ['resolution' => 'keep_server'],
                        'status' => 'completed',
                        'synced_at' => now(),
                    ]);
                    break;

                case 'keep_client':
                    // Update server with client data
                    $serverTransaction->update($request->client_data);
                    $serverTransaction->update([
                        'sync_status' => 'synced',
                        'last_sync_at' => now(),
                    ]);

                    SyncLog::create([
                        'user_id' => $user->id,
                        'operation' => 'conflict_resolved',
                        'table_name' => 'transactions',
                        'record_id' => $serverTransaction->id,
                        'offline_id' => $request->offline_id,
                        'data' => ['resolution' => 'keep_client', 'client_data' => $request->client_data],
                        'status' => 'completed',
                        'synced_at' => now(),
                    ]);
                    break;

                case 'merge':
                    // Implement merge logic based on business rules
                    // For now, keeping server version and logging the merge attempt
                    SyncLog::create([
                        'user_id' => $user->id,
                        'operation' => 'conflict_resolved',
                        'table_name' => 'transactions',
                        'record_id' => $serverTransaction->id,
                        'offline_id' => $request->offline_id,
                        'data' => ['resolution' => 'merge', 'client_data' => $request->client_data],
                        'status' => 'completed',
                        'synced_at' => now(),
                    ]);
                    break;
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Conflict resolved successfully',
            'resolution' => $request->resolution,
        ]);
    }
}
