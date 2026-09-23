@extends('layouts.main')

@section('title', 'Products')
@section('subTitle', 'List')

@section('content')

<div class="card h-100 p-0 radius-12">
    <!-- Card Header -->
    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between" style="min-height: 70px;">
        <div class="d-flex align-items-center gap-2">
            <h6 class="text-lg fw-semibold text-primary-light mb-0">Products</h6>
            <span class="text-secondary-light fw-normal">List</span>
        </div>
        <button type="button" class="add-btn btn btn-primary-600 radius-8 px-20 py-10 d-flex align-items-center gap-2" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <iconify-icon icon="lucide:plus" class="icon"></iconify-icon>
            Tambah Product
        </button>
    </div>

    <div class="card-body p-24">
        <!-- Success Alert -->
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-20" role="alert" style="border-radius: 0.75rem; border: 1px solid rgba(34, 197, 94, 0.3);">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="akar-icons:circle-check-fill" class="text-success" style="font-size: 1.25rem;"></iconify-icon>
                <span class="fw-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        <!-- Table Section -->
        <div class="table-responsive scroll-sm">
            <table class="table bordered-table sm-table mb-0">
                <thead>
                    <tr>
                        <th class="text-sm text-secondary-light fw-semibold" style="width:48px">#</th>
                        <th class="text-sm text-secondary-light fw-semibold">Nama</th>
                        <th class="text-sm text-secondary-light fw-semibold">Barcode</th>
                        <th class="text-sm text-secondary-light fw-semibold">Category</th>
                        <th class="text-sm text-secondary-light fw-semibold">Harga Beli</th>
                        <th class="text-sm text-secondary-light fw-semibold">Harga Jual</th>
                        <th class="text-sm text-secondary-light fw-semibold">Stock</th>
                        <th class="text-sm text-secondary-light fw-semibold">Unit</th>
                        <th class="text-sm text-secondary-light fw-semibold">Dibuat</th>
                        <th class="text-sm text-secondary-light fw-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr style="transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='rgba(0,0,0,0.02)'" onmouseout="this.style.backgroundColor='transparent'">
                            <td class="text-sm text-secondary-light">{{ $loop->iteration }}</td>
                            <td class="text-sm text-secondary-light fw-medium">{{ $product->name }}</td>
                            <td class="text-sm text-secondary-light">
                                @if ($product->barcode)
                                    <span style="font-family: monospace; background-color: #f8fafc; padding: 0.25rem 0.75rem; border-radius: 0.25rem; border: 1px solid #e2e8f0;">
                                        {{ $product->barcode }}
                                    </span>
                                @else
                                    <span class="text-secondary-light" style="font-style: italic;">-</span>
                                @endif
                            </td>
                            <td class="text-sm text-secondary-light">
                                @if ($product->category)
                                    <span style="background-color: #f0fdf4; color: #059669; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.8rem; font-weight: 500;">
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-secondary-light" style="font-style: italic;">-</span>
                                @endif
                            </td>
                            <td class="text-sm text-secondary-light">{{ formatRupiah($product->cost_price) }}</td>
                            <td class="text-sm text-secondary-light fw-medium" style="color: #059669;">{{ formatRupiah($product->selling_price) }}</td>
                            <td class="text-sm">
                                @if ($product->stock > 10)
                                    <span style="background-color: #f0fdf4; color: #059669; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-weight: 500;">{{ $product->stock }}</span>
                                @elseif ($product->stock > 0)
                                    <span style="background-color: #fffbeb; color: #b45309; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-weight: 500;">{{ $product->stock }}</span>
                                @else
                                    <span style="background-color: #fef2f2; color: #dc2626; padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-weight: 500;">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td class="text-sm text-secondary-light">{{ $product->unit }}</td>
                            <td class="text-sm text-secondary-light">{{ $product->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-8">
                                    <!-- Print Barcode Button -->
                                    @php $hasBarcode = !empty($product->barcode); @endphp
                                    <button type="button" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-barcode="{{ $product->barcode ?? '' }}" class="print-barcode-btn {{ $hasBarcode ? '' : 'print-disabled' }} d-flex align-items-center justify-content-center"
                                            {{ $hasBarcode ? '' : 'disabled' }}
                                            style="width: 36px; height: 36px; border-radius: 0.5rem; background-color: #eff6ff; border: 1.5px solid #bfdbfe; color: #2563eb; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                                            onmouseover="if(!this.disabled){this.style.backgroundColor='#2563eb'; this.style.color='white'; this.style.boxShadow='0 4px 12px rgba(37, 99, 235, 0.3)';}"
                                            onmouseout="if(!this.disabled){this.style.backgroundColor='#eff6ff'; this.style.color='#2563eb'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';}"
                                            title="{{ $hasBarcode ? 'Cetak Barcode' : 'Barcode belum diisi' }}">
                                        <iconify-icon icon="lucide:barcode" class="icon" style="font-size: 1.125rem;"></iconify-icon>
                                    </button>

                                    <!-- Edit Button -->
                                    <button type="button" data-product="{{ json_encode($product->load('category')) }}" class="edit-btn d-flex align-items-center justify-content-center" 
                                            style="width: 36px; height: 36px; border-radius: 0.5rem; background-color: #f0fdf4; border: 1.5px solid #bbf7d0; color: #059669; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                                            onmouseover="this.style.backgroundColor='#059669'; this.style.color='white'; this.style.boxShadow='0 4px 12px rgba(5, 150, 105, 0.3)';"
                                            onmouseout="this.style.backgroundColor='#f0fdf4'; this.style.color='#059669'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                                        <iconify-icon icon="lucide:edit" class="icon" style="font-size: 1.125rem;"></iconify-icon>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="handleFormSubmit(event, 'Yakin ingin menghapus produk ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="d-flex align-items-center justify-content-center" 
                                                style="width: 36px; height: 36px; border-radius: 0.5rem; background-color: #fef2f2; border: 1.5px solid #fecaca; color: #dc2626; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05); padding: 0; margin: 0;"
                                                onmouseover="this.style.backgroundColor='#dc2626'; this.style.color='white'; this.style.boxShadow='0 4px 12px rgba(220, 38, 38, 0.3)';"
                                                onmouseout="this.style.backgroundColor='#fef2f2'; this.style.color='#dc2626'; this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                                            <iconify-icon icon="lucide:trash-2" class="icon" style="font-size: 1.125rem;"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-secondary-light py-40">
                                <iconify-icon icon="lucide:inbox" class="d-block mx-auto mb-8" style="font-size:2.5rem; opacity:0.3;"></iconify-icon>
                                <p class="mb-0">Belum ada data produk.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal untuk Add -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 650px;">
        <div class="modal-content" style="border-radius: 1rem; overflow: hidden; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 20px 25px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom-0 bg-base pt-20 px-24 pb-8">
                <h5 class="modal-title fw-semibold text-primary-light" id="addModalLabel">Tambah Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-24 py-24" style="background-color: #fafbfc; max-height: 70vh; overflow-y: auto;">
                <form id="addForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Name -->
                    <div class="mb-20">
                        <label for="name" class="form-label fw-semibold text-sm text-primary-light mb-8">Nama Product <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Misal: Laptop Dell Inspiron 15" required 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <!-- Category -->
                    <div class="mb-20">
                        <label for="category_id" class="form-label fw-semibold text-sm text-primary-light mb-8">Category <span class="text-secondary-light">(Opsional)</span></label>
                        <select class="form-control" id="category_id" name="category_id" 
                                style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            <option value="">-- Pilih Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Barcode -->
                    <div class="mb-20">
                        <label for="barcode" class="form-label fw-semibold text-sm text-primary-light mb-8">Barcode <span class="text-secondary-light">(Opsional)</span></label>
                        <input type="text" class="form-control" id="barcode" name="barcode" placeholder="Scan atau input barcode"
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <!-- Prices Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label for="cost_price" class="form-label fw-semibold text-sm text-primary-light mb-8">Harga Beli <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rupiah" id="cost_price" name="cost_price" placeholder="0" required
                                       style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                       onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label for="selling_price" class="form-label fw-semibold text-sm text-primary-light mb-8">Harga Jual <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rupiah" id="selling_price" name="selling_price" placeholder="0" required
                                       style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                       onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>
                        </div>
                    </div>

                    <!-- Discount -->
                    <div class="mb-20">
                        <label for="discount" class="form-label fw-semibold text-sm text-primary-light mb-8">Diskon <span class="text-secondary-light">(Opsional)</span></label>
                        <input type="text" class="form-control rupiah" id="discount" name="discount" placeholder="0"
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <!-- Stock & Unit Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label for="stock" class="form-label fw-semibold text-sm text-primary-light mb-8">Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stock" name="stock" placeholder="0" value="0" required
                                       style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                       onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label for="unit" class="form-label fw-semibold text-sm text-primary-light mb-8">Unit <span class="text-danger">*</span></label>
                                <select class="form-control" id="unit" name="unit" required
                                        style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <option value="pcs">Pcs</option>
                                    <option value="bks">Bks</option>
                                    <option value="botol">Botol</option>
                                    <option value="kg">Kg</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-24">
                        <label for="image" class="form-label fw-semibold text-sm text-primary-light mb-8">Gambar Product <span class="text-secondary-light">(Opsional)</span></label>
                        <div style="border: 2px dashed #e2e8f0; border-radius: 0.5rem; padding: 1rem; text-align: center; cursor: pointer; transition: all 0.2s ease;" 
                             id="dropZone" onmouseover="this.style.borderColor='#3b82f6'; this.style.backgroundColor='rgba(59, 130, 246, 0.05)';" 
                             onmouseout="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='transparent';">
                            <iconify-icon icon="lucide:image-plus" style="font-size: 2rem; color: #94a3b8; display: block; margin-bottom: 0.5rem;"></iconify-icon>
                            <p class="text-sm text-secondary-light mb-2">Klik atau drag gambar ke sini</p>
                            <small class="text-secondary-light">Format: JPG, PNG, GIF (Max 2MB)</small>
                            <input type="file" id="image" name="image" accept="image/*" class="d-none" onchange="previewImage(this)">
                        </div>
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 0.5rem;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">Hapus Gambar</button>
                        </div>
                    </div>

                    <hr>

                    <div class="form-switch switch-primary d-flex align-items-center gap-3 mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="add-record-expense"
                            name="record_expense" value="1" onchange="toggleExpenseSection('add')">
                        <label class="form-check-label line-height-1 fw-medium text-secondary-light" for="add-record-expense">
                            Expense
                        </label>
                    </div>

                    <div id="add-expense-section" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control rupiah-input" id="add-expense-amount"
                                    name="expense_amount" placeholder="0">
                            </div>
                        </div>
                        {{-- <div class="mb-3">
                            <label class="form-label">Periode</label>
                            <select class="form-control" id="add-expense-periode" name="expense_periode">
                                <option value="1">1 Bulan</option>
                                <option value="3">3 Bulan</option>
                                <option value="6">6 Bulan</option>
                                <option value="12">12 Bulan</option>
                            </select>
                        </div> --}}
                    </div>

                    <button type="submit" class="btn btn-primary-600 w-100 radius-8" 
                            style="padding: 0.6rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);"
                            onmouseover="this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.3)';"
                            onmouseout="this.style.boxShadow='0 2px 8px rgba(59, 130, 246, 0.2)';">
                        Simpan Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 650px;">
        <div class="modal-content" style="border-radius: 1rem; overflow: hidden; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 20px 25px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom-0 bg-base pt-20 px-24 pb-8">
                <h5 class="modal-title fw-semibold text-primary-light" id="editModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-24 py-24" style="background-color: #fafbfc; max-height: 70vh; overflow-y: auto;">
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Name -->
                    <div class="mb-20">
                        <label for="edit-name" class="form-label fw-semibold text-sm text-primary-light mb-8">Nama Product <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" placeholder="Misal: Laptop Dell Inspiron 15" required 
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <!-- Category -->
                    <div class="mb-20">
                        <label for="edit-category_id" class="form-label fw-semibold text-sm text-primary-light mb-8">Category <span class="text-secondary-light">(Opsional)</span></label>
                        <select class="form-control" id="edit-category_id" name="category_id" 
                                style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            <option value="">-- Pilih Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Barcode -->
                    <div class="mb-20">
                        <label for="edit-barcode" class="form-label fw-semibold text-sm text-primary-light mb-8">Barcode <span class="text-secondary-light">(Opsional)</span></label>
                        <input type="text" class="form-control" id="edit-barcode" name="barcode" placeholder="Scan atau input barcode"
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <!-- Prices Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label for="edit-cost_price" class="form-label fw-semibold text-sm text-primary-light mb-8">Harga Beli <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rupiah" id="edit-cost_price" name="cost_price" placeholder="0" required
                                       style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                       onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label for="edit-selling_price" class="form-label fw-semibold text-sm text-primary-light mb-8">Harga Jual <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rupiah" id="edit-selling_price" name="selling_price" placeholder="0" required
                                       style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                       onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>
                        </div>
                    </div>

                    <!-- Discount -->
                    <div class="mb-20">
                        <label for="edit-discount" class="form-label fw-semibold text-sm text-primary-light mb-8">Diskon <span class="text-secondary-light">(Opsional)</span></label>
                        <input type="text" class="form-control rupiah" id="edit-discount" name="discount" placeholder="0"
                               style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                               onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                    </div>

                    <!-- Stock & Unit Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label for="edit-stock" class="form-label fw-semibold text-sm text-primary-light mb-8">Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit-stock" name="stock" placeholder="0" required
                                       style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                       onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label for="edit-unit" class="form-label fw-semibold text-sm text-primary-light mb-8">Unit <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit-unit" name="unit" required
                                        style="border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s ease;"
                                        onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59, 130, 246, 0.1)';"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                                    <option value="pcs">Pcs</option>
                                    <option value="bks">Bks</option>
                                    <option value="botol">Botol</option>
                                    <option value="kg">Kg</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-24">
                        <label for="edit-image" class="form-label fw-semibold text-sm text-primary-light mb-8">Gambar Product <span class="text-secondary-light">(Opsional)</span></label>
                        
                        <!-- Current Image Preview -->
                        <div id="currentImagePreview" class="mb-3" style="display: none;">
                            <p class="text-sm text-secondary-light mb-2">Gambar saat ini:</p>
                            <img id="currentImg" src="" alt="Current" style="max-width: 100%; max-height: 150px; border-radius: 0.5rem;">
                        </div>

                        <div style="border: 2px dashed #e2e8f0; border-radius: 0.5rem; padding: 1rem; text-align: center; cursor: pointer; transition: all 0.2s ease;" 
                             id="editDropZone" onmouseover="this.style.borderColor='#3b82f6'; this.style.backgroundColor='rgba(59, 130, 246, 0.05)';" 
                             onmouseout="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='transparent';">
                            <iconify-icon icon="lucide:image-plus" style="font-size: 2rem; color: #94a3b8; display: block; margin-bottom: 0.5rem;"></iconify-icon>
                            <p class="text-sm text-secondary-light mb-2">Klik atau drag gambar ke sini</p>
                            <small class="text-secondary-light">Format: JPG, PNG, GIF (Max 2MB)</small>
                            <input type="file" id="edit-image" name="image" accept="image/*" class="d-none" onchange="editPreviewImage(this)">
                        </div>
                        <div id="editImagePreview" class="mt-3" style="display: none;">
                            <img id="editPreviewImg" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 0.5rem;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="editRemoveImage()">Hapus Gambar Baru</button>
                        </div>
                    </div>

                    <hr>

                    <div class="form-switch switch-primary d-flex align-items-center gap-3 mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="edit-record-expense"
                            name="record_expense" value="1" onchange="toggleExpenseSection('edit')">
                        <label class="form-check-label line-height-1 fw-medium text-secondary-light" for="edit-record-expense">
                            Expense
                        </label>
                    </div>

                    <div id="edit-expense-section" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control rupiah-input" id="edit-expense-amount"
                                    name="expense_amount" placeholder="0">
                            </div>
                        </div>
                        {{-- <div class="mb-3">
                            <label class="form-label">Periode</label>
                            <select class="form-control" id="edit-expense-periode" name="expense_periode">
                                <option value="1">1 Bulan</option>
                                <option value="3">3 Bulan</option>
                                <option value="6">6 Bulan</option>
                                <option value="12">12 Bulan</option>
                            </select>
                        </div> --}}
                    </div>

                    <button type="submit" class="btn btn-primary-600 w-100 radius-8" 
                            style="padding: 0.6rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);"
                            onmouseover="this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.3)';"
                            onmouseout="this.style.boxShadow='0 2px 8px rgba(59, 130, 246, 0.2)';">
                        Perbarui Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
    .print-disabled {
        opacity: 0.35;
        cursor: not-allowed !important;
    }
</style>
<script>
    let categories = {!! json_encode($categories) !!};

    // Format Rupiah
    function formatRupiah(value) {
        return value.toString().replace(/[^,\d]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Clean Rupiah (remove formatting)
    function cleanRupiah(value) {
        return value.replace(/\./g, '');
    }

    // Rupiah formatter
    function toRupiah(angka) {
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) rupiah += (sisa ? '.' : '') + ribuan.join('.');
        return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function toggleExpenseSection(prefix) {
        var checked = $('#' + prefix + '-record-expense').is(':checked');
        $('#' + prefix + '-expense-section').toggle(checked);
    }

    // Format rupiah on expense amount input
    $(document).on('keyup', '#add-expense-amount, #edit-expense-amount', function () {
        $(this).val(toRupiah($(this).val()));
    });

    // Add Modal - Rupiah input listener
    document.querySelectorAll('#addForm .rupiah').forEach(el => {
        el.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
        });
    });

    // Edit Modal - Rupiah input listener
    document.querySelectorAll('#editForm .rupiah').forEach(el => {
        el.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
        });
    });

    // Add Modal - Drag & Drop
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('click', () => document.getElementById('image').click());
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#3b82f6';
        dropZone.style.backgroundColor = 'rgba(59, 130, 246, 0.05)';
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = '#e2e8f0';
        dropZone.style.backgroundColor = 'transparent';
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        document.getElementById('image').files = e.dataTransfer.files;
        previewImage(document.getElementById('image'));
    });

    // Add Modal - Image Preview
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
                dropZone.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImage() {
        document.getElementById('image').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        dropZone.style.display = 'block';
    }

    // Edit Modal - Drag & Drop
    const editDropZone = document.getElementById('editDropZone');
    editDropZone.addEventListener('click', () => document.getElementById('edit-image').click());
    editDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        editDropZone.style.borderColor = '#3b82f6';
        editDropZone.style.backgroundColor = 'rgba(59, 130, 246, 0.05)';
    });
    editDropZone.addEventListener('dragleave', () => {
        editDropZone.style.borderColor = '#e2e8f0';
        editDropZone.style.backgroundColor = 'transparent';
    });
    editDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        document.getElementById('edit-image').files = e.dataTransfer.files;
        editPreviewImage(document.getElementById('edit-image'));
    });

    // Edit Modal - Image Preview
    function editPreviewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('editPreviewImg').src = e.target.result;
                document.getElementById('editImagePreview').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function editRemoveImage() {
        document.getElementById('edit-image').value = '';
        document.getElementById('editImagePreview').style.display = 'none';
    }

    // Add Button Click
    $(".add-btn").on("click", function() {
        $('#addForm')[0].reset();
        document.getElementById('imagePreview').style.display = 'none';
        dropZone.style.display = 'block';

        $('#add-record-expense').prop('checked', false);
        $('#add-expense-section').hide();
        $('#add-expense-amount').val('');
        $('#add-expense-periode').val('1');

        $('#addModal').modal('show');
    });

    // Edit Button Click
    $(".edit-btn").on("click", function() {
        var product = $(this).data('product');
        console.log(product);

        $('#edit-name').val(product.name);
        $('#edit-category_id').val(product.category_id || '');
        $('#edit-barcode').val(product.barcode || '');
        $('#edit-cost_price').val(formatRupiah(product.cost_price.toString()));
        $('#edit-selling_price').val(formatRupiah(product.selling_price.toString()));
        $('#edit-discount').val(formatRupiah(product.discount.toString()));
        $('#edit-stock').val(product.stock);
        $('#edit-unit').val(product.unit);

        $('#edit-record-expense').prop('checked', false);
        $('#edit-expense-section').hide();
        $('#edit-expense-amount').val('');
        $('#edit-expense-periode').val('1');

        // Reset image preview
        document.getElementById('edit-image').value = '';
        document.getElementById('editImagePreview').style.display = 'none';
        
        // Show current image if exists
        if (product.image) {
            document.getElementById('currentImg').src = '/storage/' + product.image;
            document.getElementById('currentImagePreview').style.display = 'block';
        } else {
            document.getElementById('currentImagePreview').style.display = 'none';
        }

        // Update action form dengan ID yang sesuai
        var formAction = "{{ route('products.update', ':id') }}".replace(':id', product.id);
        $('#editForm').attr('action', formAction);

        // Tampilkan modal
        $('#editModal').modal('show');
    });

    // Form submission - clean rupiah format
    $('#addForm, #editForm').on('submit', function(e) {
        const rupiahFields = $(this).find('.rupiah, .rupiah-input');
        rupiahFields.each(function() {
            if ($(this).val()) {
                $(this).val(cleanRupiah($(this).val()));
            }
        });
    });

    // Print Barcode Button
    const PRINT_MODE = @json(config('thermal.print_mode'));
    const BRIDGE_URL = @json(config('thermal.bridge_url'));
    const BRIDGE_PRINTER = @json(config('thermal.bridge_printer', ''));

    function printBarcodeViaBridge(button, id) {
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span id="printSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width:0.9rem;height:0.9rem;"></span>';

        fetch("{{ route('products.barcode-data', ':id') }}".replace(':id', id), {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (!ok || !data.success) {
                throw new Error(data.error || data.message || 'Gagal ambil data barcode.');
            }
            return sendBridgeBarcode(data.product.name, data.product.barcode, data.product.selling_price);
        })
        .then(success => {
            if (success) alert('Barcode berhasil dikirim ke printer.');
        })
        .catch(error => alert(error.message || 'Terjadi kesalahan saat cetak barcode.'))
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalHtml;
        });
    }

    function sendBridgeBarcode(name, barcode, price) {
        const lineWidth = 42;
        const money = new Intl.NumberFormat('id-ID').format(Number(price || 0));
        const center = (s) => {
            const pad = Math.max(0, Math.floor((lineWidth - s.length) / 2));
            return ' '.repeat(pad) + s;
        };
        const content = center(name) + '\n' +
            center('*' + barcode + '*') + '\n' +
            center('Rp ' + money) + '\n\n';

        const body = { content: content };
        if (BRIDGE_PRINTER) body.printer = BRIDGE_PRINTER;

        return fetch(BRIDGE_URL + '/print', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(response => response.json().then(d => ({ ok: response.ok, data: d })))
        .then(({ ok, data }) => {
            if (!ok || !data.success) throw new Error(data.error || data.message || 'Bridge gagal mencetak.');
            return true;
        })
        .catch(error => {
            throw new Error(error.message + "\nPastikan bridge berjalan di " + BRIDGE_URL);
        });
    }

    function doPrintBarcode(button, id, name) {
        if (PRINT_MODE === 'bridge') {
            printBarcodeViaBridge(button, id);
            return;
        }

        if (PRINT_MODE === 'browser') {
            const labelUrl = "{{ route('products.barcode-label', ':id') }}".replace(':id', id) + '?copies=' + @json(config('thermal.barcode_copies'));
            window.open(labelUrl, '_blank');
            return;
        }

        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span id="printSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width:0.9rem;height:0.9rem;"></span>';

        fetch("{{ route('products.print-barcode', ':id') }}".replace(':id', id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (!ok || !data.success) {
                throw new Error(data.message || 'Gagal cetak barcode.');
            }
            alert(data.message);
        })
        .catch(error => alert(error.message || 'Terjadi kesalahan saat cetak barcode.'))
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalHtml;
        });
    }

    $('.print-barcode-btn').on('click', function() {
        const button = this;
        const id = $(this).data('id');
        const name = $(this).data('name');
        const barcode = $(this).data('barcode') || '';

        if (button.disabled || $(button).hasClass('print-disabled') || !barcode) {
            alert('Produk ini belum punya kode barcode.');
            return;
        }

        showConfirmation('Cetak barcode untuk "' + name + '"?', function() {
            doPrintBarcode(button, id, name);
        }, 'Ya, Cetak');
    });
</script>
@endsection
