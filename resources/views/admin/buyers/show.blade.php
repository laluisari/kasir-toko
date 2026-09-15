@extends('layouts.main')

@section('title', 'Buyer Detail')
@section('subTitle', $buyer->name)

@section('content')

<div class="row gy-4">
    <!-- Buyer Info Card -->
    <div class="col-md-4">
        <div class="card h-100 p-0 radius-12">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h5 class="mb-0">Buyer Information</h5>
            </div>
            <div class="card-body p-24">
                <div class="mb-4">
                    <label class="form-label text-muted">Nama</label>
                    <p class="mb-0 fw-semibold">{{ $buyer->name }}</p>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted">Nomor HP</label>
                    <p class="mb-0">{{ $buyer->phone ?? 'Tidak ada' }}</p>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted">Bergabung Sejak</label>
                    <p class="mb-0">{{ $buyer->created_at->format('d M Y H:i') }}</p>
                </div>

                <hr>

                <div class="mb-4">
                    <label class="form-label text-muted">Jumlah Nota Hutang</label>
                    <p class="mb-0 fw-semibold">
                        <span class="badge bg-info">{{ count($buyer->saleDocuments) }}</span>
                    </p>
                </div>

                <a href="{{ route('buyers.index') }}" class="btn btn-secondary btn-sm w-100">
                    <iconify-icon icon="lucide:arrow-left" class="icon"></iconify-icon>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Debt Summary & History -->
    <div class="col-md-8">
        <!-- Summary Card -->
        <div class="card h-100 p-0 radius-12 mb-3">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h5 class="mb-0">Debt Summary</h5>
            </div>
            <div class="card-body p-24">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <label class="form-label text-muted d-block">Total Hutang</label>
                            <p class="mb-0 fs-5 fw-semibold">Rp {{ number_format($totalDebt, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <label class="form-label text-muted d-block">Sudah Dibayar</label>
                            <p class="mb-0 fs-5 fw-semibold text-success">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <label class="form-label text-muted d-block">Sisa Hutang</label>
                            <p class="mb-0 fs-5 fw-semibold {{ $totalOutstanding > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debt History -->
        <div class="card h-100 p-0 radius-12">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h5 class="mb-0">Riwayat Hutang</h5>
            </div>
            <div class="card-body p-24">
                @if(count($buyer->saleDocuments) > 0)
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table sm-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Invoice</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Sudah Dibayar</th>
                                    <th scope="col">Due Date</th>
                                    <th scope="col">Outstanding</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($buyer->saleDocuments as $sale)
                                <tr>
                                    <td>
                                        <a href="{{ route('sales.show', $sale->id) }}" class="fw-semibold">
                                            {{ $sale->invoice_number }}
                                        </a>
                                    </td>
                                    <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                                    <td>
                                        Rp {{ number_format(($sale->down_payment ?? 0) + $sale->debtPayments->sum('amount'), 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if($sale->due_date)
                                            {{ \Carbon\Carbon::parse($sale->due_date)->format('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ ($sale->debt_remaining ?? 0) > 0 ? 'bg-warning text-dark' : 'bg-success' }}">
                                            Rp {{ number_format($sale->debt_remaining ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($sale->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($sale->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($sale->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center">
                                            @if($sale->status === 'pending' && ($sale->debt_remaining ?? 0) > 0)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-success"
                                                data-pay-url="{{ route('sales.pay-debt', $sale->id) }}"
                                                data-invoice="{{ $sale->invoice_number }}"
                                                data-remaining="{{ $sale->debt_remaining }}"
                                                onclick="openPayModal(this)"
                                            >
                                                Bayar
                                            </button>
                                            @endif
                                            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info">
                                                <iconify-icon icon="lucide:eye" class="icon"></iconify-icon>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">Tidak ada riwayat hutang</p>
                @endif
            </div>
        </div>

        <!-- Payment History -->
        @php
            $allPayments = collect();
            foreach ($buyer->saleDocuments as $sale) {
                foreach ($sale->debtPayments as $pay) {
                    $allPayments->push((object) [
                        'sale' => $sale,
                        'pay' => $pay,
                    ]);
                }
            }
        @endphp
        <div class="card h-100 p-0 radius-12">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h5 class="mb-0">Riwayat Pembayaran Hutang</h5>
            </div>
            <div class="card-body p-24">
                @if($allPayments->count() > 0)
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table sm-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Invoice</th>
                                    <th scope="col">Kasir</th>
                                    <th scope="col">Metode</th>
                                    <th scope="col">Nominal</th>
                                    <th scope="col">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allPayments->sortByDesc(fn ($p) => $p->pay->paid_at) as $item)
                                <tr>
                                    <td>{{ $item->pay->paid_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('sales.show', $item->sale->id) }}" class="fw-semibold">
                                            {{ $item->sale->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ $item->pay->user->name ?? 'Admin' }}</td>
                                    <td>{{ strtoupper($item->pay->payment_method) }}</td>
                                    <td>Rp {{ number_format($item->pay->amount, 0, ',', '.') }}</td>
                                    <td>{{ $item->pay->note ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">Belum ada pembayaran hutang</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Terima Pembayaran Hutang -->
<div class="modal fade" id="payDebtModal" tabindex="-1" aria-labelledby="payDebtModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payDebtModalLabel">Terima Pembayaran Hutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Invoice</label>
                    <p class="fw-semibold mb-0" id="payInvoice">-</p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Sisa Hutang</label>
                    <p class="mb-0 fs-5 fw-bold text-danger" id="payRemaining">Rp 0</p>
                </div>

                <div id="payError" class="alert alert-danger py-2 d-none" style="font-size: 0.85rem;"></div>

                <div class="mb-3">
                    <label for="payAmount" class="form-label">Nominal Pembayaran <span class="text-danger">*</span></label>
                    <input type="number" id="payAmount" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label for="payMethod" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select id="payMethod" class="form-select">
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="payNote" class="form-label">Catatan</label>
                    <textarea id="payNote" class="form-control" rows="2" placeholder="Opsional"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="paySubmitBtn" class="btn btn-success">Simpan Pembayaran</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    let payDebtModalEl = null;
    let payUrl = null;

    function openPayModal(btn) {
        payUrl = btn.dataset.payUrl;
        document.getElementById('payInvoice').textContent = btn.dataset.invoice;
        document.getElementById('payRemaining').textContent = 'Rp ' + formatRupiah(btn.dataset.remaining);
        document.getElementById('payAmount').value = btn.dataset.remaining;
        document.getElementById('payMethod').value = 'cash';
        document.getElementById('payNote').value = '';
        const err = document.getElementById('payError');
        err.classList.add('d-none');
        err.textContent = '';

        if (!payDebtModalEl) {
            payDebtModalEl = new bootstrap.Modal(document.getElementById('payDebtModal'));
        }
        payDebtModalEl.show();
    }

    function formatRupiah(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    document.getElementById('paySubmitBtn').addEventListener('click', async function () {
        const btn = this;
        const err = document.getElementById('payError');
        const amount = parseInt(document.getElementById('payAmount').value, 10);

        if (!amount || amount < 1) {
            err.textContent = 'Nominal pembayaran harus diisi dan lebih dari 0.';
            err.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
        err.classList.add('d-none');

        try {
            const response = await fetch(payUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    amount: amount,
                    payment_method: document.getElementById('payMethod').value,
                    note: document.getElementById('payNote').value.trim()
                })
            });

            const data = await response.json();

            if (!response.ok) {
                const message = (data.errors && data.errors.amount && data.errors.amount[0]) || data.message || 'Gagal menyimpan pembayaran.';
                throw new Error(message);
            }

            alert(data.message || 'Pembayaran berhasil dicatat.');
            window.location.reload();
        } catch (error) {
            err.textContent = error.message || 'Terjadi kesalahan saat menyimpan pembayaran.';
            err.classList.remove('d-none');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Simpan Pembayaran';
        }
    });
</script>
@endsection
