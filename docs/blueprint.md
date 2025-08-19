# Blueprint Aplikasi Kasir E-commerce

Dokumen ini merupakan blueprint untuk pengembangan aplikasi kasir (Point of Sale) berbasis web menggunakan Laravel, Inertia.js, dan Vue.js.

## Status Proyek Saat Ini

**Aplikasi sudah dalam tahap lanjut pengembangan** dengan stack teknologi:

- **Backend**: Laravel 12.24.0 dengan PHP 8.3.24
- **Frontend**: Vue.js 3.5.13 dengan Inertia.js 2.0.5
- **Styling**: Tailwind CSS 4.1.1
- **Database**: SQLite (production-ready untuk switch ke MySQL/PostgreSQL)
- **Testing**: Pest 3.8.2 (202 tests passed dengan 836 assertions)
- **PWA**: Vite PWA plugin dengan service worker otomatis

### Fitur yang Sudah Tersedia (Fase 1-3 Complete)

- ✅ **Sistem autentikasi lengkap** (login, register, verification)
- ✅ **Dashboard interaktif** dengan analytics real-time
- ✅ **Manajemen produk lengkap** (CRUD, barcode, kategori)
- ✅ **Sistem transaksi kasir** (POS interface, checkout, receipt)
- ✅ **Manajemen stok** (tracking, adjustment, bulk operations)
- ✅ **Sistem pelaporan** (daily, custom range, export)
- ✅ **Keranjang belanja** dengan real-time calculations
- ✅ **Multiple payment methods** (cash, digital)
- ✅ **User management** dengan role-based access
- ✅ **PWA foundation** (manifest, service worker, caching)

### Fitur Fase 4 yang Perlu Diimplementasi

- 🔄 **Offline functionality** untuk transaksi
- 🔄 **Background sync** untuk data synchronization
- 🔄 **IndexedDB integration** untuk offline storage
- 🔄 **Conflict resolution** untuk data sync
- 🔄 **Advanced PWA features** (install prompt, offline indicators)
- 🔄 **Performance optimization** (lazy loading, caching strategies)

## Workflow Pengguna Target

Aplikasi ini dirancang untuk peran utama: **Kasir** sekaligus **Admin**.

### Workflow yang Direncanakan

1. **Alur Kerja Kasir (Transaksi Harian)**
   - **Mulai Transaksi**: Kasir membuka halaman utama yang menampilkan daftar produk yang tersedia
   - **Menambahkan Produk**: Kasir memilih produk yang dibeli pelanggan, dan produk tersebut masuk ke dalam keranjang belanja
   - **Proses Checkout**: Setelah semua produk ditambahkan, kasir melanjutkan ke proses checkout dengan kalkulasi total harga
   - **Pembayaran & Struk**: Setelah pembayaran diterima, transaksi selesai dan sistem menghasilkan struk digital

2. **Alur Kerja Admin (Manajemen)**
   - **Manajemen Item**: Admin dapat mengakses halaman "Items" untuk melakukan operasi CRUD pada data produk
   - **Manajemen Stok**: Admin dapat memantau dan menyesuaikan jumlah stok produk
   - **Melihat Laporan**: Admin dapat mengakses halaman "Reports" untuk melihat riwayat transaksi dengan filter periode
   - **Pengaturan**: Admin dapat mengkonfigurasi pengaturan umum aplikasi

### Arsitektur Aplikasi Saat Ini

**Database Schema (Fully Implemented):**

- **users**: User management dengan role-based access
- **categories**: Kategori produk dengan hierarchy support
- **products**: Produk lengkap dengan barcode, stock tracking
- **transactions**: Transaksi dengan multiple payment methods
- **transaction_items**: Detail item transaksi
- **stock_movements**: Tracking pergerakan stok dengan audit trail

**Backend Architecture (Laravel 12):**

- **Controllers**: ProductController, CategoryController, TransactionController, StockController, UserController
- **Models**: Eloquent models dengan relationships lengkap
- **Form Requests**: Validation terstruktur untuk semua input
- **Factories & Seeders**: Test data generation yang robust
- **Queue System**: Background job processing siap pakai

**Frontend Architecture (Vue 3 + Inertia.js):**

- **Pages**: Dashboard, Products, Categories, Transactions, Stock, Reports, Users
- **Components**: Reusable UI components dengan TypeScript
- **Composables**: useProducts, useCategories, useTransactions, useStock, useUsers
- **Layouts**: Responsive layout dengan dark mode support

**Testing Coverage (202 Tests Passed):**

- **Feature Tests**: End-to-end workflow testing
- **Unit Tests**: Business logic validation
- **E2E Tests**: Playwright untuk browser testing
- **Code Quality**: Laravel Pint untuk formatting

**PWA Foundation (80% Complete):**

- **Service Worker**: Auto-generated dengan Vite PWA
- **Manifest**: App metadata dan icons
- **Caching**: Strategic caching untuk assets dan API
- **Update Mechanism**: PWAUpdatePrompt component

## Roadmap Pengembangan

## Pengembangan dan Implementasi

### Fase 1-3: Core Application ✅ COMPLETED

#### Phase 1: Database & Models (DONE)

- **Semua tabel sudah dibuat dan optimal**
- **Eloquent relationships lengkap dan tested**  
- **Factories dan seeders production-ready**
- **Migration system dengan rollback support**

#### Phase 2: Core Features (DONE)

- **Backend Controllers dengan business logic robust**
- **Form Requests untuk semua validasi**
- **API endpoints untuk mobile/external access**
- **Frontend pages dengan TypeScript support**
- **Responsive UI dengan dark mode**

#### Phase 3: Advanced Features (DONE)

- **Sistem pelaporan dengan export functionality**
- **Barcode scanner integration**
- **Multiple payment methods**
- **User roles dan permissions**
- **Real-time stock tracking**

### Fase 4: PWA & Offline Functionality � IN PROGRESS (85% Complete)

#### ✅ Completed Components (85%)

##### Database Schema Enhancement ✅
- ✅ Added sync fields to all core tables (transactions, transaction_items, stock_movements)
- ✅ Created sync_logs table for tracking all sync operations
- ✅ All migrations successfully applied and tested
- ✅ Models updated with sync field casting (JSON, datetime)

##### API Infrastructure ✅
- ✅ Laravel Sanctum integrated for API authentication
- ✅ API routes configured and registered
- ✅ SyncController with complete CRUD operations (6 endpoints)
- ✅ TransactionSyncController for transaction-specific operations (4 endpoints)
- ✅ All API endpoints tested with authentication

##### Testing Infrastructure ✅
- ✅ Comprehensive API test suite (SyncControllerTest)
- ✅ All 149 existing tests still passing
- ✅ Factory patterns for sync testing
- ✅ Database integrity maintained

##### Frontend Foundation ✅
- ✅ Service Worker with background sync capabilities
- ✅ OfflineSyncManager class for IndexedDB operations
- ✅ useOfflineSync Vue composable
- ✅ SyncStatus Vue component for UI feedback
- ✅ PWA demo page for testing functionality

#### 🚧 In Progress Components (10%)

##### Service Worker Integration 🔄
- 🔄 Background sync registration and execution
- 🔄 IndexedDB for offline transaction storage
- 🔄 Push notifications for sync reminders

#### ⏳ Remaining Components (5%)

##### User Experience ⏸️
- ⏸️ Integrate sync components into main layout
- ⏸️ Update transaction creation flow for offline mode
- ⏸️ Add conflict resolution UI

#### Phase 4 Implementation Statistics

**Week 1 Progress (Day 1-2 COMPLETED)**
- ✅ Database Schema: 100% (5/5 migrations)
- ✅ API Endpoints: 100% (10/10 endpoints)  
- ✅ Models: 100% (4/4 updated)
- ✅ Testing: 100% (149/149 tests passing)

**Week 1 Progress (Day 3-4 IN PROGRESS)**
- ✅ Service Worker: 80% (foundation ready)
- 🔄 IndexedDB: 60% (classes created)
- 🔄 Background Sync: 70% (handlers implemented)
- ⏸️ Vue Integration: 40% (components created)

##### Target Timeline Remaining: 1 Week

###### Day 3-4: Service Worker & Offline Storage (Current)
- ✅ Service Worker foundation
- 🔄 IndexedDB implementation
- 🔄 Background sync registration
- 🔄 Offline transaction storage

###### Day 5-7: Frontend Integration
- ⏸️ Vue component integration
- ⏸️ Transaction flow updates
- ⏸️ Network status handling
- ⏸️ User feedback systems

## Struktur Database yang Direkomendasikan

```sql
-- Products table
CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(500),
    category_id INTEGER,
    current_stock INTEGER DEFAULT 0,
    minimum_stock INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Categories table  
CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Transactions table
CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INTEGER NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_amount DECIMAL(10,2) NOT NULL,
    change_amount DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Transaction items table
CREATE TABLE transaction_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Stock movements table
CREATE TABLE stock_movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    type VARCHAR(20) NOT NULL, -- 'in', 'out', 'adjustment'
    quantity INTEGER NOT NULL,
    previous_stock INTEGER NOT NULL,
    new_stock INTEGER NOT NULL,
    reference_id INTEGER, -- transaction_id for 'out' movements
    reference_type VARCHAR(50), -- 'transaction', 'manual', 'initial'
    notes TEXT,
    user_id INTEGER NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

- Production deployment optimization

## Implementasi Detail Fase 4

### Dependencies yang Diperlukan

#### Backend Dependencies

```bash
# Authentication untuk API sync
composer require laravel/sanctum

# Background job processing (sudah tersedia)
# Queue system sudah dikonfigurasi

# Optional: Better error tracking
composer require spatie/laravel-ray --dev
```

#### Frontend Dependencies

```bash
# IndexedDB wrapper untuk easier database operations
pnpm add idb

# Local storage with fallbacks
pnpm add localforage

# Network status detection
pnpm add @vueuse/core  # sudah tersedia

# Background sync utilities
pnpm add workbox-strategies workbox-window  # workbox-window sudah tersedia
```

### Database Schema Extensions

#### Migration untuk PWA Support

```sql
-- Add to existing tables for sync tracking
ALTER TABLE transactions ADD COLUMN sync_status VARCHAR(20) DEFAULT 'synced';
ALTER TABLE transactions ADD COLUMN offline_id VARCHAR(50) NULL;
ALTER TABLE transactions ADD COLUMN last_sync_at TIMESTAMP NULL;

ALTER TABLE transaction_items ADD COLUMN sync_status VARCHAR(20) DEFAULT 'synced';
ALTER TABLE transaction_items ADD COLUMN offline_id VARCHAR(50) NULL;

ALTER TABLE stock_movements ADD COLUMN sync_status VARCHAR(20) DEFAULT 'synced';
ALTER TABLE stock_movements ADD COLUMN offline_id VARCHAR(50) NULL;

-- New sync log table
CREATE TABLE sync_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    table_name VARCHAR(50) NOT NULL,
    record_id INTEGER NOT NULL,
    offline_id VARCHAR(50),
    action VARCHAR(20) NOT NULL, -- 'create', 'update', 'delete'
    sync_status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'synced', 'failed'
    data_snapshot TEXT, -- JSON snapshot for conflict resolution
    error_message TEXT,
    attempt_count INTEGER DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### API Architecture untuk Sync

#### Endpoint Structure

```php
// routes/api.php
Route::middleware('auth:sanctum')->prefix('sync')->group(function () {
    // Transaction sync
    Route::post('/transactions', [TransactionSyncController::class, 'syncTransactions']);
    Route::post('/transactions/resolve-conflicts', [TransactionSyncController::class, 'resolveConflicts']);
    
    // Data sync
    Route::get('/products', [OfflineSyncController::class, 'getProducts']);
    Route::get('/categories', [OfflineSyncController::class, 'getCategories']);
    
    // Sync status
    Route::get('/status', [OfflineSyncController::class, 'getSyncStatus']);
    Route::post('/heartbeat', [OfflineSyncController::class, 'heartbeat']);
});
```

#### Sync Data Structure

```json
{
  "sync_batch_id": "uuid",
  "timestamp": "2025-08-19T21:00:00Z",
  "transactions": [
    {
      "offline_id": "offline_txn_123",
      "items": [...],
      "total_amount": 150000,
      "payment_method": "cash",
      "created_at": "2025-08-19T20:30:00Z"
    }
  ],
  "conflicts": [
    {
      "type": "stock_mismatch",
      "product_id": 123,
      "expected_stock": 10,
      "actual_stock": 8
    }
  ]
}
```

### Frontend Architecture untuk Offline

#### IndexedDB Schema

```typescript
// resources/js/composables/useIndexedDB.ts
interface OfflineDBSchema {
  products: {
    id: number;
    name: string;
    price: number;
    stock: number;
    category_id: number;
    last_updated: Date;
  };
  
  categories: {
    id: number;
    name: string;
    last_updated: Date;
  };
  
  offline_transactions: {
    offline_id: string;
    items: TransactionItem[];
    total_amount: number;
    payment_method: string;
    status: 'pending' | 'syncing' | 'synced' | 'failed';
    created_at: Date;
    sync_attempts: number;
  };
  
  sync_queue: {
    id: string;
    table: string;
    action: 'create' | 'update' | 'delete';
    data: any;
    timestamp: Date;
    retry_count: number;
  };
}
```

#### Offline Transaction Flow

```typescript
// Offline transaction process
1. User creates transaction while offline
2. Save to IndexedDB with 'pending' status
3. Display in UI with offline indicator
4. When online: Queue for background sync
5. Background sync processes transaction
6. Update status based on result
7. Handle conflicts if any
8. Remove from queue when successful
```

### Testing Strategy untuk PWA

#### Offline Testing Scenarios

1. **Complete Offline Workflow**
   - Start app offline
   - Browse products from cache
   - Create transactions
   - Verify local storage
   - Go online and verify sync

2. **Intermittent Connectivity**
   - Simulate network drops during sync
   - Test retry mechanisms
   - Verify data integrity

3. **Conflict Resolution**
   - Create conflicts with stock levels
   - Test resolution strategies
   - Verify user experience

4. **Performance Testing**
   - Large dataset handling
   - Sync performance with many transactions
   - Memory usage monitoring

### Production Readiness Checklist

#### Security Considerations

- [ ] HTTPS enforcement untuk PWA
- [ ] API token security dengan Sanctum
- [ ] Data encryption untuk sensitive offline data
- [ ] Rate limiting untuk sync endpoints
- [ ] CORS configuration untuk API access

#### Performance Optimization

- [ ] Service worker caching optimization
- [ ] IndexedDB query optimization
- [ ] Lazy loading untuk large datasets
- [ ] Background sync batching
- [ ] Memory management untuk offline data

#### Monitoring & Analytics

- [ ] Offline usage tracking
- [ ] Sync failure monitoring
- [ ] Performance metrics collection
- [ ] Error logging dan alerting
- [ ] User experience analytics

#### Browser Compatibility

- [ ] Chrome/Chromium PWA features
- [ ] Safari PWA limitations handling
- [ ] Firefox IndexedDB compatibility
- [ ] Mobile browser testing
- [ ] Install prompt optimization

## Technology Stack Detail

**Backend (Laravel 12):**

- PHP 8.3.24
- SQLite database
- Eloquent ORM dengan relationships
- Form Request validation
- Resource API untuk mobile/external access

**Frontend (Inertia.js + Vue.js):**

- Vue.js 3.5.13 dengan Composition API
- Inertia.js 2.0.5 untuk SPA-like experience
- Tailwind CSS 4.1.1 untuk styling
- TypeScript untuk type safety

**Testing & Quality:**

- Pest 3.8.2 untuk testing
- Laravel Pint untuk code formatting
- Feature tests untuk workflow
- Unit tests untuk business logic

## Implementasi Bertahap

### Week 1-2: Database Foundation

1. Buat migration untuk semua tabel
2. Setup Eloquent models dengan relationships
3. Buat factory dan seeder untuk testing data
4. Write basic model tests

### Week 3-4: Product Management

1. ProductController dengan CRUD operations
2. Vue pages untuk product listing dan form
3. Image upload functionality
4. Category management

### Week 5-6: Transaction System

1. TransactionController untuk POS operations
2. Cart management dengan Vue composables
3. Checkout process implementation
4. Receipt generation

### Week 7-8: Stock & Reporting

1. Stock movement tracking
2. Inventory adjustment features
3. Transaction reports dengan filtering
4. Dashboard dengan analytics

## Considerations untuk Deployment

**Production Requirements:**

- Switch ke MySQL/PostgreSQL untuk production
- Setup proper image storage (S3/local disk)
- Implement proper user roles
- Add backup strategy
- Setup monitoring dan logging

**Security Measures:**

- Implement CSRF protection
- Add rate limiting
- Input validation on all forms
- Secure file uploads
- User session management

**Performance Optimization:**

- Database indexing untuk query performance
- Image optimization dan caching
- Laravel cache untuk static data
- CDN untuk asset delivery
