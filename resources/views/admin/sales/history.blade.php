@extends('layouts.main')

@section('title', 'Riwayat Penjualan')
@section('subTitle', 'Pantau, cari, dan analisis seluruh transaksi')

@section('content')
<div class="container-fluid mb-4">
    
    <!-- Top Bar: Title Context & Primary Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <!-- Quick Filters Preset -->
        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
            <a href="?range=today" class="btn {{ request('range') == 'today' ? 'btn-primary' : 'btn-white border' }} btn-sm px-3 fw-medium">Hari Ini</a>
            <a href="?range=7days" class="btn {{ request('range') == '7days' ? 'btn-primary' : 'btn-white border' }} btn-sm px-3 fw-medium">7 Hari</a>
            <a href="?range=30days" class="btn {{ request('range') == '30days' ? 'btn-primary' : 'btn-white border' }} btn-sm px-3 fw-medium">30 Hari</a>
        </div>

        {{-- <button class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-2 fw-medium px-3 rounded-3" onclick="alert('Fitur Ekspor Laporan sedang dikembangkan.')">
            <iconify-icon icon="solar:document-text-bold" class="fs-6"></iconify-icon> Ekspor Laporan
        </button> --}}
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label for="searchInvoice" class="form-label text-muted small fw-medium mb-1">Cari Invoice</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" id="searchInvoice" name="invoice" value="{{ request('invoice') }}" placeholder="Contoh: INV-2026...">
                    </div>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label for="fromDate" class="form-label text-muted small fw-medium mb-1">Dari Tanggal</label>
                    <input type="date" class="form-control form-control-sm rounded-3 py-2" id="fromDate" name="from_date" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-4 col-lg-2">
                    <label for="toDate" class="form-label text-muted small fw-medium mb-1">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm rounded-3 py-2" id="toDate" name="to_date" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label for="userFilter" class="form-label text-muted small fw-medium mb-1">Kasir</label>
                    <select class="form-select form-select-sm rounded-3 py-2" id="userFilter" name="user_id">
                        <option value="">Semua Kasir</option>
                        @php
                        $users = \App\Models\User::where('role', 'kasir')->orWhere('role', 'admin')->get();
                        @endphp
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-6 col-lg-2 ms-auto">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 fw-medium flex-grow-1 py-2">Terapkan</button>
                        <a href="{{ route('sales.history') }}" class="btn btn-outline-secondary btn-sm rounded-3 fw-medium px-3 py-2">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        @php
        $totalSales = $saleDocuments->sum('total_price');
        $totalTransactions = $saleDocuments->total();
        $totalItems = 0;
        foreach($saleDocuments as $doc) {
            foreach($doc->sales as $sale) {
                $totalItems += $sale->quantity;
            }
        }
        @endphp

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center p-3 me-3">
                        <iconify-icon icon="solar:wallet-bold" width="24" height="24"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-medium">Total Penjualan</p>
                        <h5 class="text-primary mb-0 fw-bold fs-6 fs-md-5">Rp {{ number_format($totalSales, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center p-3 me-3">
                        <iconify-icon icon="solar:bill-list-bold" width="24" height="24"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-medium">Total Transaksi</p>
                        <h5 class="text-dark mb-0 fw-bold fs-6 fs-md-5">{{ number_format($totalTransactions, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center p-3 me-3">
                        <iconify-icon icon="solar:box-minimalistic-line-duotone" width="24" height="24"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-medium">Total Item Terjual</p>
                        <h5 class="text-dark mb-0 fw-bold fs-6 fs-md-5">{{ number_format($totalItems, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center p-3 me-3">
                        <iconify-icon icon="solar:graph-bold" width="24" height="24"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small fw-medium">Rata-rata Transaksi</p>
                        <h5 class="text-dark mb-0 fw-bold fs-6 fs-md-5">Rp {{ number_format($totalTransactions > 0 ? $totalSales / $totalTransactions : 0, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm rounded-4 mt-10">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold text-dark" style="font-size: 0.95rem;">Daftar Transaksi</h6>
            <span class="badge bg-light text-secondary rounded-pill border px-3 py-1">{{ $totalTransactions }} Transaksi</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover border-top mb-0 align-middle">
                <thead class="table-light text-muted small" style="background-color: #f8f9fa;">
                    <tr>
                        <th class="fw-semibold px-4 py-4 border-bottom-0">NO. INVOICE</th>
                        <th class="text-center fw-semibold border-bottom-0 px-4 py-4">TANGGAL & WAKTU</th>
                        <th class="text-center fw-semibold border-bottom-0 px-4 py-4">KASIR</th>
                        <th class="text-center fw-semibold border-bottom-0 px-4 py-4">ITEM</th>
                        <th class="text-center fw-semibold border-bottom-0 px-4 py-4">TOTAL (NET)</th>
                        <th class="text-center fw-semibold border-bottom-0 px-4 py-4">METODE</th>
                        <th class="text-center fw-semibold border-bottom-0 px-4 py-4">STATUS</th>
                        <th class="text-center px-4 fw-semibold border-bottom-0 py-4">AKSI</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($saleDocuments as $sale)
                    @php
                        // Format Truncate Invoice ID
                        $fullInvoice = $sale->invoice_number;
                        $shortInvoice = strlen($fullInvoice) > 14 
                            ? substr($fullInvoice, 0, 4) . '...' . substr($fullInvoice, -4) 
                            : $fullInvoice;
                    @endphp
                    <tr>
                        <td class="px-4 py-4">
                            <!-- Truncated Invoice ID with Tooltip & Copy Trigger -->
                            <span class="font-monospace text-dark fw-bold invoice-copy-btn" 
                                  style="font-size: 0.85rem; cursor: pointer;"
                                  data-bs-toggle="tooltip" 
                                  data-bs-placement="top" 
                                  title="Klik untuk salin: {{ $fullInvoice }}"
                                  onclick="copyToClipboard('{{ $fullInvoice }}', this)">
                                {{ $shortInvoice }}
                                <iconify-icon icon="solar:copy-linear" class="ms-1 text-muted" style="font-size: 0.8rem;"></iconify-icon>
                            </span>
                        </td>
                        <td class="text-center text-muted small px-4 py-4">
                            {{ $sale->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-center px-4 py-4">
                            <span class="badge bg-light text-dark border font-normal rounded-pill px-2 py-1" style="font-weight: 500;">
                                {{ $sale->user->name ?? 'Admin Kasir' }}
                            </span>
                        </td>
                        <td class="text-center small text-muted fw-medium px-4 py-4">
                            <span class="badge bg-light text-secondary rounded-2">{{ $sale->sales->sum('quantity') }} item</span>
                        </td>
                        <td class="text-center px-4 py-4">
                            <div class="d-flex flex-column align-items-center">
                                <strong class="text-primary" style="font-size: 0.9rem;">
                                    Rp {{ number_format($sale->total_price, 0, ',', '.') }}
                                </strong>
                                @if($sale->discount_total > 0)
                                    <small class="text-danger" style="font-size: 0.725rem;">
                                        (Disc. -Rp {{ number_format($sale->discount_total, 0, ',', '.') }})
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td class="text-center px-4 py-4">
                            @if($sale->payment_method == 'cash')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-medium">Tunai</span>
                            @elseif($sale->payment_method == 'qris')
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-medium">QRIS</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 fw-medium">Transfer</span>
                            @endif
                        </td>
                        <td class="text-center px-4 py-4">
                            @if($sale->status == 'completed')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">Selesai</span>
                            @elseif($sale->status == 'pending')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1">Pending</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1">Batal</span>
                            @endif
                        </td>
                        <td class="text-center px-4 py-4">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-light btn-sm text-primary rounded-2 border p-1 px-2" data-bs-toggle="tooltip" title="Lihat Detail">
                                    <iconify-icon icon="solar:eye-bold" class="fs-6 mt-1 pointer-events-none"></iconify-icon>
                                </a>
                                <button type="button" onclick="printInvoice({{ $sale->id }})" class="btn btn-light btn-sm text-dark rounded-2 border p-1 px-2" data-bs-toggle="tooltip" title="Cetak Struk">
                                    <iconify-icon icon="solar:printer-bold" class="fs-6 mt-1 pointer-events-none"></iconify-icon>
                                </button>
                                <button type="button" class="btn btn-light btn-sm text-secondary rounded-2 border p-1 px-2" data-bs-toggle="tooltip" title="Aksi Lainnya">
                                    <iconify-icon icon="solar:menu-dots-circle-bold" class="fs-6 mt-1 pointer-events-none"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center text-muted">
                                <iconify-icon icon="solar:folder-with-files-line-duotone" width="48" height="48" class="opacity-50 mb-2"></iconify-icon>
                                <p class="mb-0">Tidak ada data transaksi yang ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($saleDocuments->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4 rounded-bottom-4">
            {{ $saleDocuments->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    /* Styling Updates */
    .btn-white {
        background-color: #fff;
        color: #495057;
    }
    .btn-white:hover {
        background-color: #f8f9fa;
        color: #212529;
    }

    .table-hover tbody tr:hover {
        background-color: #f8fafc;
        transition: background-color 0.2s ease;
    }

    .font-monospace {
        font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
        letter-spacing: -0.3px;
    }
    
    .pointer-events-none {
        pointer-events: none;
    }

    .invoice-copy-btn:hover {
        color: #0d6efd !important;
    }
</style>

<script>
    function printInvoice(saleId) {
        window.open(`/admin/sales/${saleId}`, '_blank');
    }

    function copyToClipboard(text, element) {
        navigator.clipboard.writeText(text).then(function() {
            var tooltip = bootstrap.Tooltip.getInstance(element);
            if (tooltip) {
                element.setAttribute('data-bs-original-title', 'Tersalin!');
                tooltip.show();
                setTimeout(() => {
                    element.setAttribute('data-bs-original-title', 'Klik untuk salin: ' + text);
                }, 1500);
            }
        });
    }
    
    // Inisialisasi Tooltip Bootstrap
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection