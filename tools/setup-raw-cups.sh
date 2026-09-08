#!/usr/bin/env bash
#
# Tes kirim byte ESC/POS mentah ke printer thermal RPP02N (GEZHI micro-printer)
# lewat queue CUPS yang sudah ada + opsi `-o raw` (tanpa filter driver).
#
# macOS tidak mendukung queue raw (`lpadmin -m raw` ditolak),
# tapi opsi job `-o raw` tetapkan: byte dikirim apa adanya ke printer.
#
# Jalankan:  bash tools/setup-raw-cups.sh

set -euo pipefail

# Queue printer thermal (cek dgn: lpstat -p)
QUEUE="${1:-GEZHI_micro_printer}"

if ! lpstat -p "$QUEUE" 2>/dev/null | grep -qi "$QUEUE"; then
    echo "!! Queue '$QUEUE' tidak ada. Daftar printer:" >&2
    lpstat -p 2>/dev/null | grep -i shas | sed 's/^/   - /' || true
    echo "   Pakai nama queue yang benar: bash tools/setup-raw-cups.sh NAMA_QUEUE" >&2
    exit 1
fi

echo "==> Queue: $QUEUE"
lpstat -v "$QUEUE"

echo "==> Kirim tes ESC/POS (teks tebal + feed + potong kertas)..."
printf '\x1b\x40\x1d\x21\x11RPP02N RAW OK\x1d\x21\x00\n\n\n\x1d\x56\x41\x00' > /tmp/rpp02n-test.bin
lp -d "$QUEUE" -o raw /tmp/rpp02n-test.bin

echo ""
echo "Selesai. Harusnya tercetak 'RPP02N RAW OK' lalu kertas terpotong."
echo "Aplikasi pakai queue ini lewat THERMAL_CUPS_QUEUE (default GEZHI_micro_printer)."