<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    /**
     * Get sync status for the authenticated user
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();

        $pendingCount = SyncLog::forUser($user->id)->pending()->count();
        $lastSync = SyncLog::forUser($user->id)
            ->completed()
            ->latest('synced_at')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'pending_operations' => $pendingCount,
                'last_sync_at' => $lastSync?->synced_at,
                'sync_required' => $pendingCount > 0,
            ],
        ]);
    }

    /**
     * Get pending sync operations for the user
     */
    public function pending(): JsonResponse
    {
        $user = Auth::user();

        $pendingOperations = SyncLog::forUser($user->id)
            ->pending()
            ->orderBy('created_at')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'operation' => $log->operation,
                    'table_name' => $log->table_name,
                    'record_id' => $log->record_id,
                    'offline_id' => $log->offline_id,
                    'data' => $log->data,
                    'created_at' => $log->created_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $pendingOperations,
        ]);
    }

    /**
     * Mark sync operations as completed
     */
    public function markCompleted(Request $request): JsonResponse
    {
        $request->validate([
            'sync_ids' => 'required|array',
            'sync_ids.*' => 'integer|exists:sync_logs,id',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {
            SyncLog::whereIn('id', $request->sync_ids)
                ->forUser($user->id)
                ->pending()
                ->update([
                    'status' => 'completed',
                    'synced_at' => now(),
                ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Sync operations marked as completed',
        ]);
    }

    /**
     * Log a sync operation failure
     */
    public function logFailure(Request $request): JsonResponse
    {
        $request->validate([
            'sync_id' => 'required|integer|exists:sync_logs,id',
            'error_message' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        $syncLog = SyncLog::where('id', $request->sync_id)
            ->forUser($user->id)
            ->firstOrFail();

        $syncLog->update([
            'status' => 'failed',
            'error_message' => $request->error_message,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sync failure logged',
        ]);
    }

    /**
     * Get sync history for debugging
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 50);

        $history = SyncLog::forUser($user->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'operation' => $log->operation,
                    'table_name' => $log->table_name,
                    'status' => $log->status,
                    'error_message' => $log->error_message,
                    'created_at' => $log->created_at,
                    'synced_at' => $log->synced_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }

    /**
     * Clear completed sync logs older than specified days
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:1|max:365',
        ]);

        $user = Auth::user();
        $days = $request->get('days', 30);

        $deletedCount = SyncLog::forUser($user->id)
            ->completed()
            ->where('synced_at', '<', now()->subDays($days))
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Cleaned up {$deletedCount} old sync logs",
            'deleted_count' => $deletedCount,
        ]);
    }
}
