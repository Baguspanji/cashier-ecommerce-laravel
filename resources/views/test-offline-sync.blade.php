<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Offline Sync Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .online { background: #d4edda; color: #155724; }
        .offline { background: #f8d7da; color: #721c24; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
        .results { margin-top: 20px; padding: 10px; background: #f8f9fa; }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Offline Sync Test</h1>

        <div id="status" class="status">
            Checking connection...
        </div>

        <div>
            <h3>Test Actions</h3>
            <button onclick="testIndexedDB()">Test IndexedDB</button>
            <button onclick="createOfflineTransaction()">Create Offline Transaction</button>
            <button onclick="getPendingTransactions()">Get Pending Transactions</button>
            <button onclick="syncTransactions()">Sync Transactions</button>
            <button onclick="clearOfflineData()">Clear Offline Data</button>
        </div>

        <div id="results" class="results">
            <h3>Results:</h3>
            <pre id="output"></pre>
        </div>
    </div>

    <script>
        // Status check
        function updateStatus() {
            const statusEl = document.getElementById('status');
            const isOnline = navigator.onLine;
            statusEl.className = `status ${isOnline ? 'online' : 'offline'}`;
            statusEl.textContent = `Status: ${isOnline ? 'Online' : 'Offline'}`;
        }

        // Update status on page load and when online/offline events occur
        updateStatus();
        window.addEventListener('online', updateStatus);
        window.addEventListener('offline', updateStatus);

        function log(message) {
            const output = document.getElementById('output');
            output.textContent += new Date().toLocaleTimeString() + ': ' + message + '\n';
            console.log(message);
        }

        // Test IndexedDB
        async function testIndexedDB() {
            try {
                const request = indexedDB.open('CashierSyncDB', 1);

                request.onerror = () => {
                    log('ERROR: Could not open IndexedDB');
                };

                request.onsuccess = (event) => {
                    const db = event.target.result;
                    log('SUCCESS: IndexedDB opened successfully');
                    log('Database name: ' + db.name);
                    log('Database version: ' + db.version);
                    log('Object stores: ' + Array.from(db.objectStoreNames).join(', '));
                    db.close();
                };

                request.onupgradeneeded = (event) => {
                    log('IndexedDB upgrade needed - creating object stores');
                    const db = event.target.result;

                    if (!db.objectStoreNames.contains('pending_transactions')) {
                        const store = db.createObjectStore('pending_transactions', { keyPath: 'offline_id' });
                        store.createIndex('timestamp', 'created_at');
                        store.createIndex('stored_at', 'stored_at');
                        log('Created pending_transactions object store');
                    }

                    if (!db.objectStoreNames.contains('sync_logs')) {
                        const logStore = db.createObjectStore('sync_logs', {
                            keyPath: 'id',
                            autoIncrement: true
                        });
                        logStore.createIndex('status', 'status');
                        logStore.createIndex('timestamp', 'created_at');
                        log('Created sync_logs object store');
                    }
                };
            } catch (error) {
                log('ERROR: ' + error.message);
            }
        }

        // Create offline transaction
        async function createOfflineTransaction() {
            try {
                const transaction = {
                    offline_id: crypto.randomUUID(),
                    customer_name: 'Test Customer',
                    total_amount: Math.floor(Math.random() * 100000) + 10000,
                    payment_method: 'cash',
                    items: [
                        {
                            product_id: 1,
                            quantity: Math.floor(Math.random() * 5) + 1,
                            price: Math.floor(Math.random() * 50000) + 5000
                        }
                    ],
                    created_at: new Date().toISOString(),
                    stored_at: new Date().toISOString()
                };

                const request = indexedDB.open('CashierSyncDB', 1);

                request.onsuccess = (event) => {
                    const db = event.target.result;
                    const txn = db.transaction(['pending_transactions'], 'readwrite');
                    const store = txn.objectStore('pending_transactions');

                    const addRequest = store.add(transaction);

                    addRequest.onsuccess = () => {
                        log('SUCCESS: Created offline transaction with ID: ' + transaction.offline_id);
                        log('Transaction data: ' + JSON.stringify(transaction, null, 2));
                    };

                    addRequest.onerror = () => {
                        log('ERROR: Failed to create offline transaction');
                    };

                    db.close();
                };

                request.onerror = () => {
                    log('ERROR: Could not open IndexedDB');
                };
            } catch (error) {
                log('ERROR: ' + error.message);
            }
        }

        // Get pending transactions
        async function getPendingTransactions() {
            try {
                const request = indexedDB.open('CashierSyncDB', 1);

                request.onsuccess = (event) => {
                    const db = event.target.result;
                    const txn = db.transaction(['pending_transactions'], 'readonly');
                    const store = txn.objectStore('pending_transactions');

                    const getAllRequest = store.getAll();

                    getAllRequest.onsuccess = () => {
                        const transactions = getAllRequest.result;
                        log('SUCCESS: Found ' + transactions.length + ' pending transactions');
                        if (transactions.length > 0) {
                            log('Pending transactions: ' + JSON.stringify(transactions, null, 2));
                        }
                    };

                    getAllRequest.onerror = () => {
                        log('ERROR: Failed to get pending transactions');
                    };

                    db.close();
                };

                request.onerror = () => {
                    log('ERROR: Could not open IndexedDB');
                };
            } catch (error) {
                log('ERROR: ' + error.message);
            }
        }

        // Sync transactions
        async function syncTransactions() {
            try {
                log('Starting sync process...');

                // First get pending transactions
                const request = indexedDB.open('CashierSyncDB', 1);

                request.onsuccess = async (event) => {
                    const db = event.target.result;
                    const txn = db.transaction(['pending_transactions'], 'readonly');
                    const store = txn.objectStore('pending_transactions');

                    const getAllRequest = store.getAll();

                    getAllRequest.onsuccess = async () => {
                        const transactions = getAllRequest.result;
                        log('Found ' + transactions.length + ' transactions to sync');

                        if (transactions.length === 0) {
                            log('No transactions to sync');
                            db.close();
                            return;
                        }

                        // Get CSRF token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        log('CSRF token found: ' + (csrfToken ? 'Yes' : 'No'));

                        try {
                            const response = await fetch('/api/transactions/sync', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({ transactions }),
                            });

                            log('Sync response status: ' + response.status);

                            if (!response.ok) {
                                const errorText = await response.text();
                                log('Sync response error: ' + errorText);
                                throw new Error(`Sync failed: ${response.statusText}`);
                            }

                            const result = await response.json();
                            log('Sync result: ' + JSON.stringify(result, null, 2));

                            if (result.status === 'success') {
                                // Remove successfully synced transactions
                                const syncedIds = result.results
                                    ?.filter(r => r.status === 'success')
                                    ?.map(r => r.offline_id) || [];

                                log('Removing ' + syncedIds.length + ' successfully synced transactions');

                                const deleteTxn = db.transaction(['pending_transactions'], 'readwrite');
                                const deleteStore = deleteTxn.objectStore('pending_transactions');

                                for (const id of syncedIds) {
                                    deleteStore.delete(id);
                                }

                                deleteTxn.oncomplete = () => {
                                    log('Successfully removed synced transactions from IndexedDB');
                                };
                            }
                        } catch (error) {
                            log('Sync error: ' + error.message);
                        }

                        db.close();
                    };

                    getAllRequest.onerror = () => {
                        log('ERROR: Failed to get pending transactions for sync');
                        db.close();
                    };
                };

                request.onerror = () => {
                    log('ERROR: Could not open IndexedDB for sync');
                };
            } catch (error) {
                log('ERROR: ' + error.message);
            }
        }

        // Clear offline data
        async function clearOfflineData() {
            if (!confirm('Are you sure you want to clear all offline data?')) {
                return;
            }

            try {
                const request = indexedDB.open('CashierSyncDB', 1);

                request.onsuccess = (event) => {
                    const db = event.target.result;
                    const txn = db.transaction(['pending_transactions', 'sync_logs'], 'readwrite');

                    txn.objectStore('pending_transactions').clear();
                    txn.objectStore('sync_logs').clear();

                    txn.oncomplete = () => {
                        log('SUCCESS: Cleared all offline data');
                    };

                    txn.onerror = () => {
                        log('ERROR: Failed to clear offline data');
                    };

                    db.close();
                };

                request.onerror = () => {
                    log('ERROR: Could not open IndexedDB');
                };
            } catch (error) {
                log('ERROR: ' + error.message);
            }
        }

        // Clear results
        function clearResults() {
            document.getElementById('output').textContent = '';
        }

        // Add clear button
        const clearBtn = document.createElement('button');
        clearBtn.textContent = 'Clear Results';
        clearBtn.onclick = clearResults;
        document.querySelector('div > h3').parentNode.appendChild(clearBtn);
    </script>
</body>
</html>
