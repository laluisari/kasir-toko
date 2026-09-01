# 🏪 Sistem Kasir - ERD & Dokumentasi MVP

## 📊 Entity Relationship Diagram (ERD)

```
┌─────────────────────────────────────────────────────────────────┐
│                         USERS                                   │
├─────────────────────────────────────────────────────────────────┤
│ id (PK)                                                         │
│ name                                                            │
│ email (UNIQUE)                                                  │
│ password (hashed)                                               │
│ role (admin|kasir) ← NEW FIELD                                  │
│ created_at                                                      │
│ updated_at                                                      │
└──────────────────────────┬──────────────────────────────────────┘
                           │ 1
                           │ creates
                           │
                           ▼ *
         ┌─────────────────────────────────────┐
         │      SALE_DOCUMENTS                 │
         ├─────────────────────────────────────┤
         │ id (PK)                             │
         │ user_id (FK) ← NEW FIELD            │
         │ invoice_number (UNIQUE)             │
         │ subtotal                            │
         │ discount_total                      │
         │ total_price                         │
         │ paid_amount                         │
         │ change_amount                       │
         │ payment_method (cash|qris|transfer) │
         │ status (completed|pending|canceled) │
         │ customer_note (nullable)            │
         │ created_at                          │
         │ updated_at                          │
         └────────────────┬────────────────────┘
                          │ 1
                          │ has
                          │
                          ▼ *
         ┌─────────────────────────────────────┐
         │           SALES                     │
         ├─────────────────────────────────────┤
         │ id (PK)                             │
         │ sale_document_id (FK)               │
         │ product_id (FK, nullable)           │
         │ product_name (snapshot)             │
         │ cost_price (snapshot)               │
         │ selling_price (snapshot)            │
         │ discount                            │
         │ quantity                            │
         │ subtotal                            │
         │ created_at                          │
         │ updated_at                          │
         └────────────────┬────────────────────┘
                          │ *
                          │
                          │ product_id (FK)
                          │
                          ▼ 1
         ┌─────────────────────────────────────┐
         │          PRODUCTS                   │
         ├─────────────────────────────────────┤
         │ id (PK)                             │
         │ category_id (FK)                    │
         │ barcode (UNIQUE, nullable)          │
         │ name                                │
         │ cost_price (modal)                  │
         │ selling_price (harga jual)          │
         │ discount (master diskon)            │
         │ stock                               │
         │ unit (pcs, bks, botol, kg)          │
         │ image (nullable)                    │
         │ created_at                          │
         │ updated_at                          │
         └────────────────┬────────────────────┘
                          │ *
                          │ belongs to
                          │
                          ▼ 1
         ┌─────────────────────────────────────┐
         │        CATEGORIES                   │
         ├─────────────────────────────────────┤
         │ id (PK)                             │
         │ name                                │
         │ slug (UNIQUE)                       │
         │ created_at                          │
         │ updated_at                          │
         └─────────────────────────────────────┘
```

---

## 🗂️ Fitur Per Modul

### 1. **Categories Management** ✅
- CRUD kategori produk
- Auto slug generation
- Used for product classification

**Routes:**
- `GET /admin/categories` - List kategori
- `POST /admin/categories` - Create kategori
- `PUT /admin/categories/{id}` - Update kategori
- `DELETE /admin/categories/{id}` - Delete kategori

---

### 2. **Products Management** ✅
- CRUD produk
- Tracking: barcode, harga beli, harga jual, stok, unit
- Master discount per produk
- Kategori grouping

**Routes:**
- `GET /admin/products` - List produk
- `POST /admin/products` - Create produk
- `PUT /admin/products/{id}` - Update produk
- `DELETE /admin/products/{id}` - Delete produk

**Stock Management:**
- Auto decrement saat checkout
- Low stock warning di dashboard (< 5 unit)

---

### 3. **Sales Module (Halaman Kasir)** ✅
**Fitur:**
- 🔍 Search produk by name atau barcode
- 🛒 Add to cart (session-based)
- 📝 Edit quantity & diskon per item
- 🧾 Auto calculate subtotal, total, kembalian
- 💳 Support 3 metode pembayaran (Cash, QRIS, Transfer)
- 🧮 Generate nomor invoice otomatis
- 📄 Print struk/nota

**Data Flow:**
1. Kasir mencari produk
2. Pilih quantity
3. Add to cart (disimpan di session)
4. Set metode pembayaran & uang diterima
5. Checkout → Buat SaleDocument + Sales items
6. Auto kurangi stock
7. Print struk

**Routes:**
- `GET /admin/sales` - Halaman kasir
- `GET /admin/sales/search?q=xxx` - Cari produk (AJAX)
- `POST /admin/sales/add-to-cart` - Add item (AJAX)
- `PUT /admin/sales/update-cart` - Update qty/diskon (AJAX)
- `DELETE /admin/sales/remove-from-cart` - Hapus item (AJAX)
- `POST /admin/sales/checkout` - Proses pembayaran (AJAX)
- `GET /admin/sales/{id}` - Detail struk (untuk print)

---

### 4. **Sales History & Tracking** ✅
**Fitur:**
- 📋 List semua transaksi
- 🔍 Filter by tanggal range
- 👤 Filter by kasir
- 📊 Summary: total penjualan, jumlah transaksi, total item
- 📄 View detail & print ulang struk

**Routes:**
- `GET /admin/sales-history` - History list dengan filter

---

### 5. **Dashboard** ✅
**Widgets:**
1. **KPI Cards:**
   - 💰 Total penjualan hari ini
   - 📅 Total penjualan bulan ini
   - 📆 Total penjualan tahun ini
   - 🛍️ Total item terjual hari ini

2. **Charts:**
   - 📈 Grafik penjualan 7 hari terakhir

3. **Data Tables:**
   - ⭐ Produk terlaris (Top 5 all-time)
   - 👥 Kasir aktif hari ini (dengan transaksi & total)
   - 💳 Metode pembayaran hari ini
   - ⚠️ Produk stok terbatas (< 5)
   - 📂 Penjualan per kategori (hari ini)

**Routes:**
- `GET /dashboard` - Dashboard utama

---

## 🔐 Role-Based Access

### Admin
- ✅ Manage categories
- ✅ Manage products
- ✅ View dashboard
- ✅ View all sales history
- ✅ Manage users (create kasir account) ← TODO

### Kasir
- ✅ Use kasir page (penjualan)
- ✅ View own sales history
- ✅ Print struk

---

## 📦 Database Schema

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(50) DEFAULT 'kasir',  -- NEW
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Sale Documents Table
```sql
CREATE TABLE sale_documents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,  -- NEW (FK to users)
    invoice_number VARCHAR(255) UNIQUE,
    subtotal BIGINT DEFAULT 0,
    discount_total BIGINT DEFAULT 0,
    total_price BIGINT,
    paid_amount BIGINT DEFAULT 0,
    change_amount BIGINT DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT 'cash',
    status VARCHAR(50) DEFAULT 'completed',
    customer_note TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### Sales Table
```sql
CREATE TABLE sales (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sale_document_id BIGINT,
    product_id BIGINT,
    product_name VARCHAR(255),
    cost_price BIGINT DEFAULT 0,
    selling_price BIGINT,
    discount BIGINT DEFAULT 0,
    quantity INT DEFAULT 1,
    subtotal BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (sale_document_id) REFERENCES sale_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);
```

### Products Table
```sql
CREATE TABLE products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT,
    barcode VARCHAR(255) UNIQUE NULLABLE,
    name VARCHAR(255),
    cost_price BIGINT DEFAULT 0,
    selling_price BIGINT,
    discount BIGINT DEFAULT 0,
    stock INT DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'pcs',
    image VARCHAR(255) NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

### Categories Table
```sql
CREATE TABLE categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 💡 Key Concepts

### 1. **Snapshot Data pada Transaksi**
Saat checkout, harga produk di-snapshot ke tabel `sales` agar tidak terpengaruh perubahan harga di master products.

```
Product: Mie Instan - Rp 3000 (harga produk)
   ↓ checkout
Sales Item: Mie Instan - Rp 3000 (harga saat transaksi)
   ↓ kemudian harga diubah ke Rp 3500
Product: Mie Instan - Rp 3500 (harga produk berubah)
Sales Item: Mie Instan - Rp 3000 (tetap sesuai transaksi)
```

### 2. **Stock Management**
- Stock dikurangi otomatis saat checkout
- Jika checkout dibatalkan, stock perlu dikembalikan (TODO: soft delete / undo)
- Dashboard warning jika stock < 5

### 3. **Invoice Number Generation**
Format: `INV-{YYYYMMDDHIS}-{RANDOM4}`
Contoh: `INV-20260829120530-A7K2`

### 4. **Session-Based Cart**
Keranjang disimpan di session (bukan database) untuk performa.
Dihapus setelah checkout sukses.

### 5. **Payment Methods**
- **Cash (Tunai):** Pembayaran langsung
- **QRIS:** Pembayaran via QR code
- **Transfer:** Pembayaran via transfer bank

---

## 🚀 Fitur Todo/Future

1. **User Management**
   - Create/edit kasir account
   - Role-based middleware

2. **Print Receipt via Bluetooth**
   - Browser Bluetooth API
   - Thermal printer support

3. **Customer Account** (optional)
   - Member loyalty program
   - Purchase history

4. **Advanced Reports**
   - Daily/weekly/monthly sales report
   - Product performance analysis
   - Tax reporting

5. **Stock Management**
   - Stock in/out manual adjustment
   - Supplier tracking
   - Reorder level alerts

6. **Multi-store Support**
   - Store management
   - Consolidate reports

---

## 📝 File Structure

```
app/
├── Http/Controllers/
│   ├── CategoryController.php ✅
│   ├── ProductController.php ✅
│   ├── SaleController.php ✅
│   └── DashboardController.php ✅
│
└── Models/
    ├── User.php (updated with role & relation) ✅
    ├── Category.php ✅
    ├── Product.php ✅
    ├── Sale.php ✅
    └── SaleDocument.php (updated with user relation) ✅

database/migrations/
├── *_create_users_table.php
├── *_create_categories_table.php ✅
├── *_create_products_table.php ✅
├── *_create_sale_documents_table.php ✅
├── *_create_sales_table.php ✅
├── 2026_08_29_000001_add_role_to_users_table.php ✅
└── 2026_08_29_000002_add_user_id_to_sale_documents_table.php ✅

resources/views/admin/
├── sales/
│   ├── index.blade.php (halaman kasir + cart) ✅
│   ├── show.blade.php (struk detail) ✅
│   └── history.blade.php (transaction history) ✅
├── products/ ✅
├── categories/ ✅
└── dashboard.blade.php ✅

routes/
└── web.php (updated with sale & dashboard routes) ✅
```

---

## ✅ Migration Checklist

- [x] Create categories table
- [x] Create products table
- [x] Create sale_documents table
- [x] Create sales table
- [x] Add role field to users
- [x] Add user_id to sale_documents
- [x] Create/update models dengan relations
- [x] Create controllers
- [x] Create views
- [x] Create routes

---

## 📌 Koneksi Antar Modul

```
User (Admin/Kasir)
    ↓
Dashboard (view analytics)
    ↓
Sales Page (halaman kasir)
    ├─ Search Products
    ├─ Add to Cart
    └─ Checkout → Create SaleDocument + Sales items
                ↓
        Sales History (track penjualan per kasir)
        ↓
        Dashboard (show kasir performance)
```

---

Created: 2026-08-29
Status: MVP Phase Complete 🎉
