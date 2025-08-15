# PWA Implementation Blueprint - Aplikasi Kasir E-commerce

Dokumen ini berisi blueprint lengkap untuk implementasi Progressive Web App (PWA) pada aplikasi kasir e-commerce.

## Overview PWA untuk Aplikasi Kasir

Progressive Web App akan memungkinkan aplikasi kasir beroperasi secara offline, sangat penting untuk:

- Transaksi tidak terganggu saat koneksi internet bermasalah
- Performa lebih cepat dengan caching
- Installable di device seperti native app
- Background sync untuk sinkronisasi data otomatis

## Arsitektur PWA

### Frontend Components

```text
resources/js/
├── composables/
│   ├── useOfflineSync.js      # Handle offline/online state
│   ├── useIndexedDB.js        # Database operations
│   ├── usePWA.js             # PWA utilities
│   └── useOfflineTransaction.js # Offline transaction logic
├── stores/
│   ├── offlineStore.js        # Pinia store untuk offline data
│   ├── transactionStore.js    # Transaction state management
│   └── syncStore.js          # Sync status management
├── workers/
│   └── background-sync.js     # Background sync logic
└── components/
    ├── OfflineIndicator.vue   # Status koneksi
    ├── SyncStatus.vue        # Progress sinkronisasi
    └── OfflineTransaction.vue # Form transaksi offline
```

### Backend Components

```text
app/
├── Http/Controllers/Api/
│   ├── OfflineSyncController.php    # API sync endpoints
│   └── TransactionSyncController.php # Transaction sync logic
├── Jobs/
│   ├── ProcessOfflineTransaction.php # Queue job untuk sync
│   └── SyncOfflineData.php         # Bulk data sync
├── Services/
│   ├── OfflineSyncService.php      # Business logic sync
│   └── PWAManifestService.php      # Generate manifest
└── Middleware/
    └── OfflineDataMiddleware.php    # Handle offline requests
```

## Database Schema untuk PWA

### Tambahan field untuk sync tracking

```sql
-- Add sync fields to existing tables
ALTER TABLE transactions ADD COLUMN sync_status VARCHAR(20) DEFAULT 'synced';
ALTER TABLE transactions ADD COLUMN offline_id VARCHAR(50) NULL;
ALTER TABLE transactions ADD COLUMN last_sync_at TIMESTAMP NULL;

ALTER TABLE transaction_items ADD COLUMN sync_status VARCHAR(20) DEFAULT 'synced';
ALTER TABLE transaction_items ADD COLUMN offline_id VARCHAR(50) NULL;

ALTER TABLE stock_movements ADD COLUMN sync_status VARCHAR(20) DEFAULT 'synced';
ALTER TABLE stock_movements ADD COLUMN offline_id VARCHAR(50) NULL;

-- Sync log table
CREATE TABLE sync_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    table_name VARCHAR(50) NOT NULL,
    record_id INTEGER NOT NULL,
    offline_id VARCHAR(50),
    action VARCHAR(20) NOT NULL, -- 'create', 'update', 'delete'
    sync_status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'synced', 'failed'
    data_snapshot TEXT, -- JSON snapshot untuk conflict resolution
    error_message TEXT,
    attempt_count INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## PWA Core Files Structure

### 1. Service Worker (public/sw.js)

```javascript
// Cache strategies dan offline handling
const CACHE_NAME = 'cashier-v1';
const urlsToCache = [
  '/',
  '/dashboard',
  '/css/app.css',
  '/js/app.js',
  // Static assets
];

// Install, activate, fetch event listeners
// Background sync registration
// Cache management
```

### 2. Web App Manifest (public/manifest.json)

```json
{
  "name": "Kasir E-commerce",
  "short_name": "Kasir",
  "description": "Aplikasi Point of Sale",
  "start_url": "/",
  "display": "standalone",
  "theme_color": "#3B82F6",
  "background_color": "#FFFFFF",
  "icons": [...]
}
```

### 3. IndexedDB Schema

```javascript
// Database structure untuk offline storage
const dbSchema = {
  products: 'id, name, price, stock, category_id, image_path',
  categories: 'id, name, description',
  offlineTransactions: 'id, items, total, timestamp, status',
  syncQueue: 'id, table, action, data, timestamp'
};
```

## Implementation Roadmap PWA

### Week 1: PWA Foundation

**Day 1-2: Service Worker Setup**

- [x] Create service worker dengan basic caching
- [x] Register service worker di main app
- [x] Implement cache strategies (Cache First, Network First)

**Day 3-4: Web App Manifest**

- [x] Create manifest.json dengan app metadata
- [x] Generate app icons (72x72 hingga 512x512)
- [x] Add meta tags untuk PWA di blade template

**Day 5-7: IndexedDB Setup**

- [x] Create useIndexedDB composable
- [x] Database schema untuk offline storage
- [x] CRUD operations untuk products & categories

### Week 2: Offline Transaction System

**Day 1-3: Offline Transaction Logic**

- [x] useOfflineTransaction composable
- [x] Transaction form yang work offline
- [x] Local storage untuk cart data

**Day 4-5: Sync Queue System**

- [x] Queue system untuk pending transactions
- [x] Conflict detection dan resolution
- [x] Retry mechanism untuk failed sync

**Day 6-7: UI Components**

- [x] OfflineIndicator component
- [x] SyncStatus component dengan progress
- [x] Offline transaction list

### Week 3: Background Sync & Advanced Features

**Day 1-3: Background Sync**

- [x] Background sync registration
- [x] Sync when connection restored
- [x] Batch sync untuk multiple transactions

**Day 4-5: Data Sync API**

- [x] OfflineSyncController endpoints
- [x] Transaction validation on sync
- [x] Stock conflict resolution

**Day 6-7: Error Handling & Logging**

- [x] Comprehensive error handling
- [x] Sync logs untuk debugging
- [x] User feedback untuk sync status

### Week 4: Testing & Optimization

**Day 1-2: Testing Offline Scenarios**

- [x] Test complete offline workflow
- [x] Test sync when reconnected
- [x] Test conflict resolution

**Day 3-4: Performance Optimization**

- [x] Optimize cache size
- [x] Lazy loading untuk large datasets
- [x] Compress sync data

**Day 5-7: Documentation & Deployment**

- [x] User manual untuk offline features
- [x] Deployment checklist
- [x] Performance monitoring setup

## Technical Implementation Details

### 1. Offline Transaction Flow

```text
1. User starts transaction offline
2. Products loaded from IndexedDB
3. Cart managed in local storage
4. Transaction saved to offline queue
5. UI shows "pending sync" status
6. When online: auto-sync in background
7. Update transaction status on success
```

### 2. Sync Conflict Resolution

```text
Priority Rules:
- Server data wins for product info
- Last transaction timestamp wins
- Stock conflicts require manual review
- Critical conflicts pause sync for review
```

### 3. Cache Strategy

```text
- Static assets: Cache First
- Product data: Stale While Revalidate
- Transaction data: Network First
- User preferences: Cache First
```

## PWA Testing Checklist

### Offline Functionality

- [x] App loads without network
- [x] Can browse products offline
- [x] Can create transactions offline
- [x] Queue shows pending transactions
- [x] Auto-sync when reconnected

### Performance

- [x] First load < 3 seconds
- [x] Subsequent loads < 1 second
- [x] Smooth animations
- [x] Responsive on mobile devices

### Installation

- [x] Install prompt appears
- [x] App installs correctly
- [x] Launched as standalone app
- [x] Proper app icon displays

## Production Considerations

### Security

- HTTPS requirement untuk PWA
- API token management untuk sync
- Data encryption untuk sensitive info

### Monitoring

- Sync failure rate tracking
- Offline usage analytics
- Performance metrics
- Error logging dan alerting

### Backup Strategy

- Regular IndexedDB backup
- Conflict resolution logs
- Transaction audit trail

## Contoh Implementasi File PWA

### 1. Service Worker (public/sw.js)

```javascript
const CACHE_NAME = 'cashier-v1.0.0';
const API_CACHE = 'api-cache-v1';

const STATIC_ASSETS = [
  '/',
  '/dashboard',
  '/manifest.json',
  '/css/app.css',
  '/js/app.js'
];

// Install event
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

// Activate event
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME && cacheName !== API_CACHE) {
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch event dengan cache strategy
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // API requests - Network First
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(networkFirst(request));
  }
  // Static assets - Cache First
  else {
    event.respondWith(cacheFirst(request));
  }
});

// Background Sync
self.addEventListener('sync', event => {
  if (event.tag === 'offline-transactions') {
    event.waitUntil(syncOfflineTransactions());
  }
});

async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(API_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    return cachedResponse || new Response('Offline', { status: 503 });
  }
}

async function cacheFirst(request) {
  const cachedResponse = await caches.match(request);
  return cachedResponse || fetch(request);
}
```

### 2. IndexedDB Composable (resources/js/composables/useIndexedDB.js)

```javascript
import { ref } from 'vue';

export function useIndexedDB() {
  const db = ref(null);
  const isReady = ref(false);

  const initDB = async () => {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open('CashierDB', 1);

      request.onerror = () => reject(request.error);
      request.onsuccess = () => {
        db.value = request.result;
        isReady.value = true;
        resolve(db.value);
      };

      request.onupgradeneeded = (event) => {
        const database = event.target.result;

        // Products store
        if (!database.objectStoreNames.contains('products')) {
          const productStore = database.createObjectStore('products', { keyPath: 'id' });
          productStore.createIndex('category_id', 'category_id', { unique: false });
        }

        // Categories store
        if (!database.objectStoreNames.contains('categories')) {
          database.createObjectStore('categories', { keyPath: 'id' });
        }

        // Offline transactions store
        if (!database.objectStoreNames.contains('offlineTransactions')) {
          const transactionStore = database.createObjectStore('offlineTransactions', { 
            keyPath: 'offline_id' 
          });
          transactionStore.createIndex('timestamp', 'timestamp', { unique: false });
          transactionStore.createIndex('sync_status', 'sync_status', { unique: false });
        }

        // Sync queue store
        if (!database.objectStoreNames.contains('syncQueue')) {
          const syncStore = database.createObjectStore('syncQueue', { 
            keyPath: 'id', 
            autoIncrement: true 
          });
          syncStore.createIndex('timestamp', 'timestamp', { unique: false });
        }
      };
    });
  };

  const addItem = async (storeName, item) => {
    if (!db.value) await initDB();
    
    const transaction = db.value.transaction([storeName], 'readwrite');
    const store = transaction.objectStore(storeName);
    return store.add(item);
  };

  const getItem = async (storeName, key) => {
    if (!db.value) await initDB();
    
    const transaction = db.value.transaction([storeName], 'readonly');
    const store = transaction.objectStore(storeName);
    return store.get(key);
  };

  const getAllItems = async (storeName) => {
    if (!db.value) await initDB();
    
    const transaction = db.value.transaction([storeName], 'readonly');
    const store = transaction.objectStore(storeName);
    return store.getAll();
  };

  const updateItem = async (storeName, item) => {
    if (!db.value) await initDB();
    
    const transaction = db.value.transaction([storeName], 'readwrite');
    const store = transaction.objectStore(storeName);
    return store.put(item);
  };

  const deleteItem = async (storeName, key) => {
    if (!db.value) await initDB();
    
    const transaction = db.value.transaction([storeName], 'readwrite');
    const store = transaction.objectStore(storeName);
    return store.delete(key);
  };

  return {
    db,
    isReady,
    initDB,
    addItem,
    getItem,
    getAllItems,
    updateItem,
    deleteItem
  };
}
```

### 3. Offline Transaction Composable (resources/js/composables/useOfflineTransaction.js)

```javascript
import { ref, computed } from 'vue';
import { useIndexedDB } from './useIndexedDB';
import { v4 as uuidv4 } from 'uuid';

export function useOfflineTransaction() {
  const { addItem, getAllItems, updateItem } = useIndexedDB();
  const isOnline = ref(navigator.onLine);
  const pendingTransactions = ref([]);

  // Listen untuk perubahan status online/offline
  window.addEventListener('online', () => {
    isOnline.value = true;
    syncPendingTransactions();
  });

  window.addEventListener('offline', () => {
    isOnline.value = false;
  });

  const createOfflineTransaction = async (transactionData) => {
    const offlineTransaction = {
      offline_id: uuidv4(),
      ...transactionData,
      sync_status: 'pending',
      created_at: new Date().toISOString(),
      is_offline: true
    };

    try {
      await addItem('offlineTransactions', offlineTransaction);
      await loadPendingTransactions();
      
      // Register background sync jika didukung
      if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
        const registration = await navigator.serviceWorker.ready;
        await registration.sync.register('offline-transactions');
      }

      return offlineTransaction;
    } catch (error) {
      console.error('Failed to save offline transaction:', error);
      throw error;
    }
  };

  const loadPendingTransactions = async () => {
    try {
      const allTransactions = await getAllItems('offlineTransactions');
      pendingTransactions.value = allTransactions.filter(t => t.sync_status === 'pending');
    } catch (error) {
      console.error('Failed to load pending transactions:', error);
    }
  };

  const syncPendingTransactions = async () => {
    if (!isOnline.value || pendingTransactions.value.length === 0) return;

    for (const transaction of pendingTransactions.value) {
      try {
        await syncSingleTransaction(transaction);
      } catch (error) {
        console.error('Failed to sync transaction:', transaction.offline_id, error);
      }
    }
  };

  const syncSingleTransaction = async (transaction) => {
    try {
      const response = await fetch('/api/transactions/sync', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(transaction)
      });

      if (response.ok) {
        // Update status menjadi synced
        await updateItem('offlineTransactions', {
          ...transaction,
          sync_status: 'synced',
          synced_at: new Date().toISOString()
        });
        
        await loadPendingTransactions();
      } else {
        throw new Error(`Sync failed: ${response.statusText}`);
      }
    } catch (error) {
      // Mark as failed
      await updateItem('offlineTransactions', {
        ...transaction,
        sync_status: 'failed',
        error_message: error.message
      });
      throw error;
    }
  };

  const pendingCount = computed(() => pendingTransactions.value.length);

  return {
    isOnline,
    pendingTransactions,
    pendingCount,
    createOfflineTransaction,
    loadPendingTransactions,
    syncPendingTransactions,
    syncSingleTransaction
  };
}
```

### 4. Offline Sync Controller (app/Http/Controllers/Api/OfflineSyncController.php)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\OfflineSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfflineSyncController extends Controller
{
    public function __construct(
        private OfflineSyncService $syncService
    ) {}

    public function syncTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'offline_id' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_amount' => 'required|numeric|min:0',
            'created_at' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check jika offline_id sudah pernah di-sync
            $existingTransaction = Transaction::where('offline_id', $request->offline_id)->first();
            
            if ($existingTransaction) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction already synced',
                    'transaction_id' => $existingTransaction->id
                ]);
            }

            $result = $this->syncService->processOfflineTransaction($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction synced successfully',
                'transaction_id' => $result['transaction']->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSyncStatus(Request $request)
    {
        $offlineIds = $request->input('offline_ids', []);
        
        $transactions = Transaction::whereIn('offline_id', $offlineIds)
            ->select('offline_id', 'id', 'created_at')
            ->get()
            ->keyBy('offline_id');

        return response()->json([
            'success' => true,
            'synced_transactions' => $transactions
        ]);
    }

    public function batchSync(Request $request)
    {
        $transactions = $request->input('transactions', []);
        $results = [];

        foreach ($transactions as $transactionData) {
            try {
                $result = $this->syncService->processOfflineTransaction($transactionData);
                $results[] = [
                    'offline_id' => $transactionData['offline_id'],
                    'success' => true,
                    'transaction_id' => $result['transaction']->id
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'offline_id' => $transactionData['offline_id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }
}
```

### 5. Offline Sync Service (app/Services/OfflineSyncService.php)

```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class OfflineSyncService
{
    public function processOfflineTransaction(array $data): array
    {
        // Generate transaction number
        $transactionNumber = $this->generateTransactionNumber();

        // Create transaction
        $transaction = Transaction::create([
            'transaction_number' => $transactionNumber,
            'offline_id' => $data['offline_id'],
            'user_id' => Auth::id(),
            'total_amount' => $data['total_amount'],
            'payment_method' => $data['payment_method'],
            'payment_amount' => $data['payment_amount'],
            'change_amount' => $data['payment_amount'] - $data['total_amount'],
            'status' => 'completed',
            'sync_status' => 'synced',
            'created_at' => $data['created_at']
        ]);

        $stockMovements = [];

        // Create transaction items dan update stock
        foreach ($data['items'] as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);

            // Create transaction item
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'subtotal' => $itemData['quantity'] * $itemData['unit_price']
            ]);

            // Update stock dan create stock movement
            $previousStock = $product->current_stock;
            $newStock = $previousStock - $itemData['quantity'];

            $product->update(['current_stock' => $newStock]);

            $stockMovements[] = StockMovement::create([
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => $itemData['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reference_id' => $transaction->id,
                'reference_type' => 'transaction',
                'notes' => 'Sale transaction (offline sync)',
                'user_id' => Auth::id()
            ]);
        }

        return [
            'transaction' => $transaction,
            'stock_movements' => $stockMovements
        ];
    }

    private function generateTransactionNumber(): string
    {
        $prefix = 'TRX';
        $date = now()->format('Ymd');
        $sequence = Transaction::whereDate('created_at', today())->count() + 1;
        
        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }
}
```

## Langkah-Langkah Implementation

### Step 1: Setup Basic PWA

1. Buat file manifest.json dan service worker
2. Register service worker di app.js
3. Add PWA meta tags di layout blade

### Step 2: IndexedDB Integration

1. Implement useIndexedDB composable
2. Setup offline data stores
3. Sync master data (products, categories) ke IndexedDB

### Step 3: Offline Transaction System

1. Create useOfflineTransaction composable
2. Implement offline transaction form
3. Setup sync queue system

### Step 4: Backend Sync API

1. Create OfflineSyncController
2. Implement OfflineSyncService
3. Add sync routes dan middleware

### Step 5: UI Components

1. Create OfflineIndicator component
2. Add SyncStatus component
3. Implement offline transaction list

Dengan blueprint PWA ini, aplikasi kasir Anda akan dapat beroperasi secara offline dan melakukan sinkronisasi otomatis ketika koneksi kembali tersedia.
