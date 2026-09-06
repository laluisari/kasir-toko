@extends('layouts.main')

@section('title', 'Expenses')
@section('subTitle', 'List')

@section('content')

<div class="card h-100 p-0 radius-12">
    <div
        class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center flex-wrap gap-3 justify-content-between">
        <div class="d-flex align-items-center flex-wrap gap-3">

            <form class="d-flex" action="{{ route('expense.search') }}" method="GET">
                <input type="text" class="form-control bg-base h-40-px flex-grow-1" name="q" placeholder="Search">
                <button type="submit" class="btn btn-sm btn-primary">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </button>
            </form>

        </div>
        <button 
            class="add-btn btn btn-primary text-sm btn-sm px-12 py-12 radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
            Add New Expense
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
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>

                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Date</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                    <tr>
                        <td>{{ $expense->id }}</td>
                        <td>{{ $expense->name }}</td>
                        <td>{{ $expense->category }}</td>
                        <td>{{ formatRupiah($expense->amount) }}</td>
                        <td>{{ $expense->created_at }}</td>

                        <td class="text-center">
                            <div class="d-flex align-items-center gap-10 justify-content-center">
                                <button type="button" data-expense="{{ json_encode($expense) }}"
                                    class="edit-btn bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>
                                <a href="{{ url('admin/expense/delete/' . $expense->id) }}">
                                    <button type="button" class="remove-item-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle">
                                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                    </button>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-24">
            <span>Menampilkan {{ $expenses->firstItem() }} hingga {{ $expenses->lastItem() }} dari
                {{ $expenses->total() }} entri</span>
            {{ $expenses->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>



<!-- Modal untuk Add -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addForm" action="{{ route('expense.add') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="account">Account</option>
                            <option value="marketing">Marketing</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="amount" name="amount" placeholder="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Note</label>
                        <textarea class="form-control" id="note" name="note"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="periode" class="form-label">Periode</label>
                        <select class="form-control" id="periode" name="periode">
                            <option value="1">1 Bulan</option>
                            <option value="3">3 Bulan</option>
                            <option value="6">6 Bulan</option>
                            <option value="12">12 Bulan</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal untuk Add -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="{{ route('expense.update', ':id') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="edit-category" name="category">
                            <option value="account">Account</option>
                            <option value="marketing">Marketing</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="edit-amount" name="amount" placeholder="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Note</label>
                        <textarea class="form-control" id="edit-note" name="note"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script>
    // Format Rupiah untuk input amount
    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? '' + rupiah : '');
    }

    // Event listener untuk format rupiah pada kedua field amount
    $('#amount, #edit-amount').on('keyup', function(e) {
        $(this).val(formatRupiah($(this).val(), ''));
    });

    $(".add-btn").on("click", function () {
        $('#addModal').modal('show');
    });

    $(".edit-btn").on("click", function () {
        var expense = $(this).data('expense');
        console.log(expense);

        $('#edit-name').val(expense.name);
        $('#edit-category').val(expense.category);
        $('#edit-amount').val(formatRupiah(expense.amount.toString(), ''));
        $('#edit-note').val(expense.note);

        // Update action form dengan ID yang sesuai
        var formAction = "{{ route('expense.update', ':id') }}".replace(':id', expense.id);
        $('#editForm').attr('action', formAction);

        // Tampilkan modal
        $('#editModal').modal('show');
    });

    // Form submission - convert formatted amount back to number
    $('#addForm, #editForm').on('submit', function(e) {
        // Convert formatted amount back to number for submission
        const amountFields = $(this).find('#amount, #edit-amount');
        amountFields.each(function() {
            if ($(this).val()) {
                const cleanAmount = $(this).val().replace(/\./g, '');
                $(this).val(cleanAmount);
            }
        });
    });

</script>
@endsection
