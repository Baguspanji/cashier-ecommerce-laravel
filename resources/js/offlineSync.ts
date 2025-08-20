// Offline Sync Manager
// Handles offline transaction storage and synchronization

export interface OfflineTransaction {
    offline_id: string;
    customer_name?: string;
    total_amount: number;
    payment_method: 'cash' | 'bank_transfer' | 'e_wallet';
    payment_amount: number;
    change_amount: number;
    items: Array<{
        offline_id?: string;
        product_id: number;
        quantity: number;
        price: number;
    }>;
    created_at: string;
    stored_at?: string;
}

export interface SyncResult {
    success: boolean;
    syncedCount: number;
    failedCount: number;
    errors: string[];
}

export class OfflineSyncManager {
    private dbName = 'CashierSyncDB';
    private dbVersion = 1;
    private db: IDBDatabase | null = null;

    constructor() {
        this.initDB();
    }

    private async initDB(): Promise<void> {
        try {
            this.db = await this.openDB();
        } catch (error) {
            console.error('Failed to initialize offline sync database:', error);
        }
    }

    private openDB(): Promise<IDBDatabase> {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);

            request.onupgradeneeded = (event) => {
                const db = (event.target as IDBOpenDBRequest).result;

                // Create object stores
                if (!db.objectStoreNames.contains('pending_transactions')) {
                    const store = db.createObjectStore('pending_transactions', { keyPath: 'offline_id' });
                    store.createIndex('timestamp', 'created_at');
                    store.createIndex('stored_at', 'stored_at');
                }

                if (!db.objectStoreNames.contains('sync_logs')) {
                    const logStore = db.createObjectStore('sync_logs', {
                        keyPath: 'id',
                        autoIncrement: true
                    });
                    logStore.createIndex('status', 'status');
                    logStore.createIndex('timestamp', 'created_at');
                }
            };
        });
    }

    async storeTransaction(transaction: Omit<OfflineTransaction, 'offline_id' | 'stored_at'>): Promise<string> {
        if (!this.db) {
            await this.initDB();
        }

        const offline_id = crypto.randomUUID();
        const transactionData: OfflineTransaction = {
            ...transaction,
            offline_id,
            stored_at: new Date().toISOString(),
        };

        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const txn = this.db.transaction(['pending_transactions'], 'readwrite');
            const store = txn.objectStore('pending_transactions');
            const request = store.put(transactionData);

            request.onsuccess = () => {
                // Register for background sync
                this.registerBackgroundSync();
                resolve(offline_id);
            };
            request.onerror = () => reject(request.error);
        });
    }

    async getPendingTransactions(): Promise<OfflineTransaction[]> {
        if (!this.db) {
            await this.initDB();
        }

        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const txn = this.db.transaction(['pending_transactions'], 'readonly');
            const store = txn.objectStore('pending_transactions');
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async removeSyncedTransaction(offline_id: string): Promise<void> {
        if (!this.db) {
            await this.initDB();
        }

        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const txn = this.db.transaction(['pending_transactions'], 'readwrite');
            const store = txn.objectStore('pending_transactions');
            const request = store.delete(offline_id);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async syncToServer(): Promise<SyncResult> {
        const pendingTransactions = await this.getPendingTransactions();

        if (pendingTransactions.length === 0) {
            console.log('No pending transactions to sync');
            return {
                success: true,
                syncedCount: 0,
                failedCount: 0,
                errors: []
            };
        }

        console.log(`Syncing ${pendingTransactions.length} pending transactions...`);

        try {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token found:', !!csrfToken);

            const response = await fetch('/api/transactions/sync-web', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                },
                credentials: 'same-origin', // Include cookies for session authentication
                body: JSON.stringify({ transactions: pendingTransactions }),
            });

            console.log('Sync response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Sync response error:', errorText);
                throw new Error(`Sync failed: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('Sync result:', result);

            if (result.status === 'success') {
                // Remove successfully synced transactions
                const syncedIds = result.results
                    ?.filter((r: any) => r.status === 'success')
                    ?.map((r: any) => r.offline_id) || [];

                console.log(`Removing ${syncedIds.length} successfully synced transactions`);
                for (const id of syncedIds) {
                    await this.removeSyncedTransaction(id);
                }

                return {
                    success: true,
                    syncedCount: result.summary?.success || 0,
                    failedCount: result.summary?.failed || 0,
                    errors: result.results
                        ?.filter((r: any) => r.status === 'failed')
                        ?.map((r: any) => r.message) || []
                };
            } else {
                throw new Error(result.message || 'Sync failed');
            }
        } catch (error) {
            console.error('Sync error:', error);
            return {
                success: false,
                syncedCount: 0,
                failedCount: pendingTransactions.length,
                errors: [error instanceof Error ? error.message : 'Unknown error']
            };
        }
    }

    async getPendingCount(): Promise<number> {
        const transactions = await this.getPendingTransactions();
        return transactions.length;
    }

    private async registerBackgroundSync(): Promise<void> {
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: 'REGISTER_SYNC'
            });
        }
    }

    async forceSync(): Promise<SyncResult> {
        // Try direct sync first
        const result = await this.syncToServer();

        // Also trigger background sync
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: 'FORCE_SYNC'
            });
        }

        return result;
    }

    // Listen for sync events from service worker
    setupSyncListener(onSyncComplete?: (data: any) => void, onSyncError?: (error: any) => void): void {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                const { type, data } = event.data;

                switch (type) {
                    case 'SYNC_COMPLETE':
                        onSyncComplete?.(data);
                        break;
                    case 'SYNC_ERROR':
                        onSyncError?.(data);
                        break;
                }
            });
        }
    }

    async clearAllData(): Promise<void> {
        if (!this.db) {
            await this.initDB();
        }

        return new Promise((resolve, reject) => {
            if (!this.db) {
                reject(new Error('Database not initialized'));
                return;
            }

            const txn = this.db.transaction(['pending_transactions', 'sync_logs'], 'readwrite');

            txn.objectStore('pending_transactions').clear();
            txn.objectStore('sync_logs').clear();

            txn.oncomplete = () => resolve();
            txn.onerror = () => reject(txn.error);
        });
    }
}

// Export singleton instance
export const offlineSyncManager = new OfflineSyncManager();
