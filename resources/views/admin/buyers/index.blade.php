@extends('layouts.main')

@section('title', 'Buyers')
@section('subTitle', 'List')

@section('content')

<div class="card h-100 p-0 radius-12">
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
        <div class="d-flex align-items-center flex-wrap gap-3">
            <form class="d-flex" action="{{ route('buyers.index') }}" method="GET">
                <input type="text" class="form-control bg-base h-40-px flex-grow-1" name="q" placeholder="Cari nama atau nomor HP" value="{{ request('q') }}">
                <button type="submit" class="btn btn-sm btn-primary">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </button>
            </form>
        </div>
        <button 
            class="add-btn btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
            Add New Buyer
        </button>
    </div>

    <div class="card-body p-24">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="akar-icons:circle-check-fill" class="text-success"></iconify-icon>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="akar-icons:circle-x-fill" class="text-danger"></iconify-icon>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Transactions</th>
                        <th scope="col">Outstanding Debt</th>
                        <th scope="col">Date Joined</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($buyers as $buyer)
                    <tr>
                        <td>{{ $buyer->id }}</td>
                        <td>{{ $buyer->name }}</td>
                        <td>{{ $buyer->phone ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ $buyer->sale_documents_count }} {{ $buyer->sale_documents_count === 1 ? 'Transaksi' : 'Transaksi' }}
                            </span>
                        </td>
                        <td>
                            @if($buyer->total_debt_outstanding > 0)
                                <span class="badge bg-warning text-dark">
                                    Rp {{ number_format($buyer->total_debt_outstanding, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="badge bg-success">
                                    Lunas
                                </span>
                            @endif
                        </td>
                        <td>{{ $buyer->created_at->format('d M Y') }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <a href="{{ route('buyers.show', $buyer->id) }}">
                                    <button type="button" class="bg-info-focus text-info-600 bg-hover-info-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" title="View">
                                        <iconify-icon icon="lucide:eye" class="menu-icon"></iconify-icon>
                                    </button>
                                </a>
                                <button type="button" data-buyer="{{ json_encode($buyer) }}"
                                    class="edit-btn bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>
                                <form action="{{ route('buyers.destroy', $buyer->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="remove-item-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" onclick="return confirm('Hapus buyer ini?')">
                                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-3">
                            <span class="text-muted">Tidak ada buyer</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>Menampilkan {{ $buyers->count() > 0 ? $buyers->firstItem() : 0 }} hingga {{ $buyers->count() > 0 ? $buyers->lastItem() : 0 }} dari {{ $buyers->total() }} entri</span>
            {{ $buyers->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<!-- Modal untuk Add -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Add New Buyer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addForm" action="{{ route('buyers.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="62812345678">
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Add Buyer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Buyer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="{{ route('buyers.update', ':id') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="edit-phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="edit-phone" name="phone" placeholder="62812345678">
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update Buyer</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(".add-btn").on("click", function () {
        // Reset form
        $('#addForm')[0].reset();
        $('#addModal').modal('show');
    });

    $(".edit-btn").on("click", function () {
        var buyer = $(this).data('buyer');
        console.log(buyer);

        $('#edit-name').val(buyer.name);
        $('#edit-phone').val(buyer.phone ?? '');

        // Update action form dengan ID yang sesuai
        var formAction = "{{ route('buyers.update', ':id') }}".replace(':id', buyer.id);
        $('#editForm').attr('action', formAction);

        // Tampilkan modal
        $('#editModal').modal('show');
    });
</script>
@endsection
