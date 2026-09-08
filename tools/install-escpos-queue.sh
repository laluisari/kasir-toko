#!/usr/bin/env bash
#
# Instal queue ESC/POS passthrough untuk printer thermal RPP02N (GEZHI).
# macOS menolak `-m raw`, tapi PPD dengan cupsFilter2 raw tetap didukung.
# Jalankan:  sudo bash tools/install-escpos-queue.sh   (sekali saja)

set -euo pipefail

DIR="$(cd "$(dirname "$0")" && pwd)"
QUEUE="${1:-RPP02N}"
PPD="$DIR/raw.ppd"

echo "==> Mencari device USB printer thermal..."
URI=$(lpstat -v 2>/dev/null | grep -oE 'usb://[^ ]+' | sed -n '1p')
if [ -z "$URI" ]; then
    URI=$(lpinfo -v 2>/dev/null | grep -iE 'usb.*(GEZHI|micro|0289|0x0289|RPP)' | sed -n '1p' | sed -E 's/^.*(usb:[^ ]+) *$/\1/')
fi
[ -z "$URI" ] && { echo "!! Printer USB tidak ditemukan." >&2; exit 1; }
echo "    URI: $URI"

echo "==> (Re)buat queue '$QUEUE' dengan PPD passthrough..."
sudo lpadmin -x "$QUEUE" 2>/dev/null || true
sudo lpadmin -p "$QUEUE" -E -v "$URI" -P "$PPD"
sudo cupsenable "$QUEUE"
sudo cupsaccept "$QUEUE"

echo "==> Queue:"
lpstat -v "$QUEUE"
lpstat -p "$QUEUE"

echo "==> Tes ESC/POS (harusnya cetak 'RPP02N PPD RAW OK' + potong)..."
printf '\x1b\x40\x1d\x21\x11RPP02N PPD RAW OK\x1d\x21\x00\n\n\n\x1d\x56\x41\x00' > /tmp/rpp02n-ppd.bin
lp -d "$QUEUE" -o document-format=application/vnd.cups-raw /tmp/rpp02n-ppd.bin

echo ""
echo "Selesai. Set THERMAL_CUPS_QUEUE=$QUEUE di .env agar aplikasi memakainya."