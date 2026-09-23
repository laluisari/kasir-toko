# print-bridge

Jembatan kecil di **Windows meja kasir** yang menerima data cetak dari browser lalu
mengirimkannya ke printer thermal lewat spooler Windows (RAW). Tidak butuh driver vendor
(Rongta) — cukup queue **"Generic / Text Only"** yang sudah terbukti mencetak.

> Mengapa begini? Server (VPS) tidak bisa menyentuh bluetooth/COM laptop. Tapi **browser di
> laptop itulah yang menjalankan JS**, jadi browser mengirim `POST` ke `127.0.0.1:8765`
> (bridge lokal) → bridge tulis byte ke printer. Tidak perlu Tailscale.

## Alur

```
[ Browser di Windows: https://lebbok.my.id ]
        │  fetch POST http://127.0.0.1:8765/print
        ▼
[ print-bridge (Flask + win32print) ]
        │  OpenPrinter("Generic / Text Only") → StartDoc RAW
        ▼
[ Queue "Generic / Text Only" → COM5 Bluetooth ]
        ▼
[ RPP02N ]
```

## Setup (sekali saja, di Windows)

1. Pastikan Python 3 sudah terpasang (`python --version`).
2. Pasang dependensi:
   ```
   pip install -r requirements.txt
   ```
3. Edit `config.json` — sesuaikan `printer` dengan nama queue di Windows
   (jalankan `/health` atau cek Printers & Scanners untuk nama persisnya).
4. Jalankan:
   ```
   python app.py
   ```
   Di layar muncul: `Bridge printer aktif: ... @ http://127.0.0.1:8765`.

## Endpoint

| Method | Path    | Body | Keterangan |
|--------|---------|------|------------|
| GET    | `/health` | - | Nama printer aktif + daftar printer lokal |
| POST   | `/print` | `{ "content": "TEXT\n..." }` | Cetak teks polos (UTF-8) |
| POST   | `/print` | `{ "base64": "..." }` | Cetak byte mentah ESC/POS (base64) |

Respons: `{ "success": true, "message": "..." }` (200) atau `{ "error": "..." }`.

## Menjalankan otomatis (opsional)

Buat Task Scheduler (Publikasi startup) yang menjalankan `python app.py` di folder ini,
biar bridge selalu hidup sebelum kasir menekan tombol print.

## Integrasi web

Di sisi web cukup pasang `THERMAL_MODE=bridge` — JS pada halaman akan mengirim struk/barcode
ke `http://127.0.0.1:8765/print`. (Lihat `routes/web.php` & views bagian print.)