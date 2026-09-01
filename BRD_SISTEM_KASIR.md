# 🏪 Business Requirements Document (BRD)
## Sistem Kasir Toko Serbaguna

**Project Name:** Sistem Kasir  
**Version:** 1.0 - MVP (Minimum Viable Product)  
**Date:** 2026-08-29  
**Status:** Development Phase

---

## 📋 Executive Summary

Sistem Kasir adalah aplikasi manajemen penjualan untuk toko serbaguna (minimarket versi lokal) yang memudahkan proses transaksi, tracking inventory, dan analisis penjualan. Aplikasi ini dirancang untuk meningkatkan efisiensi operasional toko dengan interface yang user-friendly dan fitur-fitur essential untuk bisnis retail kecil-menengah.

---

## 🎯 Objektif Bisnis

### Primary Goals
1. **Mempercepat proses transaksi kasir** dari manual menjadi digital
2. **Mengurangi human error** dalam pencatatan penjualan dan kembalian
3. **Real-time inventory tracking** untuk monitoring stok produk
4. **Data-driven decision making** melalui dashboard analytics
5. **Improve customer experience** dengan proses checkout yang cepat dan struk tercetak otomatis

### Secondary Goals
1. Memudahkan tracking penjualan per kasir untuk performance evaluation
2. Mendapatkan insights tentang produk terlaris dan performa kategori
3. Mengidentifikasi low stock items untuk pemesanan tepat waktu
4. Memiliki arsip transaksi digital yang terorganisir

---

## 👥 Target Users & Personas

### 1. **Admin/Pemilik Toko**
- **Role:** Oversee seluruh operasional toko
- **Needs:** Dashboard analytics, tracking kasir, inventory management
- **Pain Points:** Susah tracking penjualan, sulit tahu produk mana yang laku
- **Primary Actions:** 
  - View dashboard
  - Manage products & categories
  - Monitor kasir performance
  - View history penjualan

### 2. **Kasir**
- **Role:** Handle transaksi pelanggan
- **Needs:** Fast checkout, easy product search, print struk
- **Pain Points:** Proses pembayaran lambat, banyak ngitung manual
- **Primary Actions:**
  - Search produk
  - Add to cart
  - Checkout & payment
  - Print receipt

### 3. **Customer** (Pelanggan)
- **Role:** Pembeli di toko
- **Needs:** Fast checkout, clear invoice/receipt
- **Benefit:** Mendapat struk yang jelas & transaksi cepat

---

## 📊 Business Context

### Current State (As-Is)
- ❌ Transaksi manual atau menggunakan aplikasi sederhana
- ❌ Stok tracking manual atau spreadsheet
- ❌ Kesulitan menganalisis penjualan
- ❌ Kasir harus menghitung change secara manual
- ❌ Tidak ada history transaksi yang terorganisir

### Desired State (To-Be)
- ✅ Transaksi digital terintegrasi
- ✅ Real-time inventory management
- ✅ Dashboard analytics untuk business insights
- ✅ Automatic change calculation
- ✅ Digital receipt dan transaction history
- ✅ Performance tracking per kasir

---

## 🎬 Use Cases & User Stories

### Epic 1: Sales Management (Halaman Kasir)

#### US1.1: Search Produk
```
AS A kasir
I WANT TO search produk by name atau barcode
SO THAT I can quickly find items saat melayani customer

Acceptance Criteria:
- Search box tersedia di halaman kasir
- Search berfungsi real-time (kecepatan < 200ms)
- Support search by nama & barcode
- Hasil mencakup: nama, kategori, harga, stok
- Tidak ada hasil → tampil "Produk tidak ditemukan"
```

#### US1.2: Add Product to Cart
```
AS A kasir
I WANT TO add produk ke keranjang dengan quantity yang saya tentukan
SO THAT I can process transaksi dengan cepat

Acceptance Criteria:
- Click/tap produk → popup input quantity
- Quantity harus >= 1
- Produk yg sudah di cart → increment quantity (tidak duplicate)
- Cart terupdate otomatis di UI
- Toast notification "Produk ditambahkan ke keranjang"
```

#### US1.3: Edit Cart Items
```
AS A kasir
I WANT TO edit quantity atau diskon item dalam cart
SO THAT I can adjust transaksi sesuai kebutuhan

Acceptance Criteria:
- Bisa edit quantity per item
- Bisa set diskon nominal per item
- Subtotal auto-calculate: (harga - diskon) x qty
- Total harga auto-update
```

#### US1.4: Remove Item from Cart
```
AS A kasir
I WANT TO remove item yang salah dari cart
SO THAT I can correct transaksi sebelum checkout

Acceptance Criteria:
- Tombol hapus ("×") tersedia di setiap item
- Confirmation dialog sebelum hapus
- Item hilang dari cart
- Cart summary terupdate otomatis
```

#### US1.5: Checkout & Payment
```
AS A kasir
I WANT TO process pembayaran dengan berbagai metode
SO THAT I can complete transaksi dan generate nota

Acceptance Criteria:
- Support 3 metode: Cash (Tunai), QRIS, Transfer Bank
- Kasir input uang diterima (untuk cash)
- Auto-calculate kembalian: uang - total
- Tombol "Bayar Sekarang" untuk confirm
- Berhasil → generate invoice number & SaleDocument
- Auto-redirect ke halaman struk untuk print
- Cart di-clear setelah checkout sukses
```

### Epic 2: Receipt Management (Struk/Nota)

#### US2.1: Display Receipt
```
AS A kasir
I WANT TO see detail struk/nota setelah checkout
SO THAT I can print receipt untuk customer

Acceptance Criteria:
- Halaman struk menampilkan:
  - No. Invoice (unik)
  - Kasir name
  - Tanggal & waktu
  - Detail items (nama, qty, harga, diskon, subtotal)
  - Total harga, total diskon, kembalian
  - Metode pembayaran
- Layout optimized untuk 80mm thermal printer
- Design professional & easy to read
```

#### US2.2: Print Receipt
```
AS A kasir
I WANT TO print struk langsung
SO THAT I can memberikan receipt ke customer

Acceptance Criteria:
- Tombol "Print Struk" di halaman detail
- Click → browser print dialog
- Pilih printer (thermal recommended)
- Layout auto-adjust untuk print
- No buttons/menus di print output
```

### Epic 3: Sales History & Tracking

#### US3.1: View Sales History
```
AS AN admin
I WANT TO view list semua transaksi dengan filter options
SO THAT I can track penjualan dan performance kasir

Acceptance Criteria:
- List transaksi dengan columns: invoice, tanggal, kasir, total, metode, status
- Pagination (15 items per page)
- Filter by date range (from_date, to_date)
- Filter by kasir (user_id)
- Summary stats: total penjualan, jumlah transaksi, total items, rata-rata
- View detail struk (bisa print ulang)
```

#### US3.2: View Kasir Sales Report
```
AS A kasir
I WANT TO see history transaksi saya sendiri
SO THAT I can track performance saya

Acceptance Criteria:
- Kasir bisa filter history by user_id mereka
- Lihat total penjualan, jumlah transaksi
- Comparison dengan hari/minggu sebelumnya (future)
```

### Epic 4: Dashboard Analytics

#### US4.1: KPI Cards
```
AS AN admin
I WANT TO see high-level metrics di dashboard
SO THAT I can understand performance hari ini

Acceptance Criteria:
- 4 Cards dengan data:
  1. Total penjualan hari ini (Rp)
  2. Total penjualan bulan ini (Rp)
  3. Total penjualan tahun ini (Rp)
  4. Total item terjual hari ini (qty)
```

#### US4.2: Sales Trend Chart
```
AS AN admin
I WANT TO see grafik penjualan 7 hari terakhir
SO THAT I can identify trends & patterns

Acceptance Criteria:
- Line chart menampilkan 7 hari data
- X-axis: tanggal (format: "01 Jan")
- Y-axis: nilai penjualan (Rp)
- Interactive: hover untuk lihat exact value
- Responsive di desktop & tablet
```

#### US4.3: Top Products
```
AS AN admin
I WANT TO see produk terlaris
SO THAT I can understand customer preferences

Acceptance Criteria:
- Table Top 5 produk (all-time berdasarkan total qty)
- Columns: ranking (🥇🥈🥉), nama produk, qty terjual
- Sortable by qty
```

#### US4.4: Low Stock Warning
```
AS AN admin
I WANT TO see produk dengan stok terbatas
SO THAT I can reorder sebelum kehabisan

Acceptance Criteria:
- Table produk dengan stok < 5 unit
- Columns: nama produk, kategori, stok
- Highlight row: habis (red), terbatas (yellow)
- Max 10 items ditampilkan
```

#### US4.5: Active Kasirs
```
AS AN admin
I WANT TO see kasir mana yang aktif hari ini
SO THAT I can monitor performance team

Acceptance Criteria:
- Table kasir yang melakukan transaksi hari ini
- Columns: nama, jumlah transaksi, total penjualan
- Sort by transaction count (descending)
```

#### US4.6: Payment Methods Breakdown
```
AS AN admin
I WANT TO see breakdown pembayaran by metode
SO THAT I can understand payment patterns

Acceptance Criteria:
- Show: Cash, QRIS, Transfer
- Display: jumlah transaksi & total Rp per metode
- Visual: progress bar menampilkan proportion
- For: hari ini
```

#### US4.7: Category Performance
```
AS AN admin
I WANT TO see sales per kategori produk
SO THAT I can optimize product mix

Acceptance Criteria:
- Table kategori: nama, jumlah item, jumlah transaksi
- For: hari ini
- Sort by item qty (descending)
```

### Epic 5: Product Management

#### US5.1: CRUD Categories
```
AS AN admin
I WANT TO manage kategori produk
SO THAT I can organize products

Acceptance Criteria:
- Create: nama & auto-slug
- List: semua kategori
- Update: edit nama & slug
- Delete: hapus (soft/hard delete)
```

#### US5.2: CRUD Products
```
AS AN admin
I WANT TO manage master produk
SO THAT I can control inventory & pricing

Acceptance Criteria:
- Create: 
  - Category, barcode (optional), nama
  - Cost price, selling price, master discount
  - Stock (initial), unit (pcs/bks/botol/kg)
  - Image (optional)
- List: all products dengan filter by category
- Update: edit semua fields
- Delete: hapus produk
- Stock tracking: tidak include transaksi yg pending
```

---

## 🎨 Functional Requirements

### F1: Cart Management
- Session-based cart (tidak tersimpan di DB)
- Support add, update qty, update discount, remove item
- Cart cleared setelah checkout sukses
- Cart session timeout: 30 menit (auto clear)

### F2: Transaksi Processing
- Generate invoice number otomatis: `INV-{YYYYMMDDHHmmss}-{RANDOM4}`
- Snapshot harga produk saat transaksi (jadi tidak terpengaruh perubahan harga master)
- Auto-decrement stock saat checkout
- Support 3 payment methods
- Calculate kembalian otomatis
- Create SaleDocument + multiple Sales items (1 per product)

### F3: Inventory Management
- Real-time stock tracking
- Low stock alert (< 5 unit)
- Stock history (future: audit trail)

### F4: Analytics & Reporting
- Dashboard dengan multiple KPIs
- Filter date range
- Filter by kasir/user
- Export (future: PDF/Excel)

### F5: Receipt/Struk
- Thermal printer optimized (80mm)
- Professional layout
- Print-friendly CSS
- Can print multiple times (no limits)

### F6: Security & Access Control
- Login required (Laravel Auth)
- Role-based: admin vs kasir (future: middleware)
- Soft/hard delete options (future)

---

## 🛠️ Non-Functional Requirements

### Performance
- Product search: < 200ms response time
- Cart operations: real-time UI update
- Dashboard load: < 2 seconds
- Concurrent users: support min 5+ simultaneous kasirs

### Scalability
- Database: dapat menampung min 1000+ produk
- Daily transactions: min 100+ per hari
- Data retention: 5 tahun (future: archive)

### Usability
- Interface: simple & intuitive (no training needed)
- Search: fast & accurate
- Mobile-friendly: responsive design
- Language: Bahasa Indonesia

### Reliability
- 99% uptime target
- Database backup: daily
- Error handling: graceful error messages
- Session management: auto logout 30 mins

### Security
- Password hashing: bcrypt
- CSRF protection: Laravel tokens
- SQL injection protection: prepared statements
- XSS protection: blade escaping

---

## 📋 Acceptance Criteria (Overall)

### General Criteria
- [x] Database schema normalized & optimized
- [x] Models dengan proper relationships
- [x] Controllers dengan SOLID principles
- [x] Views responsive & user-friendly
- [x] Routes RESTful & organized
- [x] Error handling implemented
- [x] All features tested (manual testing)

### MVP Features Completed
- [x] Product management (CRUD)
- [x] Category management (CRUD)
- [x] Kasir halaman (search, add to cart, edit, checkout)
- [x] Receipt/Struk detail & print
- [x] Sales history dengan filter
- [x] Dashboard dengan analytics
- [x] Real-time inventory updates

### Future Requirements (Phase 2+)
- [ ] User management (create kasir account)
- [ ] Role-based middleware
- [ ] Refund/cancel transaksi dengan stock reversal
- [ ] Print via Bluetooth (thermal printer)
- [ ] Export reports (PDF/Excel)
- [ ] Stock adjustment (manual in/out)
- [ ] Supplier management
- [ ] Member/customer loyalty program
- [ ] Multi-store support
- [ ] Advanced analytics & forecasting

---

## 📅 Project Timeline

### Phase 1: MVP (In Progress) ✅
**Duration:** Aug 15 - Aug 29, 2026
- Database schema & migrations
- Models & controllers
- Core features: sales, history, dashboard
- Basic UI/UX

**Status:** ~90% complete

### Phase 2: Enhancement (Planned)
**Duration:** Sep 1 - Sep 15, 2026
- User management
- Role-based authorization
- Refund/cancel transactions
- Print via Bluetooth
- Advanced reporting

### Phase 3: Optimization (Planned)
**Duration:** Sep 16 - Oct 15, 2026
- Performance optimization
- UI/UX refinement
- Load testing
- Security audit

---

## 💰 Resources & Investment

### Development Team
- 1 Backend Developer (Laravel)
- 1 Frontend Developer (Blade templates)
- 1 QA/Tester
- 1 Project Manager (part-time)

### Infrastructure
- Server: VPS/Cloud (AWS/Digital Ocean)
- Database: MySQL/PostgreSQL
- Storage: Local/Cloud storage for receipts & images
- Domain & SSL: Required

### Tools & Libraries
- Laravel 11+
- Bootstrap 5 (UI framework)
- Chart.js (charting)
- Blade templates
- MySQL database

---

## 📊 Success Metrics & KPIs

### Business Metrics
1. **Checkout speed:** Rata-rata transaksi < 3 menit (improvement dari 5-7 menit)
2. **Error reduction:** < 2% transaction errors (human error)
3. **Inventory accuracy:** 98%+ stok data match dengan fisik
4. **Customer satisfaction:** 85%+ satisfaction score

### Operational Metrics
1. **System uptime:** 99%+ availability
2. **Response time:** < 500ms untuk semua operations
3. **Daily users:** Min 3-5 kasirs consistently
4. **Data integrity:** 100% data consistency

### Adoption Metrics
1. **User adoption rate:** 100% kasirs using system within 2 weeks
2. **Training time:** < 30 mins per kasir untuk learn system
3. **Support tickets:** < 5 per bulan (post-launch)

---

## ⚠️ Risks & Mitigation

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|-----------|
| Server downtime | Medium | High | Daily backup, monitoring setup |
| Data loss | Low | Critical | Daily backup to cloud, redundancy |
| Kasir resistance | Low | Medium | Training & documentation |
| Performance issues | Low | Medium | Load testing, DB optimization |
| Security breach | Very Low | Critical | SSL, firewall, regular updates |
| Printer malfunction | Low | Low | Alternative: print to PDF, email |

---

## 📝 Assumptions & Constraints

### Assumptions
- Toko memiliki akses internet stabil (WiFi/broadband)
- Minimal 3-5 kasirs akan menggunakan sistem
- Thermal printer tersedia (atau regular printer as fallback)
- Admin tech-savvy enough untuk basic maintenance
- Produk count < 1000 (MVP scope)

### Constraints
- MVP launched dalam 2 minggu
- Budget terbatas untuk infrastructure
- Single store only (multi-store = future)
- Basic features only (no advanced AI/ML)
- Limited mobile app (web-only MVP)

---

## 🎯 Success Definition

Sistem Kasir MVP dianggap **SUKSES** jika:

✅ **Fungsional**
- Semua 5 epic (Sales, Receipt, History, Dashboard, Products) berfungsi baik
- No critical bugs
- Data integrity terjaga 100%

✅ **Performan**
- Load time < 2 detik untuk semua halaman
- Search response < 200ms
- Support min 5 concurrent users

✅ **Usability**
- Kasir dapat melakukan transaksi tanpa bantuan setelah 30 menit training
- Dashboard memberikan actionable insights
- UI intuitive & responsive

✅ **Reliability**
- Uptime 99%+ dalam testing period
- No data loss
- Graceful error handling

✅ **Adoption**
- 100% kasirs using system actively
- Admin satisfied dengan dashboard insights
- Less than 5 support tickets per bulan post-launch

---

## 🔄 Sign-off & Approval

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Project Manager | - | - | - |
| Business Owner | - | - | - |
| Technical Lead | - | - | - |
| QA Lead | - | - | - |

---

## 📚 Supporting Documents

- [x] ERD & Database Schema: `SISTEM_KASIR_ERD.md`
- [x] Technical Specifications: In-code documentation
- [x] User Manual: To be created (Phase 2)
- [x] API Documentation: To be created (Phase 2)
- [ ] Training Materials: To be created (Phase 2)
- [ ] Testing Plan: To be created (Phase 2)

---

## 📞 Contacts & References

**Project Lead:** [TBD]  
**Client:** [Pemilik Toko]  
**Support Email:** [TBD]  
**Documentation:** https://github.com/[repo]/wiki

---

**Document Version:** 1.0  
**Last Updated:** 2026-08-29  
**Next Review:** 2026-09-15

---

## 📌 Changelog

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-08-29 | 1.0 | Initial BRD creation | Dev Team |
| | | Complete MVP specifications | |
| | | Include all use cases & metrics | |

---

