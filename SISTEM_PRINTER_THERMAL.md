# 🖨️ Sistem Printer Thermal - Kasir Serbaguna
### BRD + ERD (Alur Printer) + Implementasi Teknis

**Project Name:** Sistem Kasir (kasir-toko)
**Sub-System:** Pencetakan Struk & Barcode ke Printer Thermal RPP02N
**Status:** Development (macOS) — Struk & Barcode sudah **terbukti tercetak fisik**
**Date:** 2026-09-06

---

## 1. 📋 Executive Summary

Sistem Kasir butuh mencetak **struk transaksi** dan **label barcode produk** ke printer thermal
**PUTIAN RPP02N** (58mm). Seller berjalan di hosting cloud (Dokploy), sedangkan printer fisik ada
di lokasi toko. Sub-sistem ini bertanggung jawab mengubah data transaksi/produk menjadi instruksi
cetak yang benar sampai keluar sebagai kertas, termasuk **pemotongan kertas otomatis** dan dukungan
**laci uang** (cash drawer).

---

## 2. 🎯 Latar Belakang & Masalah

| # | Masalah | Temuan |
|---|---------|--------|
| M1 | Bluetooth printer tidak bisa dipakai dari macOS | Diverifikasi 3 cara: SPP outbound ditolak (`0xE00002BC`), MAC acak + service `Braille ACL` saja, status `GS r` → `DEAD`/tidak ada respon. Printer hanya japri dari HP (HP yang aktif menelpon printer). |
| M2 | macOS menghapus "Raw Queue" (`lpadmin -m raw`) | Muncul error `Raw queues are no longer supported on macOS`. |
| M3 | Driver CUPS "Generic PostScript Printer" memuntahkan bahasa mesin | Browser kirim PDF → macOS konversi ke PostScript → printer thermal (hanya paham ESC/POS) mencetak kode-nya sebagai teks & mubazir kertas. |
| M4 | Barcode CODE128 dengan mike42/escpos perlu prefix | Input `8992822111` ditolak regex; butuh format `{B...`. |

**Kesimpulan arsitektur:** jalur paling andal = **kirim byte ESC/POS murni** dan lewati seluruh
driver. Di macOS dicapai lewat opsi job CUPS `-o document-format=application/vnd.cups-raw`
(**terbukti berhasil**). Bluetooth untuk sementara tidak dipakai di macOS.

---

## 3. 👥 Pengguna & Peran

| Peran | Kebutuhan | Mode Cetak |
|-------|-----------|------------|
| Admin/Kasir (toko) | Cetak struk transaksi per pembayaran | `cups` / `raw` / `browser` |
| Admin | Cetak label barcode produk (ukuran harga/unit) | `cups` / `raw` / `browser` |
| Owner | Konsistensi penampilan struk seluruh toko | Template ESC/POS terpusat |

---

## 4. 🧩 Kebutuhan Fungsional (BRD)

| ID | Kebutuhan | Status |
|----|-----------|--------|
| FR-01 | Cetak struk 58mm dari detail transaksi (tombol **Print Thermal**) | ✅ Implemented & tervalidasi fisik |
| FR-02 | Cetak label barcode produk dari daftar produk (tombol **Cetak Barcode**) | ✅ Implemented & tervalidasi fisik |
| FR-03 | Dukungan banyak salinan barcode (`copies`) | ✅ Implemented (browser & cups via loop) |
| FR-04 | Deteksi tipe barcode otomatis: EAN-13 (13 digit) / EAN-8 (8 digit) / Code128 (lainnya) | ✅ Implemented |
| FR-05 | Potong kertas otomatis setelah struk (`GS V` cut) | ✅ Implemented (& diaktifkan) |
| FR-06 | Cadangan jalur serial (COM/rfcomm) untuk Windows/Linux | ✅ Implemented (`mode raw`, `BluetoothSerialPrintConnector`) |
| FR-07 | Jalur "ngeprint biasa" window.print untuk dev/label | ✅ Implemented (`mode browser`, view label + JsBarcode) |
| FR-08 | Buka laci uang (ESC p) | 📌 Planned (mode bridge/raw) |
| FR-09 | Dukungan kasir Windows (PC toko) untuk produksi | 📌 Planned (bridge agent + Tailscale) |

---

## 5. 🏗️ ERD / Alur Pencetakan (Printer)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        ALUR CETAK STRUK & BARCODE                           │
└─────────────────────────────────────────────────────────────────────────────┘

 Kasir / Admin (Browser)
   │  klik tombol  "Print Thermal" | "Cetak Barcode"
   ▼
 LARAVEL
 ┌────────────────────────────────────────────────────────────┐
 │ routes/web.php                                             │
 │   POST  admin/sales/{saleDocument}/print-thermal    (struk) │
 │   POST  admin/products/{product}/print-barcode      (label) │
 │   GET   admin/products/{product}/barcode-label      (label browser) │
 ├────────────────────────────────────────────────────────────┤
 │ PrintController  →  ThermalPrintService                     │
 │  • printSaleDocument()   → renderReceipt()                  │
 │  • printBarcode()        → renderBarcode()                  │
 │    - initialize, text, barcode (ECC128/EAN), harga, feed    │
 └───────────────┬────────────────────────────────────────────┘
                 │  alias :: Printer (mike42/escpos-php)
                 ▼
      thermal.print_mode (di config/thermal.php)
      ├── "browser"  →  window.print()  (HTML/CSS, label JsBarcode)
      ├── "cups"     →  CupsRawPrintConnector
      │                  └─ lp -d <queue> -o document-format=application/vnd.cups-raw
      │                        (driver dilewati, byte verbatim)
      └── "raw"      →  BluetoothSerialPrintConnector
                           └─ tulis langsung ke /dev/cu.* / COMx (stty / mode COM)

 macOS (CUPS)  usb://GEZHI/micro-printer?serial=000000000004
   └─ queue: GEZHI_micro_printer   [driver di-bypass oleh document-format]
        │
        ▼  kabel USB (printer-class, Vendor 0x28e9 / GEZHI, PID 0x0289)
 PUTIAN RPP02N  (58mm thermal, ESC/POS)
   ├─ Struk transaksi (header toko, item, total, kembalian)
   ├─ Label barcode produk + harga
   └─ Pemotongan kertas otomatis (GS V)

 Entity yang terkait (data penggerak cetak):
 ┌──────────────────┐          ┌─────────────────────────┐
 │   PRODUCTS       │          │   SALE_DOCUMENTS        │
 ├──────────────────┤          ├─────────────────────────┤
 │ id (PK)          │          │ id (PK)                 │
 │ name             │          │ invoice_number (UNIQUE) │
 │ barcode ← cetak! │          │ subtotal, total_price   │
 │ selling_price    │          │ paid_amount, change     │
 │ unit             │          │ payment_method, status  │
 └──────────────────┘          └───────────┬─────────────┘
        │                                  │ 1
        │ 1                                ├── has many ──► SALE_ITEMS
        └──► SALE_ITEMS (product_id, qty)  └── belongs to ─► USERS (kasir)
```

---

## 6. 🎛️ Config & Environment

File: `config/thermal.php` + `.env` (+ `TERM_*` di env network).

| Key | Nilai aktif (.env) | Keterangan |
|-----|--------------------|------------|
| `THERMAL_MODE` | `cups` | `browser` / `cups` / `raw` |
| `THERMAL_CUPS_QUEUE` | `GEZHI_micro_printer` | Nama queue CUPS |
| `THERMAL_DEVICE_PATH` | `/dev/cu.RPP02N` | Untuk mode `raw` (serial) |
| `THERMAL_BAUD_RATE` | `115200` | Kecepatan serial (hasil self-test) |
| `THERMAL_AUTO_CUT` | `true` | Potong kertas otomatis |
| `THERMAL_LINE_WIDTH` | `42` | Lebar baris monospace struk |
| `THERMAL_BARCODE_HEIGHT` | `50` | Tinggi barcode (dot printer) |
| `THERMAL_BARCODE_WIDTH` | `2` | Lebar bar barcode |
| `THERMAL_BARCODE_COPIES` | `1` | Salinan default label (browser) |
| `THERMAL_FLOW_CONTROL / CHUNK_* / VERIFY_*` | - | Tuning mode `raw` (serial/BT) |

---

## 7. 📁 File Kunci

| File | Peran |
|------|-------|
| `app/PrintConnectors/CupsRawPrintConnector.php` | Memanggil `lp -d <queue> -o document-format=application/vnd.cups-raw` (byte ESC/POS verbatim) — alpha pakai macOS modern |
| `app/PrintConnectors/BluetoothSerialPrintConnector.php` | Tulis serial/COM langsung (stty/mode COM, chunked write, probe health) untuk Windows/Linux |
| `app/Services/ThermalPrintService.php` | Render struk + barcode ESC/POS; pilih connector sesuai `THERMAL_MODE` |
| `app/Http/Controllers/PrintController.php` | `printReceipt`, `printBarcode`, `barcodeLabel` |
| `routes/web.php` | `sales.print-thermal`, `products.print-barcode`, `products.barcode-label` |
| `resources/views/admin/sales/show.blade.php` | Tombol Print Thermal + template 58mm (mode browser) |
| `resources/views/admin/products/barcode-label.blade.php` | Label barcode 58mm + JsBarcode (mode browser) |
| `resources/views/admin/products/index.blade.php` | Tombol Cetak Barcode + logika mode |
| `test-printer-debug.php` | Diagnostik device/channel/print (3 langkah) |
| `tools/` | Script & tool diagnostik (lihat §8) |

---

## 8. 🛠️ Tools & Setup (macOS)

```
tools/
├── setup-raw-cups.sh          # Uji cepat: kirim "RAW OK" via -o document-format
├── install-escpos-queue.sh    # [Cadangan] bikin queue passthrough (PPD raw) utk macOS
│                                └ memakai tools/raw.ppd (cupsFilter2 vnd.cups-raw)
├── raw.ppd                    # PPD passthrough ESC/POS
├── rpp02n_spp(.m)             # Tes SPP Bluetooth (diagnosa macOS-broken)
└── rpp02n_reconnect(.m)       # Reconnect SPP Bluetooth
```

**Jalur produksi (terbukti) di macOS — tanpa sudo:**
```bash
lp -d GEZHI_micro_printer -o document-format=application/vnd.cups-raw <byte_escpos.bin>
```
PHP memakai perintah yang sama via `CupsRawPrintConnector`.

---

## 9. 📜 Kronologi / Riwayat Pekerjaan

1. **Dari awal (tujuan aplikasi)** — halaman kasir + template struk 58mm (`#receipt`, CSS print).
2. **Percobaan Bluetooth macOS** — device `/dev/cu.RPP02N` ada tapi status `GS r` DEAD;
   dibuat `tools/rpp02n_spp` & `rpp02n_reconnect` → SPP ditolak `0xE00002BC`. **BT macOS mustahil.**
3. **Keputusan arsitektur hosting** — PHP di hosting cloud tidak melihat printer fisik →
   rencana bridge agent Windows + Tailscale (FR-09, planned).
4. **Tes USB** — terdeteksi sebagai *printer-class* "micro-printer" (GEZHI) bukan port serial
   (`/dev/cu.usb*` tidak ada). Tambah queue macOS → bisa print test page.
5. **Print browser `window.print()`** — keluar "bahasa mesin": driver *Generic PostScript* tidak
   cocok dengan printer ESC/POS.
6. **Diagnosa driver** — `lpstat -p -l` & PPD → `Generic PostScript Printer`; `-o raw` pada queue
   PS ternyata dibungkam (job hilang diam-diam).
7. **Solusi** — `-o document-format=application/vnd.cups-raw` → **BERHASIL** tercetak
   "RPP02N DOCFMT OK" pada printer.
8. **Integrasi PHP** — buat `CupsRawPrintConnector`, aktifkan `THERMAL_MODE=cups`,
   perbaiki CODE128 (`{B` prefix), aktifkan auto-cut.
9. **Validasi fisik** — barcode "Kopi Susu Gula Aren" + struk transaksi #4 **tercetak rapi &
   terpotong**; balasan service `success: true`.

---

## 10. 📊 Status Sekarang & Rencana Produksi

**Saat ini (macOS dev): OK — struk & barcode mencetak fisik.**
Uji lanjut yang dilakukan: `php -l` semua file, `php artisan view:cache`, `route:list`
(3 route print), tinker direkt print `success: true`, cek fisik kertas.

**Rencana produksi (Windows meja kasir):**

| Langkah | Detail |
|---------|--------|
| 1. Driver printer Windows | Instal RPP02N sebagai printer USB (ge. "Generic / Text Only" atau vendor) |
| 2. Bridge agent (planned) | Aplikasi kecil menerima `POST http://<ip-kasir>:8765/print` berisi byte ESC/POS → tulis ke printer (COM/lp) |
| 3. Tailscale | VPN antar kasir & setup agar server hosting bisa `POST` ke agent aman |
| 4. Cash drawer | Tombol "Buka Laci" → kirim `ESC p` (FR-08) |
| 5. `THERMAL_MODE` di server | `raw` (bridge/COM) bila printer tampil serial, atau `cups` bila ada queue |

---

## 11. ✅ Verifikasi / Cara Uji

```bash
# Render & cek route
php artisan route:list | grep -E 'print-thermal|print-barcode|barcode-label'
php artisan view:cache

# Tes langsung via terminal (pastikan printer USB tersambung & mode cups)
php artisan tinker --execute='
$svc = app(App\Services\ThermalPrintService::class);
$sale = App\Models\SaleDocument::orderByDesc("id")->first();
var_export($svc->printSaleDocument($sale));
'
```
Hasil akhir fungsi: `['success' => true, 'message' => 'Struk berhasil dikirim ke printer thermal.']`