@extends('layouts.main')

@section('title', 'Stock Opname - Laporan')
@section('subTitle', $stockOpname->code)

@section('content')

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #soReport, #soReport * {
            visibility: visible;
        }
        #soReport {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>

<div class="d-print-none mb-16 d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
        <h6 class="fw-semibold text-primary-light mb-0" style="font-family: monospace;">{{ $stockOpname->code }}</h6>
        <span class="text-secondary-light text-sm">{{ $stockOpname->started_at?->format('d M Y H:i') }} s.d. {{ $stockOpname->ended_at?->format('d M Y H:i') }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stock-opname.index') }}" class="btn btn-sm btn-light radius-8 px-16 py-8 fw-semibold">
            <iconify-icon icon="lucide:arrow-left" style="font-size:1rem;"></iconify-icon> Kembali
        </a>
        <button type="button" class="btn btn-sm btn-primary-600 radius-8 px-16 py-8 fw-semibold" style="box-shadow: 0 2px 6px rgba(0,0,0,0.1);" onclick="window.print()">
            <iconify-icon icon="lucide:printer" style="font-size:1rem;"></iconify-icon> Cetak
        </button>
    </div>
</div>

@if (session('success'))
<div class="d-print-none alert alert-success alert-dismissible fade show mb-20" role="alert" style="border-radius: 0.75rem; border: 1px solid rgba(34, 197, 94, 0.3);">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <div class="d-flex align-items-center gap-2">
        <iconify-icon icon="akar-icons:circle-check-fill" class="text-success" style="font-size: 1.25rem;"></iconify-icon>
        <span class="fw-medium">{{ session('success') }}</span>
    </div>
</div>
@endif

<div id="soReport">
    <div class="card p-0 radius-12 mb-16">
        <div class="card-body p-24">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-8">
                <div>
                    <h6 class="text-lg fw-semibold text-primary-light mb-0">{{ $stockOpname->code }}</h6>
                    <div class="text-secondary-light text-sm mt-4">{{ $stockOpname->note ?? 'Tanpa keterangan' }}</div>
                </div>
                <span style="background-color: #f0fdf4; color: #059669; padding: 0.3rem 1rem; border-radius: 0.5rem; font-weight: 600; height: fit-content;">
                    Selesai
                </span>
            </div>
            <div class="text-sm text-secondary-light">
                Dicatat oleh <span class="fw-semibold text-primary-light">{{ $stockOpname->creator?->name ?? '-' }}</span> mulai {{ $stockOpname->started_at?->format('d M Y H:i') }} • selesai {{ $stockOpname->ended_at?->format('d M Y H:i') }}
            </div>
        </div>
    </div>

    <div class="row g-16 mb-16">
        <div class="col-xl-3 col-md-6">
            <div class="card p-0 radius-12 shadow-none" style="border:1px solid #e2e8f0;">
                <div class="card-body d-flex align-items-center gap-3 py-20 px-24">
                    <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:0.75rem;background-color:#eff6ff;color:#2563eb;">
                        <iconify-icon icon="lucide:clipboard-list" style="font-size:1.4rem;"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm text-secondary-light">Produk Diperiksa</div>
                        <div class="text-xl fw-bold text-primary-light">{{ $summary['dihitung'] }} / <span class="text-secondary-light" style="font-size:0.85rem;">{{ $stockOpname->items->count() }} item</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-0 radius-12 shadow-none" style="border:1px solid #e2e8f0;">
                <div class="card-body d-flex align-items-center gap-3 py-20 px-24">
                    <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:0.75rem;background-color:#f0fdf4;color:#059669;">
                        <iconify-icon icon="lucide:check-circle" style="font-size:1.4rem;"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm text-secondary-light">Stok Pas</div>
                        <div class="text-xl fw-bold" style="color:#059669;">{{ $summary['pas'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-0 radius-12 shadow-none" style="border:1px solid #e2e8f0;">
                <div class="card-body d-flex align-items-center gap-3 py-20 px-24">
                    <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:0.75rem;background-color:#fef2f2;color:#dc2626;">
                        <iconify-icon icon="lucide:alert-triangle" style="font-size:1.4rem;"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm text-secondary-light">Produk Selisih</div>
                        <div class="text-xl fw-bold" style="color:#dc2626;">{{ $summary['selisih'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card p-0 radius-12 shadow-none" style="border:1px solid #e2e8f0;">
                <div class="card-body d-flex align-items-center gap-3 py-20 px-24">
                    <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:0.75rem;background-color:#fffbeb;color:#b45309;">
                        <iconify-icon icon="lucide:scale" style="font-size:1.4rem;"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-sm text-secondary-light">Total {{ $summary['total_selisih'] >= 0 ? '+' : '' }}{{ $summary['total_selisih'] }} unit</div>
                        <div class="text-xl fw-bold" style="color:#b45309;">{{ formatRupiah($summary['total_rp_selisih']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card h-100 p-0 radius-12">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between" style="min-height: 60px;">
            <h6 class="text-md fw-semibold text-primary-light mb-0">Rincian Pencatatan</h6>
            <span class="text-sm text-secondary-light">{{ $stockOpname->items->count() }} produk dicatat</span>
        </div>
        <div class="card-body p-24">
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-sm text-secondary-light fw-semibold" style="width:48px">#</th>
                            <th class="text-sm text-secondary-light fw-semibold">Produk</th>
                            <th class="text-sm text-secondary-light fw-semibold text-center">Stok Sistem</th>
                            <th class="text-sm text-secondary-light fw-semibold text-center">Stok Fisik</th>
                            <th class="text-sm text-secondary-light fw-semibold text-center">Selisih</th>
                            <th class="text-sm text-secondary-light fw-semibold text-center">Penetapan</th>
                            <th class="text-sm text-secondary-light fw-semibold text-center">Terjual</th>
                            <th class="text-sm text-secondary-light fw-semibold">Catatan</th>
                            <th class="text-sm text-secondary-light fw-semibold text-center">Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockOpname->items as $item)
                            <tr>
                                <td class="text-sm text-secondary-light">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold text-sm text-primary-light">{{ $item->product?->name }}</div>
                                    <div class="text-secondary-light" style="font-family:monospace; font-size:0.75rem;">{{ $item->product?->barcode ?? '-' }}</div>
                                </td>
                                <td class="text-sm text-center">{{ (int) $item->stok_sistem }}</td>
                                <td class="text-sm text-center fw-semibold">{{ (int) $item->stok_fisik }}</td>
                                <td class="text-sm text-center fw-semibold">
                                    @if ($item->selisih < 0)
                                        <span style="color:#dc2626;">{{ $item->selisih }}</span>
                                    @elseif ($item->selisih > 0)
                                        <span style="color:#059669;">+{{ $item->selisih }}</span>
                                    @else
                                        <span class="text-secondary-light">0</span>
                                    @endif
                                </td>
                                <td class="text-sm text-center">{{ $item->stok_penetapan !== null ? (int) $item->stok_penetapan : '-' }}</td>
                                <td class="text-sm text-center text-secondary-light">{{ (int) $item->terjual }}</td>
                                <td class="text-sm text-secondary-light">{{ $item->catatan ?? '-' }}</td>
                                <td class="text-sm text-center">
                                    @if ($item->status === 'pas')
                                        <span style="background-color:#f0fdf4; color:#059669; padding:0.2rem 0.6rem; border-radius:0.25rem; font-weight:500;">Pas</span>
                                    @else
                                        <span style="background-color:#fef2f2; color:#dc2626; padding:0.2rem 0.6rem; border-radius:0.25rem; font-weight:500;">Selisih</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary-light py-40">
                                    <iconify-icon icon="lucide:inbox" class="d-block mx-auto mb-8" style="font-size:2.5rem; opacity:0.3;"></iconify-icon>
                                    <p class="mb-0">Tidak ada item yang dicatat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection