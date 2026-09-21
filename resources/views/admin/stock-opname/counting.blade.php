@extends('layouts.main')

@section('title', 'Stock Opname - Catat')
@section('subTitle', $stockOpname->code)

@section('content')

<style>
/* ── Stock Opname: Compact Inventory List ──────────────── */
.so-search-box .form-control {
    height: 46px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: .875rem;
    transition: border-color .2s, box-shadow .2s;
}
.so-search-box .form-control:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    outline: none;
}
.so-card {
    border: 1.5px solid #e5e7eb !important;
    border-radius: 12px !important;
    box-shadow: 0 1px 2px rgba(0,0,0,.04) !important;
    transition: border-color .15s;
}
.so-card:hover { border-color: #d1d5db !important; }
.so-card .card-body { padding: 12px; }

/* Thumbnail */
.so-thumb { width: 96px; height: 96px; flex-shrink: 0; border-radius: 8px; overflow: hidden; background: #f8fafc; }
.so-slides { display: flex; height: 100%; overflow: hidden; transition: transform .35s ease; }
.so-slide { flex-shrink: 0; width: 96px; height: 96px; object-fit: cover; cursor: zoom-in; display: block; }
.so-slide-empty { flex-shrink: 0; width: 96px; height: 96px; display: flex; align-items: center; justify-content: center; }
.so-img-placeholder { font-size: 1.75rem; color: #cbd5e1; }
.so-nav-prev, .so-nav-next {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 20px; height: 20px; padding: 0; border: none;
    background: rgba(255,255,255,.88); border-radius: 50%;
    z-index: 2; box-shadow: 0 1px 3px rgba(0,0,0,.2);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .75rem;
}
.so-nav-prev { left: 3px; }
.so-nav-next { right: 3px; }
.so-count {
    position: absolute; bottom: 3px; left: 50%; transform: translateX(-50%);
    font-size: .58rem; padding: 1px 5px;
    background: rgba(0,0,0,.55); color: #fff; border-radius: 999px; white-space: nowrap;
}
.so-zoom {
    position: absolute; bottom: 4px; right: 4px;
    width: 22px; height: 22px; padding: 0; border: none;
    background: rgba(255,255,255,.88); border-radius: 4px;
    z-index: 2; box-shadow: 0 1px 3px rgba(0,0,0,.15);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .68rem; color: #475569;
}

/* Product info */
.so-product-info { min-width: 0; }
.so-product-heading { min-width: 0; }
.so-product-name {
    font-size: .9375rem; font-weight: 700; color: #111827;
    flex: 1 1 auto; min-width: 0;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.so-product-meta { font-size: .75rem; color: #6b7280; line-height: 1.6; }
.so-barcode { font-family: monospace; }
.so-stock-info strong { color: #2563eb; font-weight: 600; }

/* Badges */
.so-badge-pending, .so-badge-pas, .so-badge-selisih {
    font-size: .67rem; font-weight: 600; padding: 2px 8px;
    border-radius: 999px; display: inline-block; white-space: nowrap; line-height: 1.6;
}
.so-badge-pending { background: #f3f4f6; color: #6b7280; }
.so-badge-pas     { background: #dcfce7; color: #15803d; }
.so-badge-selisih { background: #fee2e2; color: #b91c1c; }

/* Segmented control */
.so-status-toggle .btn {
    height: 34px; font-size: .8rem; font-weight: 500;
    padding: 0 12px; display: flex; align-items: center; gap: 4px; border-width: 1.5px;
}
.so-status-toggle .btn-check:checked + .btn-outline-success { background: #16a34a; color: #fff; border-color: #16a34a; }
.so-status-toggle .btn-check:checked + .btn-outline-danger  { background: #dc2626; color: #fff; border-color: #dc2626; }

/* Fields */
.so-field-label { display: block; font-size: .7rem; color: #6b7280; margin-bottom: 2px; }
.so-fields .form-control { font-size: .8rem; }

/* Save button */
.so-save {
    height: 34px; font-size: .8rem; font-weight: 600;
    padding: 0 16px; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;
}

@media (max-width: 576px) {
    .so-thumb, .so-slide, .so-slide-empty { width: 76px; height: 76px; }
    .so-product-name { font-size: .875rem; }
    .so-save { width: 100%; justify-content: center; }
}
</style>

<div class="card h-100 p-0 radius-12 mb-24">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-2" style="min-height: 70px;">
        <div>
            <h6 class="text-lg fw-semibold text-primary-light mb-0" style="font-family: monospace;">{{ $stockOpname->code }}</h6>
            <span class="text-secondary-light text-sm">{{ $stockOpname->started_at?->format('d M Y H:i') }} • {{ $stockOpname->note ?? 'Tanpa keterangan' }}</span>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('stock-opname.cancel', $stockOpname) }}" method="POST" onsubmit="handleFormSubmit(event, 'Batalkan periode ini? Semua catatan item akan dihapus.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger radius-8 px-16 py-8">
                    {{-- <iconify-icon icon="lucide:x" style="font-size: 1rem;"></iconify-icon>  --}}
                    Batalkan
                </button>
            </form>
            <form action="{{ route('stock-opname.finish', $stockOpname) }}" method="POST" onsubmit="handleFormSubmit(event, 'Selesaikan periode ini? Stok akan disesuaikan ke hasil hitung, lalu penjualan dibuka kembali.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-success radius-8 px-16 py-8 fw-semibold" style="box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                    {{-- <iconify-icon icon="lucide:check" style="font-size: 1rem;"></iconify-icon>  --}}
                    Selesai SO
                </button>
            </form>
        </div>
    </div>

    <div class="card-body p-24">
        @php $totalProducts = $products->count(); @endphp
        <div class="mb-24">
            <div class="d-flex justify-content-between align-items-center mb-8">
                <span class="text-sm fw-semibold text-primary-light">Progres Pencatatan</span>
                <span class="text-sm text-secondary-light" id="progressText">{{ $total }} / {{ $totalProducts }} dihitung</span>
            </div>
            <div class="progress" style="height: 10px; border-radius: 0.5rem; background-color: #eef2f7;">
                <div class="progress-bar" id="progressBar" role="progressbar"
                     style="width: {{ $totalProducts ? round($total / $totalProducts * 100) : 0 }}%; background-color: #2563eb; border-radius: 0.5rem;"></div>
            </div>
        </div>

        @if ($totalProducts === 0)
            <div class="text-center text-secondary-light py-40">
                <iconify-icon icon="lucide:inbox" class="d-block mx-auto mb-8" style="font-size: 2.5rem; opacity: 0.3;"></iconify-icon>
                <p class="mb-0">Belum ada produk untuk dihitung. Tambahkan produk dulu.</p>
            </div>
        @else
            <!-- Search -->
            <div class="so-search-box mb-4">
                <div class="position-relative">
                    <iconify-icon icon="lucide:search" class="position-absolute" style="top: 50%; left: 14px; transform: translateY(-50%); color: #94a3b8; font-size: 1rem; z-index: 1; pointer-events: none;"></iconify-icon>
                    <input type="text" id="soSearchInput"
                           class="form-control ps-5 pe-5"
                           placeholder="Cari nama produk, barcode, atau kategori..."
                           autocomplete="off">
                    <button type="button" id="soSearchClear" class="btn btn-sm btn-light border position-absolute d-none"
                            style="top: 50%; right: 10px; transform: translateY(-50%); border-radius: 6px; z-index: 1; height: 28px; padding: 0 8px;">
                        <iconify-icon icon="lucide:x" style="font-size: .82rem;"></iconify-icon>
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <span class="text-secondary-light" id="soResultCount" style="font-size: .75rem;"></span>
                    <span class="text-secondary-light" style="font-size: .75rem;">{{ $totalProducts }} produk tersedia</span>
                </div>
            </div>

            <!-- Tampilan awal: kosong -->
            <div id="soInitialState" class="text-center py-5">
                <iconify-icon icon="lucide:package-search" class="d-block mx-auto mb-3" style="font-size: 3.5rem; opacity: 0.15; color: #64748b;"></iconify-icon>
                <p class="fw-medium mb-1" style="color: #64748b; font-size: 0.9rem;">Ketik nama produk untuk mulai menghitung</p>
                <p style="color: #94a3b8; font-size: 0.78rem; margin: 0;">Cari berdasarkan nama, barcode, atau kategori</p>
            </div>

            <!-- Hasil pencarian -->
            <div id="soResults" class="row g-3 d-none">
                @foreach ($products as $product)
                    @php
                        $existing = $items->get($product->id);
                        $imgs = $product->images();
                        $searchKey = strtolower(trim($product->name . ' ' . ($product->barcode ?? '') . ' ' . ($product->category?->name ?? '')));
                    @endphp
                    <div class="col-12 so-product-col" data-search="{{ $searchKey }}">
                        <div class="card so-card"
                             data-pid="{{ $product->id }}"
                             data-sistem="{{ (int) $product->stock }}"
                             data-saved="{{ $existing ? 1 : 0 }}"
                             data-idx="0">
                            <div class="card-body">
                                <div class="d-flex align-items-start gap-3">

                                    <!-- Gambar produk -->
                                    <div class="so-thumb position-relative">
                                        <div class="so-slides">
                                            @forelse ($imgs as $img)
                                                <img src="{{ asset('storage/' . $img) }}" class="so-slide" alt="{{ $product->name }}">
                                            @empty
                                                <div class="so-slide so-slide-empty">
                                                    <iconify-icon icon="lucide:image" class="so-img-placeholder"></iconify-icon>
                                                </div>
                                            @endforelse
                                        </div>
                                        @if (count($imgs) > 1)
                                            <button type="button" class="so-nav-prev btn">
                                                <iconify-icon icon="lucide:chevron-left"></iconify-icon>
                                            </button>
                                            <button type="button" class="so-nav-next btn">
                                                <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                                            </button>
                                            <span class="so-count">1/{{ count($imgs) }}</span>
                                        @endif
                                        @if (count($imgs) > 0)
                                            <button type="button" class="so-zoom btn">
                                                <iconify-icon icon="lucide:maximize-2"></iconify-icon>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Info + Kontrol -->
                                    <div class="so-product-info flex-grow-1">

                                        <!-- Nama + Status badge -->
                                        <div class="so-product-heading d-flex align-items-center gap-2 mb-1">
                                            <span class="so-product-name">{{ $product->name }}</span>
                                            <span class="so-status-badge flex-shrink-0">
                                                @if ($existing && $existing->status === 'pas')
                                                    <span class="so-badge-pas">✓ Pas</span>
                                                @elseif ($existing && $existing->status === 'selisih')
                                                    <span class="so-badge-selisih">Selisih {{ $existing->selisih >= 0 ? '+' : '' }}{{ $existing->selisih }}</span>
                                                @else
                                                    <span class="so-badge-pending">Belum</span>
                                                @endif
                                            </span>
                                        </div>

                                        <!-- Meta -->
                                        <div class="so-product-meta mb-2">
                                            @if($product->category)<span>{{ $product->category->name }}</span>@endif
                                            @if($product->barcode)<span class="so-barcode"> · {{ $product->barcode }}</span>@endif
                                            <span class="so-stock-info"> · Stok: <strong>{{ (int) $product->stock }} {{ $product->unit ?? 'pcs' }}</strong></span>
                                            @if($existing)<span> · Terjual: {{ (int) $existing->terjual }}</span>@endif
                                        </div>

                                        <!-- Kontrol pencatatan -->
                                        <div class="so-control d-flex flex-wrap align-items-start gap-2">

                                            <!-- Toggle Pas / Selisih -->
                                            <div class="so-status-toggle btn-group flex-shrink-0" role="group" aria-label="Status pencatatan">
                                                <input type="radio" class="btn-check so-status" name="so-status-{{ $product->id }}" id="so-pas-{{ $product->id }}" value="pas" autocomplete="off" {{ !$existing || $existing->status === 'pas' ? 'checked' : '' }}>
                                                <label for="so-pas-{{ $product->id }}" class="btn btn-sm btn-outline-success">
                                                    <iconify-icon icon="lucide:check"></iconify-icon> Pas
                                                </label>
                                                <input type="radio" class="btn-check so-status" name="so-status-{{ $product->id }}" id="so-sel-{{ $product->id }}" value="selisih" autocomplete="off" {{ $existing && $existing->status === 'selisih' ? 'checked' : '' }}>
                                                <label for="so-sel-{{ $product->id }}" class="btn btn-sm btn-outline-danger">
                                                    <iconify-icon icon="lucide:alert-circle"></iconify-icon> Selisih
                                                </label>
                                            </div>

                                            <!-- Field selisih -->
                                            <div class="so-fields flex-grow-1 {{ $existing && $existing->status === 'selisih' ? '' : 'd-none' }}">
                                                <div class="d-flex gap-2 align-items-end flex-wrap">
                                                    <div>
                                                        <label class="so-field-label">Stok fisik <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control form-control-sm so-fisik" min="0"
                                                               placeholder="Fisik"
                                                               value="{{ $existing?->stok_fisik !== null ? $existing->stok_fisik : '' }}">
                                                    </div>
                                                    <div style="width: 78px;">
                                                        <label class="so-field-label">Penetapan</label>
                                                        <input type="number" class="form-control form-control-sm so-penetapan" min="0"
                                                               placeholder="Ops."
                                                               value="{{ $existing?->stok_penetapan ?? '' }}">
                                                    </div>
                                                    <div class="so-selisih-preview so-field-label pb-1">Selisih: —</div>
                                                </div>
                                                <input type="text" class="form-control form-control-sm so-catatan mt-1" maxlength="255"
                                                       placeholder="Catatan (opsional)"
                                                       value="{{ $existing?->catatan ?? '' }}">
                                            </div>

                                            <!-- Simpan -->
                                            <button type="button" class="btn btn-sm btn-primary so-save ms-auto">
                                                <iconify-icon icon="lucide:save"></iconify-icon> Simpan
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="soEmpty" class="d-none text-center py-5">
                <iconify-icon icon="lucide:search-x" class="d-block mx-auto mb-3" style="font-size: 2.5rem; opacity: 0.2;"></iconify-icon>
                <p class="text-secondary-light mb-0" style="font-size: 0.85rem;">Tidak ada produk yang cocok.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal preview gambar -->
<div class="modal fade" id="soPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 640px;">
        <div class="modal-content bg-dark" style="border: 1px solid #334155; border-radius: .75rem;">
            <div class="modal-header border-0 py-2 px-3">
                <h6 class="modal-title text-white mb-0" style="font-size: .95rem;">Preview Gambar Produk</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3"></div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const CSRF = "{{ csrf_token() }}";
    const soId = {{ $stockOpname->id }};
    const itemUrl = (pid) => "{{ route('stock-opname.item', [$stockOpname, ':pid']) }}".replace(':pid', pid);

    function updateProgress() {
        const total = $('.so-card').length;
        const saved = $('.so-card[data-saved="1"]').length;
        const pct = total ? Math.round(saved / total * 100) : 0;
        $('#progressBar').css('width', pct + '%');
        $('#progressText').text(saved + ' / ' + total + ' dihitung');
    }

    function previewSelisih($card) {
        const sistem = parseInt($card.data('sistem'), 10);
        const fisik = parseInt($card.find('.so-fisik').val(), 10);
        const $pv = $card.find('.so-selisih-preview');
        if (!Number.isFinite(fisik)) {
            $pv.html('Selisih: <span class="text-secondary">—</span>');
            return;
        }
        const s = fisik - sistem;
        $pv.html('Selisih: <span class="fw-semibold" style="color:' + (s >= 0 ? '#059669' : '#dc2626') + ';">' + (s >= 0 ? '+' : '') + s + '</span>');
    }

    function moveSlides($card, dir) {
        const $slides = $card.find('.so-slides');
        const n = $slides.find('.so-slide').length;
        if (n < 2) return;
        let idx = parseInt($card.data('idx') || 0, 10);
        idx = (idx + dir + n) % n;
        $card.data('idx', idx);
        $slides.css('transform', 'translateX(' + (-idx * 100) + '%)');
        $card.find('.so-count').text((idx + 1) + '/' + n);
    }

    function openPreview(imgs, activeIdx) {
        const $modal = $('#soPreviewModal');
        const $body = $modal.find('.modal-body');
        $body.empty();

        const $wrap = $('<div class="position-relative"></div>');
        const $slides = $('<div class="d-flex" style="overflow: hidden; transition: transform .3s ease;"></div>');
        imgs.forEach(src => {
            $slides.append('<img class="flex-shrink-0" style="width: 100%; max-height: 72vh; object-fit: contain;" src="' + src + '">');
        });
        $slides.css({ width: '100%' });
        $wrap.append($slides);

        const n = imgs.length;
        if (n > 1) {
            $wrap.append(
                '<button type="button" class="btn btn-light position-absolute top-50 start-0 translate-middle-y ms-2" id="pv-prev" style="width:32px;height:32px;padding:0;line-height:1;">\u2039</button>' +
                '<button type="button" class="btn btn-light position-absolute top-50 end-0 translate-middle-y me-2" id="pv-next" style="width:32px;height:32px;padding:0;line-height:1;">\u203A</button>' +
                '<span class="position-absolute bottom-0 start-50 translate-middle-x badge text-bg-dark mb-2" id="pv-count" style="font-size:.75rem;"></span>'
            );
        }

        let idx = activeIdx || 0;
        const render = () => {
            $slides.css('transform', 'translateX(' + (-idx * 100) + '%)');
            $('#pv-count').text((idx + 1) + '/' + n);
        };

        $wrap.on('click', '#pv-prev', () => { idx = (idx - 1 + n) % n; render(); });
        $wrap.on('click', '#pv-next', () => { idx = (idx + 1) % n; render(); });

        $body.html($wrap);
        render();
        $modal.modal('show');
    }

    $(function () {
        if (!$('.so-card').length) return;

        updateProgress();

        // Inisialisasi: baris selisih yang sudah tersimpan → tampilkan field + pratinjau
        $('.so-card').each(function () {
            const $card = $(this);
            if ($card.find('.so-status:checked').val() === 'selisih') {
                $card.find('.so-fields').removeClass('d-none');
            }
            previewSelisih($card);
        });

        // Pencarian: tampil kosong dulu, muncul saat ketik
        $('#soSearchInput').on('input', function () {
            const q = $.trim(this.value).toLowerCase();

            if (q === '') {
                $('#soInitialState').removeClass('d-none');
                $('#soResults').addClass('d-none');
                $('#soEmpty').addClass('d-none');
                $('#soResultCount').text('');
                $('#soSearchClear').addClass('d-none');
                return;
            }

            $('#soInitialState').addClass('d-none');
            $('#soResults').removeClass('d-none');

            let shown = 0;
            const total = $('.so-product-col').length;

            $('.so-product-col').each(function () {
                const hit = $(this).data('search').indexOf(q) >= 0;
                $(this).toggleClass('d-none', !hit);
                if (hit) shown++;
            });

            $('#soSearchClear').removeClass('d-none');
            $('#soResultCount').text('Menampilkan ' + shown + ' dari ' + total + ' produk');
            $('#soEmpty').toggleClass('d-none', shown !== 0);
        });

        $('#soSearchClear').on('click', function () {
            $('#soSearchInput').val('').trigger('input').trigger('focus');
        });

        // Toggle kontrol selisih
        $('.so-card').on('change', '.so-status', function () {
            const $card = $(this).closest('.so-card');
            const selisih = this.value === 'selisih';
            $card.find('.so-fields').toggleClass('d-none', !selisih);
            $card.addClass('is-dirty');
            previewSelisih($card);
            if (selisih) {
                $card.find('.so-fisik').trigger('focus');
            }
        });

        // Pratinjau selisih live
        $('.so-card').on('input', '.so-fisik', function () {
            previewSelisih($(this).closest('.so-card'));
        });
        $('.so-card').on('input change', '.so-penetapan, .so-catatan', function () {
            $(this).closest('.so-card').addClass('is-dirty');
        });

        // Simpan per produk
        $('.so-card').on('click', '.so-save', function () {
            const $card = $(this).closest('.so-card');
            const pid = $card.data('pid');
            const status = $card.find('.so-status:checked').val();

            if (!status) {
                alert('Pilih dulu status Pas atau Selisih.');
                return;
            }

            const payload = { status: status };
            const fisik = $card.find('.so-fisik').val();
            if (status === 'selisih') {
                if (fisik === '' || fisik === null) {
                    alert('Stok fisik wajib diisi saat status Selisih.');
                    return;
                }
                payload.stok_fisik = fisik;
            }
            const penetapan = $card.find('.so-penetapan').val();
            if (penetapan !== '') payload.stok_penetapan = penetapan;
            const catatan = $card.find('.so-catatan').val();
            if (catatan) payload.catatan = catatan;

            const $btn = $(this);
            const original = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" style="width: 0.9rem; height: 0.9rem;"></span>');

            fetch(itemUrl(pid), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json().then(d => ({ ok: r.ok, d })))
            .then(({ ok, d }) => {
                if (!ok || !d.success) throw new Error(d.message || 'Gagal menyimpan.');
                $card.data('saved', 1).removeClass('is-dirty');

                const $badge = $card.find('.so-status-badge');
                if (d.item.status === 'pas') {
                    $badge.html('<span class="so-badge-pas">✓ Pas</span>');
                } else {
                    const s = (d.item.selisih >= 0 ? '+' : '') + d.item.selisih;
                    $badge.html('<span class="so-badge-selisih">Selisih ' + s + '</span>');
                }

                if (d.item.stok_penetapan !== null) {
                    $card.find('.so-penetapan').val(d.item.stok_penetapan);
                }
                updateProgress();
            })
            .catch(err => alert(err.message || 'Terjadi kesalahan saat menyimpan.'))
            .finally(() => {
                $btn.prop('disabled', false).html(original);
            });
        });

        // Navigasi carousel
        $(document).on('click', '.so-nav-prev', function (e) {
            e.preventDefault();
            moveSlides($(this).closest('.so-card'), -1);
        });
        $(document).on('click', '.so-nav-next', function (e) {
            e.preventDefault();
            moveSlides($(this).closest('.so-card'), 1);
        });

        // Preview via tombol zoom
        $('.so-card').on('click', '.so-zoom', function (e) {
            e.stopPropagation();
            const $card = $(this).closest('.so-card');
            const imgs = $card.find('.so-slide img').map(function () { return this.src; }).get();
            if (!imgs.length) return;
            openPreview(imgs, parseInt($card.data('idx') || 0, 10));
        });

        // Geser (swipe) gambar
        $('.so-slides').on('touchstart', function (e) {
            $(this).data('tx', e.originalEvent.touches[0].clientX);
        });
        $('.so-slides').on('touchend', function (e) {
            const start = $(this).data('tx');
            if (start == null) return;
            const end = e.originalEvent.changedTouches[0].clientX;
            const diff = start - end;
            if (Math.abs(diff) > 40) {
                moveSlides($(this).closest('.so-card'), diff > 0 ? 1 : -1);
            }
            $(this).removeData('tx');
        });

        // Preview gambar
        $('.so-card').on('click', '.so-thumb .so-slide', function () {
            const $card = $(this).closest('.so-card');
            const imgs = $card.find('.so-slide img').map(function () { return this.src; }).get();
            if (!imgs.length) return;
            openPreview(imgs, parseInt($card.data('idx') || 0, 10));
        });

        // Peringatan bila ada data belum disimpan
        $(window).on('beforeunload', function (e) {
            if ($('.so-card.is-dirty').length) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });
</script>
@endsection