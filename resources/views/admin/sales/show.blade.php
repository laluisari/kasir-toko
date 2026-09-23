@extends('layouts.main')

@section('title', 'Detail Struk/Nota')
@section('subTitle', 'Tampilkan Nota Penjualan')

@section('content')
@php
    $lineWidth = (int) config('thermal.line_width', 42);
    $storeName = (string) config('thermal.store_name', 'TOKO SERBAGUNA');
    $storeAddress = (string) config('thermal.store_address', 'Jl. Raya No. 123');
    $money = fn (int $v) => number_format($v, 0, ',', '.');
    $trim = fn (string $s, int $w) => rtrim(mb_strimwidth($s, 0, $w, '', 'UTF-8'));
    $div = str_repeat('-', $lineWidth);

    $rectext = [];
    $rectext[] = str_pad($storeName, $lineWidth, ' ', STR_PAD_BOTH);
    $rectext[] = str_pad($storeAddress, $lineWidth, ' ', STR_PAD_BOTH);
    $rectext[] = $div;
    $rectext[] = $saleDocument->invoice_number ?? '-';
    $rectext[] = 'Kasir: ' . ($saleDocument->user->name ?? 'Admin');
    $rectext[] = 'Tanggal: ' . optional($saleDocument->created_at)->format('d/m/Y H:i') ?? '-';
    $rectext[] = 'Metode: ' . strtoupper((string) $saleDocument->payment_method);
    if ($saleDocument->buyer) {
        $rectext[] = 'Pelanggan: ' . $saleDocument->buyer->name;
        if (!empty($saleDocument->buyer->phone)) {
            $rectext[] = 'No. HP: ' . $saleDocument->buyer->phone;
        }
    }
    if (($saleDocument->payment_type ?? 'full') === 'debt' && !empty($saleDocument->due_date)) {
        $rectext[] = 'Jatuh Tempo: ' . date('d/m/Y', strtotime((string) $saleDocument->due_date));
    }
    $rectext[] = $div;

    $calGross = 0;
    $calDiscount = 0;
    foreach ($saleDocument->sales as $sale) {
        $qty = (int) $sale->quantity;
        $price = (int) $sale->selling_price;
        $gross = $price * $qty;
        $discount = ((int) ($sale->discount ?? 0)) * $qty;
        $calGross += $gross;
        $calDiscount += $discount;

        $rectext[] = $trim((string) $sale->product_name, $lineWidth);
        $left = $qty . ' x @ Rp ' . $money($price);
        $right = 'Rp ' . $money($gross);
        $right = $trim($right, $lineWidth - 5);
        $left = $trim($left, max(1, $lineWidth - mb_strwidth($right) - 1));
        $pad = max(1, $lineWidth - mb_strwidth($left) - mb_strwidth($right));
        $rectext[] = $left . str_repeat(' ', $pad) . $right;
        if ((int) ($sale->discount ?? 0) > 0) {
            $rectext[] = 'Diskon: -Rp ' . $money($discount);
        }
    }

    $rectext[] = $div;
    $total = $calGross - $calDiscount;
    $rectext[] = 'Subtotal: Rp ' . $money($calGross);
    if ($calDiscount > 0) {
        $rectext[] = 'Total Diskon: -Rp ' . $money($calDiscount);
    }
    $rectext[] = '>>> TOTAL: Rp ' . $money($total);

    if (($saleDocument->payment_type ?? 'full') === 'debt') {
        $rectext[] = 'Bayar Awal: Rp ' . $money((int) $saleDocument->down_payment);
        $rectext[] = '>>> SISA HUTANG: Rp ' . $money((int) $saleDocument->debt_remaining);
        if (!empty($saleDocument->debt_note)) {
            $rectext[] = $trim('Catatan: ' . $saleDocument->debt_note, $lineWidth);
        }
    } else {
        $rectext[] = 'Uang Diterima: Rp ' . $money((int) $saleDocument->paid_amount);
        $rectext[] = '>>> Kembalian: Rp ' . $money((int) $saleDocument->change_amount);
    }
    $rectext[] = $div;
    $rectext[] = str_pad('Terima kasih telah berbelanja', $lineWidth, ' ', STR_PAD_BOTH);
    $rectext[] = str_pad(optional($saleDocument->created_at)->format('d M Y H:i:s') ?? '-', $lineWidth, ' ', STR_PAD_BOTH);
    $rectext[] = '';

    $rectext = implode("\n", $rectext) . "\n";
@endphp
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <!-- Receipt Container -->
            <div id="receipt" class="card border-0 shadow-sm">
                <div class="card-body p-4" style="font-family: 'Courier New', Courier, monospace; font-size: 12px; line-height: 1.5; color: #212529;">
                    
                    <!-- Header -->
                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-1" style="font-family: 'Courier New', monospace;">TOKO SERBAGUNA</h5>
                        <div class="text-muted small">Jl. Raya No. 123</div>
                    </div>

                    <div class="receipt-divider"></div>

                    <!-- Invoice Info -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>No. Invoice:</span>
                            <span class="fw-bold text-break text-end ms-2" style="max-width: 160px;">{{ $saleDocument->invoice_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Kasir:</span>
                            <strong>{{ $saleDocument->user->name ?? 'Admin' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tanggal:</span>
                            <strong>{{ $saleDocument->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Metode:</span>
                            <strong>{{ strtoupper($saleDocument->payment_method) }}</strong>
                        </div>
                        @if($saleDocument->buyer)
                        <div class="d-flex justify-content-between mb-1">
                            <span>Pelanggan:</span>
                            <strong>{{ $saleDocument->buyer->name ?? '-' }}</strong>
                        </div>
                        @if(!empty($saleDocument->buyer->phone))
                        <div class="d-flex justify-content-between mb-1">
                            <span>No. HP:</span>
                            <strong>{{ $saleDocument->buyer->phone }}</strong>
                        </div>
                        @endif
                        @endif
                        @if(($saleDocument->payment_type ?? 'full') === 'debt')
                        <div class="d-flex justify-content-between">
                            <span>Jatuh Tempo:</span>
                            <strong>{{ \Illuminate\Support\Carbon::parse($saleDocument->due_date)->format('d/m/Y') }}</strong>
                        </div>
                        @endif
                    </div>

                    <div class="receipt-divider"></div>

                    <!-- Items -->
                    @php
                        $calculated_gross_subtotal = 0;
                        $calculated_discount_total = 0;
                    @endphp

                    <div class="mb-3">
                        @foreach($saleDocument->sales as $sale)
                        @php
                            $item_gross = $sale->selling_price * $sale->quantity;
                            $item_discount = ($sale->discount ?? 0) * $sale->quantity;
                            
                            $calculated_gross_subtotal += $item_gross;
                            $calculated_discount_total += $item_discount;
                        @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between fw-bold">
                                <span class="text-truncate me-2" style="max-width: 180px;">{{ $sale->product_name }}</span>
                                <span>{{ $sale->quantity }}x</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted">
                                <span>@ Rp {{ number_format($sale->selling_price, 0, ',', '.') }}</span>
                                <span>Rp {{ number_format($item_gross, 0, ',', '.') }}</span>
                            </div>
                            @if($sale->discount > 0)
                            <div class="d-flex justify-content-between text-danger">
                                <span class="ps-2">↳ Diskon:</span>
                                <span>-Rp {{ number_format($item_discount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <div class="receipt-divider"></div>

                    <!-- Summary -->
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Subtotal:</span>
                            <strong>Rp {{ number_format($calculated_gross_subtotal, 0, ',', '.') }}</strong>
                        </div>
                        
                        @if($calculated_discount_total > 0)
                        <div class="d-flex justify-content-between mb-1 text-danger">
                            <span>Total Diskon:</span>
                            <strong>-Rp {{ number_format($calculated_discount_total, 0, ',', '.') }}</strong>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between my-2 pt-2 border-top border-dark fw-bold" style="font-size: 14px;">
                            <span>Total Harga:</span>
                            <span>Rp {{ number_format($calculated_gross_subtotal - $calculated_discount_total, 0, ',', '.') }}</span>
                        </div>

                        @if(($saleDocument->payment_type ?? 'full') === 'debt')
                        <div class="d-flex justify-content-between mb-1 pt-1">
                            <span>Pembayaran Awal:</span>
                            <strong>Rp {{ number_format($saleDocument->down_payment, 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between fw-bold text-danger" style="font-size: 13px;">
                            <span>Sisa Hutang:</span>
                            <span>Rp {{ number_format($saleDocument->debt_remaining, 0, ',', '.') }}</span>
                        </div>
                        @if($saleDocument->debt_note)
                        <div class="mt-2 small text-muted">
                            Catatan: {{ $saleDocument->debt_note }}
                        </div>
                        @endif
                        @if($saleDocument->debtPayments->count() > 0)
                        <div class="receipt-divider"></div>
                        <div class="fw-bold mb-1">Riwayat Pembayaran</div>
                        @foreach($saleDocument->debtPayments->sortBy('paid_at') as $payment)
                        <div class="d-flex justify-content-between">
                            <span>{{ $payment->paid_at->format('d/m H:i') }}</span>
                            <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
                            <span>{{ strtoupper($payment->payment_method) }} · {{ $payment->user->name ?? 'Admin' }}</span>
                            <span></span>
                        </div>
                        @endforeach
                        @endif
                        @else
                        <div class="d-flex justify-content-between mb-1 pt-1">
                            <span>Uang Diterima:</span>
                            <strong>Rp {{ number_format($saleDocument->paid_amount, 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between fw-bold text-success" style="font-size: 13px;">
                            <span>Kembalian:</span>
                            <span>Rp {{ number_format($saleDocument->change_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="receipt-divider"></div>

                    <!-- Footer -->
                    <div class="text-center text-muted pt-1">
                        <div class="fw-semibold">Terima kasih telah berbelanja</div>
                        <div style="font-size: 10px;" class="mt-1">{{ $saleDocument->created_at->format('d M Y H:i:s') }}</div>
                    </div>

                </div>
            </div>

            <!-- Action Buttons (Responsive & Compact) -->
            <div class="mt-3 d-flex gap-2 justify-content-center d-print-none">
                <button id="printThermalBtn" class="btn btn-dark btn-sm flex-fill py-2 fw-semibold" onclick="printThermalReceipt()">
                    Print Thermal
                </button>
                @if (config('thermal.print_mode') === 'browser')
                <button class="btn btn-primary btn-sm flex-fill py-2 fw-semibold" onclick="printReceipt()">
                    🖨️ Print 
                </button>
                <button class="btn btn-secondary btn-sm flex-fill py-2 fw-semibold" onclick="downloadPDF()">
                    📄  PDF
                </button>
                @endif
                <a href="{{ route('sales.index') }}" class="btn btn-success btn-sm flex-fill py-2 fw-semibold d-flex align-items-center justify-content-center">
                    🛒 New
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashed Line separator gaya thermal receipt */
    .receipt-divider {
        border-top: 1px dashed #999;
        margin: 10px 0;
    }

    #receipt {
        max-width: 58mm;
        margin: auto;
        background: #ffffff;
    }

    @media print {
        @page {
            size: 58mm auto;
            margin: 0;
        }

        body * {
            visibility: hidden;
        }

        #receipt, #receipt * {
            visibility: visible;
        }

        #receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 58mm;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
        }

        .d-print-none {
            display: none !important;
        }
    }
</style>

<script>
    const PRINT_MODE = @json(config('thermal.print_mode'));
    const BRIDGE_URL = @json(config('thermal.bridge_url'));
    const BRIDGE_PRINTER = @json(config('thermal.bridge_printer', ''));

    const RECEIPT_TEXT = @json($rectext ?? '');

    async function printViaBridge(content) {
        const button = document.getElementById('printThermalBtn');

        if (button) {
            button.disabled = true;
            button.textContent = 'Printing...';
        }

        try {
            const body = { content: content };
            if (BRIDGE_PRINTER) {
                body.printer = BRIDGE_PRINTER;
            }

            const response = await fetch(BRIDGE_URL + '/print', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || data.message || 'Bridge gagal mencetak.');
            }

            alert(data.message || 'Struk berhasil dikirim ke printer.');
        } catch (error) {
            alert('Print bridge gagal: ' + (error.message || 'bridge tidak terjangkau.') + "\nPastikan bridge berjalan di " + BRIDGE_URL);
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = 'Print Thermal';
            }
        }
    }

    async function printThermalReceipt() {
        if (PRINT_MODE === 'bridge') {
            return printViaBridge(RECEIPT_TEXT);
        }

        if (PRINT_MODE === 'browser') {
            return printReceipt();
        }

        const button = document.getElementById('printThermalBtn');

        if (button) {
            button.disabled = true;
            button.textContent = 'Printing...';
        }

        try {
            const response = await fetch("{{ route('sales.print-thermal', $saleDocument) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal mengirim data ke printer thermal.');
            }

            alert(data.message || 'Struk berhasil diprint.');
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan saat print thermal.');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = 'Print Thermal';
            }
        }
    }

    function printReceipt() {
        window.print();
    }

    function downloadPDF() {
        alert('Fitur download PDF sedang dikembangkan.');
    }
</script>
@endsection