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
                    <label class="form-label text-muted">Total Transaksi</label>
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
                                    <th scope="col">Down Payment</th>
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
                                    <td>Rp {{ number_format($sale->down_payment ?? 0, 0, ',', '.') }}</td>
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
                                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info">
                                            <iconify-icon icon="lucide:eye" class="icon"></iconify-icon>
                                        </a>
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
    </div>
</div>

@endsection
