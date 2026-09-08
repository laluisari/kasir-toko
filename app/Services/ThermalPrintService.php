<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SaleDocument;
use App\PrintConnectors\BluetoothSerialPrintConnector;
use App\PrintConnectors\CupsRawPrintConnector;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Throwable;

class ThermalPrintService
{
    private function isCupsMode(): bool
    {
        return (string) config('thermal.print_mode', 'browser') === 'cups';
    }

    private function makeConnector(): PrintConnector
    {
        if ($this->isCupsMode()) {
            return new CupsRawPrintConnector((string) config('thermal.cups_queue', 'GEZHI_micro_printer'));
        }

        return new BluetoothSerialPrintConnector(
            (string) config('thermal.device_path', '/dev/cu.RPP02N'),
            (int) config('thermal.baud_rate', 9600),
            (bool) config('thermal.flow_control', false),
            (int) config('thermal.chunk_size', 64),
            (int) config('thermal.chunk_delay_us', 20000)
        );
    }

    public function printSaleDocument(SaleDocument $saleDocument): array
    {
        $devicePath = (string) config('thermal.device_path', '/dev/cu.RPP02N');

        Log::info('Thermal Print: start', [
            'sale_id' => $saleDocument->id,
            'invoice' => $saleDocument->invoice_number,
            'device' => $devicePath,
        ]);

        if (!$this->isCupsMode()) {
            if (!file_exists($devicePath)) {
                Log::error('Thermal Print: device not found', [
                    'sale_id' => $saleDocument->id,
                    'device' => $devicePath,
                ]);

                return [
                    'success' => false,
                    'message' => "Printer tidak ditemukan di {$devicePath}.",
                ];
            }

            if (!is_writable($devicePath)) {
                Log::error('Thermal Print: device not writable', [
                    'sale_id' => $saleDocument->id,
                    'device' => $devicePath,
                ]);

                return [
                    'success' => false,
                    'message' => "Printer tidak siap. Pastikan perangkat {$devicePath} terhubung dan bisa diakses.",
                ];
            }
        }

        $saleDocument->loadMissing(['user', 'sales', 'buyer']);

        if (!$this->isCupsMode() && (bool) config('thermal.verify_channel', false)) {
            $probe = BluetoothSerialPrintConnector::probeChannel(
                $devicePath,
                (float) config('thermal.verify_timeout', 1.5)
            );

            if (!$probe['alive']) {
                $message = "Data channel printer mati ({$probe['reason']}). "
                    . $this->macOsHint();

                Log::error('Thermal Print: data channel dead', [
                    'sale_id' => $saleDocument->id,
                    'reason' => $probe['reason'],
                ]);

                return [
                    'success' => false,
                    'message' => $message,
                ];
            }
        }

        $printer = null;

        try {
            Log::debug('Thermal Print: opening connector', [
                'sale_id' => $saleDocument->id,
                'device' => $devicePath,
            ]);

            $connector = $this->makeConnector();
            $printer = new Printer($connector);

            Log::debug('Thermal Print: rendering receipt', [
                'sale_id' => $saleDocument->id,
                'items_count' => $saleDocument->sales->count(),
            ]);

            $this->renderReceipt($printer, $saleDocument);

            if ((bool) config('thermal.auto_cut', true)) {
                $printer->cut();
                Log::debug('Thermal Print: cut executed', [
                    'sale_id' => $saleDocument->id,
                ]);
            }

            $printer->close();

            Log::info('Thermal Print: success', [
                'sale_id' => $saleDocument->id,
                'invoice' => $saleDocument->invoice_number,
            ]);

            return [
                'success' => true,
                'message' => 'Struk berhasil dikirim ke printer thermal.',
            ];
        } catch (Throwable $e) {
            Log::error('Thermal Print: failed', [
                'sale_id' => $saleDocument->id,
                'invoice' => $saleDocument->invoice_number,
                'device' => $devicePath,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($printer) {
                try {
                    $printer->close();
                } catch (Throwable $closeException) {
                    Log::warning('Thermal Print: close failed after exception', [
                        'sale_id' => $saleDocument->id,
                        'message' => $closeException->getMessage(),
                    ]);
                }
            }

            return [
                'success' => false,
                'message' => 'Gagal print thermal: ' . $e->getMessage(),
            ];
        }
    }

    public function printBarcode(Product $product, int $copies = 1): array
    {
        $devicePath = (string) config('thermal.device_path', '/dev/cu.RPP02N');
        $barcode = trim((string) $product->barcode);

        if ($barcode === '') {
            return [
                'success' => false,
                'message' => "Produk '{$product->name}' belum punya kode barcode.",
            ];
        }

        Log::info('Thermal Print Barcode: start', [
            'product_id' => $product->id,
            'barcode' => $barcode,
            'device' => $devicePath,
        ]);

        if (!$this->isCupsMode()) {
            if (!file_exists($devicePath)) {
                Log::error('Thermal Print Barcode: device not found', [
                    'product_id' => $product->id,
                    'device' => $devicePath,
                ]);

                return [
                    'success' => false,
                    'message' => "Printer tidak ditemukan di {$devicePath}.",
                ];
            }

            if (!is_writable($devicePath)) {
                Log::error('Thermal Print Barcode: device not writable', [
                    'product_id' => $product->id,
                    'device' => $devicePath,
                ]);

                return [
                    'success' => false,
                    'message' => "Printer tidak siap. Pastikan perangkat {$devicePath} terhubung dan bisa diakses.",
                ];
            }
        }

        $printer = null;

        try {
            $connector = $this->makeConnector();
            $printer = new Printer($connector);

            $copies = max(1, min(100, $copies));

            for ($i = 0; $i < $copies; $i++) {
                $this->renderBarcode($printer, $product, $barcode);
            }

            if ((bool) config('thermal.auto_cut', false)) {
                $printer->cut();
            }

            $printer->close();

            Log::info('Thermal Print Barcode: success', [
                'product_id' => $product->id,
                'copies' => $copies,
            ]);

            return [
                'success' => true,
                'message' => "Barcode '{$product->name}' berhasil dicetak ({$copies}x).",
            ];
        } catch (Throwable $e) {
            Log::error('Thermal Print Barcode: failed', [
                'product_id' => $product->id,
                'device' => $devicePath,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            if ($printer) {
                try {
                    $printer->close();
                } catch (Throwable $closeException) {
                    Log::warning('Thermal Print Barcode: close failed after exception', [
                        'product_id' => $product->id,
                        'message' => $closeException->getMessage(),
                    ]);
                }
            }

            return [
                'success' => false,
                'message' => 'Gagal print barcode: ' . $e->getMessage(),
            ];
        }
    }

    private function renderBarcode(Printer $printer, Product $product, string $barcode): void
    {
        $lineWidth = (int) config('thermal.line_width', 42);

        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text($this->trimToWidth((string) $product->name, $lineWidth) . "\n");
        $printer->setEmphasis(false);

        $type = $this->barcodeType($barcode);
        $barcodeData = $barcode;

        if ($type === Printer::BARCODE_CODE128 && strpos($barcodeData, '{') !== 0) {
            $barcodeData = '{B' . $barcodeData;
        }

        $printer->setBarcodeHeight((int) config('thermal.barcode_height', 50));
        $printer->setBarcodeWidth((int) config('thermal.barcode_width', 2));
        $printer->barcode($barcodeData, $type);

        $price = 'Rp ' . $this->money((int) $product->selling_price);
        $unit = (string) ($product->unit ?: 'pcs');

        $this->setTextSize($printer);

        $this->line($printer, $price, $unit, $lineWidth);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $split = $this->trimToWidth($barcode, $lineWidth);

        $centered = (int) (($lineWidth - mb_strwidth($split)) / 2);
        if ($centered < 1) {
            $centered = 1;
        }

        $printer->text(str_repeat(' ', $centered) . $split . "\n");
        $printer->feed(3);
    }

    private function setTextSize(Printer $printer): void
    {
        try {
            $printer->setTextSize(2, 2);
        } catch (Throwable $e) {
            $printer->setTextSize(2, 1);
        }
    }

    private function barcodeType(string $barcode): int
    {
        $digits = preg_match('/^\d+$/', $barcode) === 1;
        $length = mb_strlen($barcode);

        if ($digits && $length === 13) {
            return Printer::BARCODE_JAN13;
        }

        if ($digits && $length === 8) {
            return Printer::BARCODE_JAN8;
        }

        return Printer::BARCODE_CODE128;
    }

    private function renderReceipt(Printer $printer, SaleDocument $saleDocument): void
    {
        $lineWidth = (int) config('thermal.line_width', 42);
        $storeName = (string) config('thermal.store_name', 'TOKO SERBAGUNA');
        $storeAddress = (string) config('thermal.store_address', 'Jl. Raya No. 123');

        // Initialize printer (reset to default state)
        $printer->initialize();
        Log::debug('Thermal Print: printer initialized', ['sale_id' => null]);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text($storeName . "\n");
        $printer->setEmphasis(false);
        $printer->text($storeAddress . "\n");

        $this->divider($printer, $lineWidth);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $this->line($printer, 'No. Invoice', $saleDocument->invoice_number ?? '-', $lineWidth);
        $this->line($printer, 'Kasir', $saleDocument->user->name ?? 'Admin', $lineWidth);
        $this->line($printer, 'Tanggal', optional($saleDocument->created_at)->format('d/m/Y H:i') ?? '-', $lineWidth);
        $this->line($printer, 'Metode', strtoupper((string) $saleDocument->payment_method), $lineWidth);

        if ($saleDocument->buyer) {
            $this->line($printer, 'Pelanggan', (string) $saleDocument->buyer->name, $lineWidth);
            if (!empty($saleDocument->buyer->phone)) {
                $this->line($printer, 'No. HP', (string) $saleDocument->buyer->phone, $lineWidth);
            }
        }

        if (($saleDocument->payment_type ?? 'full') === 'debt' && !empty($saleDocument->due_date)) {
            $this->line($printer, 'Jatuh Tempo', date('d/m/Y', strtotime((string) $saleDocument->due_date)), $lineWidth);
        }

        $this->divider($printer, $lineWidth);

        $calculatedGrossSubtotal = 0;
        $calculatedDiscountTotal = 0;

        foreach ($saleDocument->sales as $sale) {
            $qty = (int) $sale->quantity;
            $price = (int) $sale->selling_price;
            $itemGross = $price * $qty;
            $itemDiscount = ((int) ($sale->discount ?? 0)) * $qty;

            $calculatedGrossSubtotal += $itemGross;
            $calculatedDiscountTotal += $itemDiscount;

            $name = $this->trimToWidth((string) $sale->product_name, $lineWidth);
            $printer->setEmphasis(true);
            $printer->text($name . "\n");
            $printer->setEmphasis(false);

            $this->line(
                $printer,
                $qty . ' x @ Rp ' . $this->money($price),
                'Rp ' . $this->money($itemGross),
                $lineWidth
            );

            if ((int) ($sale->discount ?? 0) > 0) {
                $this->line($printer, 'Diskon', '-Rp ' . $this->money($itemDiscount), $lineWidth);
            }
        }

        $this->divider($printer, $lineWidth);

        $totalHarga = $calculatedGrossSubtotal - $calculatedDiscountTotal;

        $this->line($printer, 'Subtotal', 'Rp ' . $this->money($calculatedGrossSubtotal), $lineWidth);
        if ($calculatedDiscountTotal > 0) {
            $this->line($printer, 'Total Diskon', '-Rp ' . $this->money($calculatedDiscountTotal), $lineWidth);
        }

        $printer->setEmphasis(true);
        $this->line($printer, 'Total Harga', 'Rp ' . $this->money($totalHarga), $lineWidth);
        $printer->setEmphasis(false);

        if (($saleDocument->payment_type ?? 'full') === 'debt') {
            $this->line($printer, 'Bayar Awal', 'Rp ' . $this->money((int) $saleDocument->down_payment), $lineWidth);
            $printer->setEmphasis(true);
            $this->line($printer, 'Sisa Hutang', 'Rp ' . $this->money((int) $saleDocument->debt_remaining), $lineWidth);
            $printer->setEmphasis(false);
            if (!empty($saleDocument->debt_note)) {
                $printer->text('Catatan: ' . $this->trimToWidth((string) $saleDocument->debt_note, $lineWidth) . "\n");
            }
        } else {
            $this->line($printer, 'Uang Diterima', 'Rp ' . $this->money((int) $saleDocument->paid_amount), $lineWidth);
            $printer->setEmphasis(true);
            $this->line($printer, 'Kembalian', 'Rp ' . $this->money((int) $saleDocument->change_amount), $lineWidth);
            $printer->setEmphasis(false);
        }

        $this->divider($printer, $lineWidth);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Terima kasih telah berbelanja\n");
        $printer->text((optional($saleDocument->created_at)->format('d M Y H:i:s') ?? '-') . "\n");
        $printer->feed(3);
    }

    private function divider(Printer $printer, int $lineWidth): void
    {
        $printer->text(str_repeat('-', $lineWidth) . "\n");
    }

    private function line(Printer $printer, string $left, string $right, int $lineWidth): void
    {
        $right = $this->trimToWidth($right, $lineWidth - 5);
        $leftMax = max(1, $lineWidth - mb_strwidth($right) - 1);
        $left = $this->trimToWidth($left, $leftMax);

        $spaces = $lineWidth - mb_strwidth($left) - mb_strwidth($right);
        if ($spaces < 1) {
            $spaces = 1;
        }

        $printer->text($left . str_repeat(' ', $spaces) . $right . "\n");
    }

    private function trimToWidth(string $text, int $maxWidth): string
    {
        return rtrim(mb_strimwidth($text, 0, $maxWidth, '', 'UTF-8'));
    }

    private function money(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    private function macOsHint(): string
    {
        if (PHP_OS_FAMILY !== 'Darwin') {
            return '';
        }

        return 'Catatan macOS: printer bluetooth ini tidak bisa diprint langsung dari server macOS '
            . '(SPP channel ditutup OS). Solusi: jalankan aplikasi di Windows/Linux '
            . '(FilePrintConnector ke COM/rfcomm berfungsi) atau print lewat browser/re-pair printer.';
    }
}
