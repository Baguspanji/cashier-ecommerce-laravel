import { ref, onMounted, onUnmounted } from 'vue';
import { offlineSyncManager, type OfflineTransaction, type SyncResult } from '@/offlineSync';

export function useOfflineSync() {
    const pendingCount = ref(0);
    const isOnline = ref(navigator.onLine);
    const isSyncing = ref(false);
    const lastSyncResult = ref<SyncResult | null>(null);
    const syncError = ref<string | null>(null);

    // Update online status
    const updateOnlineStatus = () => {
        isOnline.value = navigator.onLine;
        if (isOnline.value) {
            // Auto-sync when coming back online
            syncPendingTransactions();
        }
    };

  // Store transaction offline
  const storeOfflineTransaction = async (transaction: Omit<OfflineTransaction, 'offline_id' | 'stored_at'>) => {
    try {
      console.log('Storing offline transaction:', transaction);
      const offline_id = await offlineSyncManager.storeTransaction(transaction);
      await updatePendingCount();

      // Try to sync immediately if online
      if (isOnline.value) {
        console.log('Online detected, attempting immediate sync...');
        syncPendingTransactions();
      }

      return offline_id;
    } catch (error) {
      console.error('Failed to store offline transaction:', error);
      throw error;
    }
  };    // Sync pending transactions
    const syncPendingTransactions = async (): Promise<SyncResult | null> => {
        if (isSyncing.value || !isOnline.value) {
            console.log('Sync skipped - already syncing or offline');
            return null;
        }

        try {
            isSyncing.value = true;
            syncError.value = null;

            console.log('Starting sync of pending transactions...');
            const result = await offlineSyncManager.forceSync();
            lastSyncResult.value = result;

            if (result.success) {
                await updatePendingCount();
                if (result.errors.length > 0) {
                    syncError.value = `Partially synced: ${result.errors.join(', ')}`;
                }
                console.log('Sync completed successfully:', result);
            } else {
                syncError.value = result.errors.join(', ');
                console.error('Sync failed:', result.errors);
            }

            return result;
        } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'Unknown sync error';
            syncError.value = errorMessage;
            console.error('Sync failed:', error);
            return null;
        } finally {
            isSyncing.value = false;
        }
    };

    // Update pending count
    const updatePendingCount = async () => {
        try {
            pendingCount.value = await offlineSyncManager.getPendingCount();
        } catch (error) {
            console.error('Failed to get pending count:', error);
        }
    };

    // Get pending transactions
    const getPendingTransactions = async () => {
        try {
            return await offlineSyncManager.getPendingTransactions();
        } catch (error) {
            console.error('Failed to get pending transactions:', error);
            return [];
        }
    };

    // Clear all offline data
    const clearOfflineData = async () => {
        try {
            await offlineSyncManager.clearAllData();
            await updatePendingCount();
        } catch (error) {
            console.error('Failed to clear offline data:', error);
            throw error;
        }
    };

    // Setup sync event listeners
    const setupSyncListeners = () => {
        offlineSyncManager.setupSyncListener(
            (data) => {
                // Sync completed in background
                updatePendingCount();
                lastSyncResult.value = {
                    success: true,
                    syncedCount: data.syncedCount || 0,
                    failedCount: 0,
                    errors: []
                };
            },
            (error) => {
                // Sync error in background
                syncError.value = error.message || 'Background sync failed';
            }
        );
    };

    // Lifecycle hooks
    onMounted(() => {
        // Update pending count on mount
        updatePendingCount();

        // Setup listeners
        setupSyncListeners();

        // Listen for online/offline events
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
    });

    onUnmounted(() => {
        window.removeEventListener('online', updateOnlineStatus);
        window.removeEventListener('offline', updateOnlineStatus);
    });

    return {
        // Reactive state
        pendingCount,
        isOnline,
        isSyncing,
        lastSyncResult,
        syncError,

        // Methods
        storeOfflineTransaction,
        syncPendingTransactions,
        getPendingTransactions,
        clearOfflineData,
        updatePendingCount,
    };
}
