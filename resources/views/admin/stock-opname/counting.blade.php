@extends('layouts.main')

@section('title', 'Stock Opname - Catat')
@section('subTitle', $stockOpname->code)

@section('content')

<div class="card h-100 p-0 radius-12">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-2" style="min-height: 70px;">
        <div>
            <h6 class="text-lg fw-semibold text-primary-light mb-0" style="font-family: monospace;">{{ $stockOpname->code }}</h6>
            <span class="text-secondary-light text-sm">{{ $stockOpname->started_at?->format('d M Y H:i') }} • {{ $stockOpname->note ?? 'Tanpa keterangan' }}</span>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('stock-opname.cancel', $stockOpname) }}" method="POST" onsubmit="return confirm('Batalkan periode ini? Semua catatan item akan dihapus.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger radius-8 px-16 py-8">
                    <iconify-icon icon="lucide:x" style="font-size: 1rem;"></iconify-icon> Batalkan
                </button>
            </form>
            <form action="{{ route('stock-opname.finish', $stockOpname) }}" method="POST" onsubmit="return confirm('Selesaikan periode ini? Penjualan akan dibuka kembali.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-success radius-8 px-16 py-8 fw-semibold" style="box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                    <iconify-icon icon="lucide:check" style="font-size: 1rem;"></iconify-icon> Selesai SO
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
            <div class="table-responsive scroll-sm">
                <table class="table bordered-table sm-table mb-0" id="soCountingTable">
                <thead>
                    <tr>
                        <th class="text-sm text-secondary-light fw-semibold" style="width:48px">#</th>
                        <th class="text-sm text-secondary-light fw-semibold">Produk</th>
                        <th class="text-sm text-secondary-light fw-semibold">Kategori</th>
                        <th class="text-sm text-secondary-light fw-semibold text-center">Stok (Sistem)</th>
                        <th class="text-sm text-secondary-light fw-semibold text-center">Terjual</th>
                        <th class="text-sm text-secondary-light fw-semibold text-center" style="width:110px">Hasil</th>
                        <th class="text-sm text-secondary-light fw-semibold text-center" style="min-width:380px">Catat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        @php $existing = $items->get($product->id); @endphp
                        <tr data-product-id="{{ $product->id }}" class="{{ $existing ? 'row-saved' : '' }}" style="transition: background-color 0.2s ease;">
                            <td class="text-sm text-secondary-light">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold text-sm text-primary-light">{{ $product->name }}</div>
                                <div class="text-secondary-light" style="font-family: monospace; font-size: 0.75rem;">{{ $product->barcode ?? '-' }}</div>
                            </td>
                            <td class="text-sm text-secondary-light">{{ $product->category?->name ?? '-' }}</td>
                            <td class="text-sm text-center fw-semibold">{{ (int) $product->stock }}</td>
                            <td class="text-sm text-center text-secondary-light">{{ $existing ? (int) $existing->terjual : '-' }}</td>
                            <td class="text-center">
                                <span class="status-chip text-sm fw-semibold">
                                    @if ($existing && $existing->status === 'pas')
                                        <span style="background-color:#f0fdf4; color:#059669; padding:0.2rem 0.6rem; border-radius:0.25rem;">Pas</span>
                                    @elseif ($existing && $existing->status === 'selisih')
                                        <span style="background-color:#fef2f2; color:#dc2626; padding:0.2rem 0.6rem; border-radius:0.25rem;">Selisih {{ $existing->selisih >= 0 ? '+' : '' }}{{ $existing->selisih }}</span>
                                    @else
                                        <span class="text-secondary-light" style="font-style: italic;">-</span>
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="radio" class="btn-check" name="status-{{ $product->id }}" id="pas-{{ $product->id }}" value="pas" autocomplete="off" {{ !$existing || $existing->status === 'pas' ? 'checked' : '' }}>
                                    <label for="pas-{{ $product->id }}" class="btn btn-sm btn-outline-success px-3" style="border-width:1.5px;">Pas</label>

                                    <input type="radio" class="btn-check" name="status-{{ $product->id }}" id="selisih-{{ $product->id }}" value="selisih" autocomplete="off" {{ $existing && $existing->status === 'selisih' ? 'checked' : '' }}>
                                    <label for="selisih-{{ $product->id }}" class="btn btn-sm btn-outline-danger px-3" style="border-width:1.5px;">Selisih</label>
                                </div>

                                <div class="selisih-fields mt-2 d-none gap-2 flex-wrap">
                                    <input type="number" class="form-control form-control-sm stok-fisik" min="0" placeholder="Stok fisik" value="{{ $existing?->stok_fisik ?? '' }}" style="width:110px; border-radius:0.5rem; border:1px solid #e2e8f0;">
                                    <input type="number" class="form-control form-control-sm stok-penetapan" min="0" placeholder="Penetapan (ops.)" value="{{ $existing?->stok_penetapan ?? '' }}" style="width:140px; border-radius:0.5rem; border:1px solid #e2e8f0;">
                                    <input type="text" class="form-control form-control-sm so-catatan" maxlength="255" placeholder="Catatan (mis. rusak/hilang)" value="{{ $existing?->catatan ?? '' }}" style="width:200px; border-radius:0.5rem; border:1px solid #e2e8f0;">
                                </div>

                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-primary-600 radius-8 px-16 py-8 fw-semibold btn-save" style="box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                        <iconify-icon icon="lucide:save" style="font-size:1rem;"></iconify-icon> Simpan
                                    </button>
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

@endsection

@section('scripts')
<script>
    const CSRF = "@json(csrf_token())";
    const soId = {{ $stockOpname->id }};
    const itemUrl = (pid) => "{{ route('stock-opname.item', [$stockOpname, ':pid']) }}".replace(':pid', pid);

    function updateProgress() {
        const total = $('tr[data-product-id]').length;
        const saved = $('tr.row-saved').length;
        const pct = total ? Math.round(saved / total * 100) : 0;
        $('#progressBar').css('width', pct + '%');
        $('#progressText').text(saved + ' / ' + total + ' dihitung');
    }

    function renderChip($row, status, selisih) {
        let html;
        if (status === 'pas') {
            html = '<span style="background-color:#f0fdf4; color:#059669; padding:0.2rem 0.6rem; border-radius:0.25rem;">Pas</span>';
        } else {
            const s = (selisih >= 0 ? '+' : '') + selisih;
            html = '<span style="background-color:#fef2f2; color:#dc2626; padding:0.2rem 0.6rem; border-radius:0.25rem;">Selisih ' + s + '</span>';
        }
        $row.find('.status-chip').html(html);
    }

    $(function () {
        if (!$soCountingTable.length) return;

        const table = $soCountingTable.DataTable({
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, 'Semua']],
            order: [[0, 'asc']]
        });

        $soCountingTable.closest('.table-responsive').find('.dataTables_filter input').attr('placeholder', 'Cari nama / barcode...');

        // Toggle field selisih sesuai radio
        $('input[type=radio]').on('change', function () {
            const $row = $(this).closest('tr');
            const showSelisih = this.value === 'selisih';
            $row.find('.selisih-fields').toggleClass('d-none', !showSelisih).toggleClass('d-flex', showSelisih);
            $row.addClass('is-dirty');
        });

        // Tandai dirty saat input berubah
        $('.selisih-fields input').on('input change', function () {
            $(this).closest('tr').addClass('is-dirty');
        });

        // Inisialisasi tampilan selisih untuk baris yang sudah ada
        $('tr[data-product-id]').each(function () {
            const $row = $(this);
            if ($row.find('input[value=selisih]').is(':checked')) {
                $row.find('.selisih-fields').removeClass('d-none').addClass('d-flex');
            }
        });

        // Simpan per baris
        $('.btn-save').on('click', function () {
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const pid = $row.data('product-id');
            const status = $row.find('input[type=radio]:checked').val();

            if (!status) {
                alert('Pilih dulu status Pas atau Selisih.');
                return;
            }

            const payload = { status: status };
            const fisik = $row.find('.stok-fisik').val();
            if (status === 'selisih') {
                if (fisik === '' || fisik === null) {
                    alert('Stok fisik wajib diisi saat status Selisih.');
                    return;
                }
                payload.stok_fisik = fisik;
            }
            const penetapan = $row.find('.stok-penetapan').val();
            if (penetapan !== '') payload.stok_penetapan = penetapan;
            const catatan = $row.find('.so-catatan').val();
            if (catatan) payload.catatan = catatan;

            const original = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" style="width:0.9rem;height:0.9rem;"></span>');

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
                renderChip($row, d.item.status, d.item.selisih);
                if (d.item.stok_penetapan !== null) {
                    $row.find('.stok-penetapan').val(d.item.stok_penetapan);
                }
                $row.addClass('row-saved').removeClass('is-dirty');
                updateProgress();
            })
            .catch(err => alert(err.message || 'Terjadi kesalahan saat menyimpan.'))
            .finally(() => {
                $btn.prop('disabled', false).html(original);
            });
        });

        // Peringatan bila ada data belum disimpan
        $(window).on('beforeunload', function (e) {
            if ($('tr.is-dirty').length) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });
</script>
@endsection