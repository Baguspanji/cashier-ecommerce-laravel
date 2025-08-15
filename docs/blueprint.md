# Blueprint Aplikasi Kasir E-commerce

Dokumen ini merupakan blueprint untuk pengembangan aplikasi kasir (Point of Sale) berbasis web menggunakan Laravel, Inertia.js, dan Vue.js.

## Status Proyek Saat Ini

**Aplikasi masih dalam tahap awal pengembangan** dengan stack teknologi:
- **Backend**: Laravel 12.24.0 dengan PHP 8.3.24
- **Frontend**: Vue.js 3.5.13 dengan Inertia.js 2.0.5
- **Styling**: Tailwind CSS 4.1.1
- **Database**: SQLite
- **Testing**: Pest 3.8.2

### Fitur yang Sudah Tersedia:
- ✅ Sistem autentikasi (login)
- ✅ Dashboard dasar
- ✅ Pengaturan profil pengguna
- ✅ Pengaturan tampilan (appearance)
- ✅ Manajemen password

### Fitur yang Belum Diimplementasi:
- ❌ Manajemen produk/item
- ❌ Sistem transaksi kasir
- ❌ Manajemen stok
- ❌ Sistem pelaporan
- ❌ Keranjang belanja
- ❌ Checkout dan pembayaran

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

## Roadmap Pengembangan

### Phase 1: Database & Models (Prioritas Tinggi)

**Tabel yang Perlu Dibuat:**

- **products**: id, name, description, price, image_path, category_id, is_active, timestamps
- **categories**: id, name, description, timestamps  
- **transactions**: id, user_id, total_amount, payment_method, status, timestamps
- **transaction_items**: id, transaction_id, product_id, quantity, unit_price, subtotal
- **stock_movements**: id, product_id, type (in/out/adjustment), quantity, notes, timestamps

### Phase 2: Core Features (Prioritas Tinggi)

**Backend Development:**

- Controllers: ProductController, CategoryController, TransactionController, StockController
- Form Requests untuk validasi
- Eloquent relationships antar model
- API endpoints untuk operasi CRUD

**Frontend Development:**

- Pages: Products, Categories, Transactions, Stock, Reports
- Components: ProductCard, CartPanel, CheckoutDialog, TransactionTable
- Composables untuk state management

### Phase 3: Advanced Features (Prioritas Medium)

- Sistem pelaporan dengan filter tanggal
- Barcode scanner integration
- Manajemen multiple payment methods
- User roles dan permissions
- Export laporan (PDF/Excel)

### Phase 4: Optimisasi & PWA (Prioritas Low)

- Progressive Web App implementation
- Offline functionality dengan IndexedDB
- Background sync untuk data
- Performance optimization

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
