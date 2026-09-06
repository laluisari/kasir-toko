@extends('layouts.main')

@section('title', 'Detail Struk/Nota')
@section('subTitle', 'Tampilkan Nota Penjualan')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <!-- Receipt Container -->
            <div id="receipt" class="card border-0 shadow-sm">
                <div class="card-body p-4" style="font-family: 'Courier New', Courier, monospace; font-size: 12px; line-height: 1.5; color: #212529;">
                    
                    <!-- Header -->
                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-1" style="font-family: 'Courier New', monospace;">🏪 TOKO SERBAGUNA</h5>
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
                <button class="btn btn-primary btn-sm flex-fill py-2 fw-semibold" onclick="printReceipt()">
                    🖨️ Print 
                </button>
                <button class="btn btn-secondary btn-sm flex-fill py-2 fw-semibold" onclick="downloadPDF()">
                    📄  PDF
                </button>
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
        max-width: 80mm;
        margin: auto;
        background: #ffffff;
    }

    @media print {
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
            width: 80mm;
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
    function printReceipt() {
        window.print();
    }

    function downloadPDF() {
        alert('Fitur download PDF sedang dikembangkan.');
    }
</script>
@endsection