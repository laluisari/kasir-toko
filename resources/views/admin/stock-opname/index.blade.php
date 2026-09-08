@extends('layouts.main')

@section('title', 'Stock Opname')
@section('subTitle', 'List')

@section('content')

<div class="card h-100 p-0 radius-12">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-2" style="min-height: 70px;">
        <div class="d-flex align-items-center gap-2">
            <h6 class="text-lg fw-semibold text-primary-light mb-0">Stock Opname</h6>
            <span class="text-secondary-light fw-normal">List periode</span>
        </div>
        @php $hasActive = $opnames->contains('status', 'active'); @endphp
        @if (!$hasActive)
        <button type="button" class="add-btn btn btn-primary-600 radius-8 px-20 py-10 d-flex align-items-center gap-2" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1);" data-bs-toggle="modal" data-bs-target="#soStartModal">
            <iconify-icon icon="lucide:plus" class="icon"></iconify-icon>
            Mulai SO
        </button>
        @endif
    </div>

    <div class="card-body p-24">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-20" role="alert" style="border-radius: 0.75rem; border: 1px solid rgba(34, 197, 94, 0.3);">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="akar-icons:circle-check-fill" class="text-success" style="font-size: 1.25rem;"></iconify-icon>
                <span class="fw-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-warning alert-dismissible fade show mb-20" role="alert" style="border-radius: 0.75rem; border: 1px solid rgba(245, 158, 11, 0.3);">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="akar-icons:warning-fill" class="text-warning" style="font-size: 1.25rem;"></iconify-icon>
                <span class="fw-medium">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if ($hasActive)
        <div class="alert alert-primary radius-12 d-flex align-items-center gap-2 mb-20">
            <iconify-icon icon="solar:clock-circle-bold" class="text-primary" style="font-size: 1.25rem;"></iconify-icon>
            <span class="fw-medium">Periode Stock Opname aktif — Penjualan dikunci sampai selesai.</span>
        </div>
        @endif

        @if ($opnames->isEmpty())
            <div class="text-center text-secondary-light py-40">
                <iconify-icon icon="lucide:clipboard-list" class="d-block mx-auto mb-8" style="font-size: 2.5rem; opacity: 0.3;"></iconify-icon>
                <p class="mb-2">Belum ada periode Stock Opname.</p>
                <p class="text-sm mb-0">Klik tombol <span class="fw-semibold text-primary-light">Mulai SO</span> di pojok kanan atas untuk membuat periode baru.</p>
            </div>
        @else
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0" id="soTable">
                    <thead>
                        <tr>
                            <th class="text-sm text-secondary-light fw-semibold" style="width:48px">#</th>
                            <th class="text-sm text-secondary-light fw-semibold">Kode</th>
                            <th class="text-sm text-secondary-light fw-semibold">Keterangan</th>
                            <th class="text-sm text-secondary-light fw-semibold">Status</th>
                            <th class="text-sm text-secondary-light fw-semibold">Mulai</th>
                            <th class="text-sm text-secondary-light fw-semibold">Selesai</th>
                            <th class="text-sm text-secondary-light fw-semibold text-center">Pas / Selisih</th>
                            <th class="text-sm text-secondary-light fw-semibold text-center" style="width:280px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($opnames as $opname)
                            @php $summary = $opname->summary(); @endphp
                            <tr style="transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='rgba(0,0,0,0.02)'" onmouseout="this.style.backgroundColor='transparent'">
                                <td class="text-sm text-secondary-light">{{ $loop->iteration }}</td>
                                <td class="text-sm fw-semibold" style="font-family: monospace;">{{ $opname->code }}</td>
                                <td class="text-sm text-secondary-light">{{ $opname->note ?? '-' }}</td>
                                <td class="text-sm">
                                    @if ($opname->status === 'active')
                                        <span style="background-color: #eff6ff; color: #2563eb; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-weight: 500;">Aktif</span>
                                    @else
                                        <span style="background-color: #f0fdf4; color: #059669; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-weight: 500;">Selesai</span>
                                    @endif
                                </td>
                                <td class="text-sm text-secondary-light">{{ $opname->started_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="text-sm text-secondary-light">{{ $opname->ended_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="text-sm text-center text-secondary-light">{{ $summary['pas'] }} / {{ $summary['selisih'] }}</td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-8">
                                        @if ($opname->status === 'active')
                                        <a href="{{ route('stock-opname.counting', $opname) }}" class="btn btn-sm btn-primary-600 radius-8 px-16 py-8 d-flex align-items-center gap-2" style="box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                            <iconify-icon icon="lucide:clipboard-list" style="font-size: 1rem;"></iconify-icon> Catat
                                        </a>
                                        <form action="{{ route('stock-opname.finish', $opname) }}" method="POST" class="d-inline" onsubmit="return confirm('Selesaikan periode {{ $opname->code }}? Penjualan kembali dibuka.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success radius-8 px-16 py-8 d-flex align-items-center gap-2" style="box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                                <iconify-icon icon="lucide:check" style="font-size: 1rem;"></iconify-icon> Selesai
                                            </button>
                                        </form>
                                        <form action="{{ route('stock-opname.cancel', $opname) }}" method="POST" class="d-inline" onsubmit="return confirm('Batalkan periode ini? Semua catatan item akan dihapus.');">
                                            @csrf
                                            <button type="submit" class="d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; border-radius: 0.5rem; background-color: #fef2f2; border: 1.5px solid #fecaca; color: #dc2626; cursor: pointer; padding: 0; margin: 0;"
                                                    onmouseover="this.style.backgroundColor='#dc2626'; this.style.color='white';"
                                                    onmouseout="this.style.backgroundColor='#fef2f2'; this.style.color='#dc2626';">
                                                <iconify-icon icon="lucide:trash-2" style="font-size: 1rem;"></iconify-icon>
                                            </button>
                                        </form>
                                        @else
                                        <a href="{{ route('stock-opname.show', $opname) }}" class="btn btn-sm btn-outline-primary radius-8 px-16 py-8 d-flex align-items-center gap-2" style="border-width: 1.5px;">
                                            <iconify-icon icon="lucide:file-text" style="font-size: 1rem;"></iconify-icon> Buka
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Modal Mulai SO -->
<div class="modal fade" id="soStartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content" style="border-radius: 1rem; box-shadow: 0 20px 25px rgba(0,0,0,0.12); border: 1px solid #e2e8f0;">
            <div class="modal-header border-bottom" style="border-color:#e2e8f0 !important;">
                <h6 class="modal-title fw-semibold text-primary-light">Mulai Stock Opname</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stock-opname.store') }}" method="POST">
                @csrf
                <div class="modal-body py-24">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-sm text-primary-light mb-8">Keterangan <span class="text-secondary-light">(opsional)</span></label>
                        <input type="text" name="note" class="form-control" placeholder="mis. Stock Opname Awal Tahun 2026"
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                    </div>
                    <p class="text-sm text-secondary-light mb-0" style="line-height:1.6;">
                        Ketika SO aktif, menu <b>Penjualan</b> otomatis terkunci sampai periode ini selesai.
                    </p>
                </div>
                <div class="modal-footer border-top d-flex gap-2" style="border-color:#e2e8f0 !important;">
                    <button type="button" class="btn btn-light radius-8 px-20 py-10 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-10 fw-semibold">Mulai Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const $soTable = $('#soTable');
    if ($soTable.length) {
        $soTable.DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Semua']],
            order: [[0, 'desc']]
        });
    }
</script>
@endsection