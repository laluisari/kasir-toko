@extends('layouts.main')

@section('title', 'Kasir - Halaman Penjualan')
@section('subTitle', 'Manage Penjualan')

@section('content')
<div class="container-fluid pe-0 ps-0 pe-md-2 ps-md-2 pos-wrapper">
    <div class="row g-3 h-100">
        <!-- KOLOM KIRI: Pencarian & Hasil Search -->
        <div class="col-md-7 col-lg-8 pos-search-column">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex flex-column">
                    <!-- Input Search (Autofocus & Scanner Friendly) -->
                    <div class="position-relative search-input-wrapper">
                        <label for="productSearch" class="form-label fw-bold mb-1">🔍 Cari / Scan Barcode Produk</label>
                        <input 
                            type="text" 
                            id="productSearch" 
                            class="form-control form-control-lg bg-light border-2" 
                            placeholder="Ketik nama / scan barcode (Tekan F2)..."
                            autocomplete="off"
                            autofocus
                        >
                    </div>

                    <!-- Tempat Hasil Pencarian (Muncul saat diketik) -->
                    <div id="searchResults" class="d-none">
                        <h6 class="fw-bold mb-2" style="color: #2563EB;">Hasil Pencarian</h6>
                        <div class="list-group shadow-sm" id="productList" style="border: 1px solid #E2E8F0;\"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: Keranjang & Panel Pembayaran -->
        <div class="col-md-5 col-lg-4" style="display: flex; flex-direction: column;">
            <div class="card border-0 shadow-sm pos-cart-panel">
                <!-- Header Cart (Dibuat Kontras Tinggi dengan Text Explicit White) -->
                <div class="card-header text-white d-flex justify-content-between align-items-center py-1.5 px-3 border-0" style="background-color: #172033;">
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size: 1.1rem;">🧾</span>
                        <h6 class="mb-0 fw-bold text-white fs-6 lh-1" style="font-size: 0.95rem;">Keranjang Belanja</h6>
                        <span class="badge" id="cartBadge" style="background-color: #2563EB; font-size: 0.7rem; padding: 0.25rem 0.5rem;">{{ count($cart) }}</span>
                    </div>
                    <button class="btn btn-outline-light btn-sm py-1 px-1.5 fw-semibold d-flex align-items-center justify-content-center" onclick="clearCart()" title="Kosongkan Keranjang" style="font-size: 0.85rem; width: 1.75rem; height: 1.75rem;">
                        🗑️
                    </button>
                </div>

                <!-- Body Item (Compact Table Style with Fixed Height & Scroll) -->
                <div class="card-body p-0 pos-cart-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr style="font-size: 0.8rem;">
                                    <th>Produk</th>
                                    <th style="width: 60px;">Qty</th>
                                    <th style="width: 65px;">Diskon</th>
                                    <th class="text-end">Subtotal</th>
                                    <th style="width: 28px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                @if(empty($cart))
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Keranjang masih kosong</td>
                                </tr>
                                @else
                                    @foreach($cart as $item)
                                    <tr id="cart-{{ $item['product_id'] }}" style="font-size: 0.8rem;">
                                        <td style="padding: 0.35rem 0.5rem;">
                                            <div class="fw-bold text-truncate" style="max-width: 100px; font-size: 0.8rem;" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                            @if($item['auto_discount'] ?? 0)
                                            <small class="text-decoration-line-through" style="color: #94A3B8; font-size: 0.65rem;">Rp {{ number_format($item['selling_price'], 0, ',', '.') }}</small>
                                            <small class="text-muted" style="display: block; font-size: 0.65rem; color: #059669; font-weight: 600;">→ Rp {{ number_format($item['selling_price'] - ($item['auto_discount'] ?? 0), 0, ',', '.') }}</small>
                                            @else
                                            <small class="text-muted" style="font-size: 0.65rem;">@ Rp {{ number_format($item['selling_price'], 0, ',', '.') }}</small>
                                            @endif
                                        </td>
                                        <td style="padding: 0.25rem 0.25rem;">
                                            <input 
                                                type="number" 
                                                class="form-control form-control-sm px-1 text-center quantity-input" 
                                                value="{{ $item['quantity'] }}" 
                                                min="1"
                                                data-product-id="{{ $item['product_id'] }}"
                                                onchange="updateCartItem(this)"
                                                style="font-size: 0.75rem; padding: 0.2rem 0.25rem; height: 28px;"
                                            >
                                        </td>
                                        <td style="padding: 0.25rem 0.25rem;">
                                            <input 
                                                type="number" 
                                                class="form-control form-control-sm px-1 text-center discount-input" 
                                                value="{{ $item['manual_discount'] ?? 0 }}" 
                                                min="0"
                                                placeholder="+"
                                                data-product-id="{{ $item['product_id'] }}"
                                                onchange="updateCartItem(this)"
                                                style="font-size: 0.75rem; padding: 0.2rem 0.25rem; height: 28px;"
                                            >
                                        </td>
                                        <td class="text-end fw-bold" style="padding: 0.35rem 0.5rem; font-size: 0.8rem;">
                                            Rp {{ number_format(($item['selling_price'] - $item['discount']) * $item['quantity'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-center" style="padding: 0.25rem 0.1rem;">
                                            <button class="btn btn-link p-0" onclick="removeFromCart({{ $item['product_id'] }})" style="color: #E5484D; text-decoration: none; font-weight: bold; font-size: 1.1rem; line-height: 1;">&times;</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Summary (Selalu Terkunci di Bawah) -->
                <div class="card-footer border-top p-2" style="background-color: #FFFFFF;">
                    @php
                    $subtotal_gross = 0;  // Sebelum diskon (KOTOR)
                    $discount_total = 0;
                    $total_qty = 0;
                    foreach($cart as $item) {
                        $auto_discount = $item['auto_discount'] ?? 0;
                        $manual_discount = $item['manual_discount'] ?? 0;
                        $total_discount = $auto_discount + $manual_discount;
                        
                        $subtotal_gross += $item['selling_price'] * $item['quantity'];  // KOTOR: harga asli
                        $discount_total += $total_discount * $item['quantity'];
                        $total_qty += $item['quantity'];
                    }
                    $total = $subtotal_gross - $discount_total;  // FINAL: Gross - Diskon
                    @endphp

                    <div class="d-flex justify-content-between" style="font-size: 0.7rem; color: #64748B; margin-bottom: 0.25rem; border-top: 1px solid #E2E8F0; padding-top: 0.25rem;">
                        <span>Subtotal (Kotor):</span>
                        <span id="subtotalDisplay">Rp {{ number_format($subtotal_gross, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size: 0.7rem; color: #64748B; margin-bottom: 0.25rem;">
                        <span style="color: #1E293B; font-weight: 500;">Total Diskon:</span>
                        <span id="discountDisplay" style="color: #E5484D;">-Rp {{ number_format($discount_total, 0, ',', '.') }}</span>
                    </div>
                                        
                    <div class="d-flex justify-content-between" style="font-size: 0.7rem; color: #64748B; margin-bottom: 0.25rem;">
                        <span style="color: #1E293B; font-weight: 500;">Total Qty:</span>
                        <span id="totalQtyDisplay">{{ $total_qty }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-1.5 rounded" style="background-color: #EFF6FF; border: 1.5px solid #2563EB; margin-bottom: 0.25rem;">
                        <span class="fw-bold" style="color: #1E293B; font-size: 0.8rem;">💰 TOTAL:</span>
                        <span class="fw-bold" id="totalDisplay" style="font-size: 1.1rem; color: #2563EB;">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="row g-1 mb-1.5" style="margin-top: 1rem;">
                        <div class="col-6">
                            <label for="paymentMethod" class="form-label mb-0" style="font-size: 0.65rem; font-weight: bold; display: block;">Bayar</label>
                            <select class="form-select" id="paymentMethod" style="font-size: 0.7rem; padding: 0.2rem 0.35rem; height: 28px;">
                                <option value="cash" selected>💵 Tunai</option>
                                <option value="qris">📱 QRIS</option>
                                <option value="transfer">🏦 Transfer</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="paidAmount" class="form-label mb-0" style="font-size: 0.65rem; font-weight: bold; display: block;">Terima</label>
                            <input 
                                type="number" 
                                id="paidAmount" 
                                class="form-control text-end fw-bold" 
                                placeholder="0"
                                oninput="calculateChange()"
                                style="font-size: 0.7rem; padding: 0.2rem 0.35rem; height: 28px;"
                            >
                        </div>
                    </div>

                    <!-- Quick Cash Buttons -->
                    <div class="d-flex gap-0.5 mb-2" style="font-size: 0.65rem; margin-top: 1rem;">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1 py-0" onclick="setQuickCash('exact')" style="padding: 0.2rem 0.3rem !important; height: 28px; line-height: 1.4; margin: 0.15rem;">💵 Pas</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1 py-0" onclick="setQuickCash(50000)" style="padding: 0.2rem 0.3rem !important; height: 28px; line-height: 1.4; margin: 0.15rem;">💵 50rb</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1 py-0" onclick="setQuickCash(100000)" style="padding: 0.2rem 0.3rem !important; height: 28px; line-height: 1.4; margin: 0.15rem;">💵 100rb</button>
                    </div>

                    <!-- Status Kembalian -->
                    <div class="d-flex justify-content-between align-items-center mb-2 p-1.5 rounded" style="font-size: 0.7rem; background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                        <span class="fw-bold" style="color: #1E293B;">Kembali:</span>
                        <span class="fw-bold" id="changeDisplay" style="color: #059669;">Rp 0</span>
                    </div>

                    <!-- Action Button -->
                    <button 
                        class="w-100 fw-bold shadow-sm" 
                        id="checkoutBtn"
                        onclick="processCheckout()"
                        style="font-size: 0.8rem; padding: 0.4rem 0.5rem; background-color: #059669; color: white; border: none; border-radius: 0.375rem; transition: background-color 0.15s ease; position: relative; height: 32px;"
                        onmouseover="this.style.backgroundColor='#047857'" 
                        onmouseout="this.style.backgroundColor='#059669'"
                        @if(empty($cart)) disabled @endif
                    >
                        ✓ Bayar
                        <kbd style="position: absolute; right: 0.3rem; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.2); padding: 0.1rem 0.25rem; border-radius: 0.2rem; font-size: 0.6rem; font-family: monospace;">Enter</kbd>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ========== FULL SCREEN LAYOUT - 100% VIEWPORT FIT ========== */
    
    /* Container kasir utama - relative ke dashboard-main-body */
    .container-fluid.pos-wrapper {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Hanya row utama. Jangan gunakan .pos-wrapper .row karena akan
       membuat row pembayaran ikut memiliki height: 100%. */
    .pos-wrapper > .row {
        flex: 1 1 auto;
        height: 100%;
        min-height: 0;
    }

    /* Kolom kanan harus dapat menyusut di dalam flex container */
    .pos-wrapper > .row > .col-md-5 {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    /* Kolom Kiri: Search & Hasil */
    .pos-search-column {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .pos-search-column .card {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    .pos-search-column .card-body {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }

    /* Search input area (tetap atas) */
    .search-input-wrapper {
        flex-shrink: 0;
        margin-bottom: 1rem;
    }

    /* Hasil pencarian area (fleksibel, scroll lokal) */
    #searchResults {
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden;
    }

    #productList {
        max-height: none !important;
        height: auto;
    }

    /* Container Panel Keranjang Kanan */
.pos-cart-panel {
    display: flex;
    flex-direction: column;
    height: calc(100dvh - 260px);
    min-height: 560px;
    max-height: 750px;
    overflow: hidden;
}

    .pos-cart-panel .card-header {
        flex-shrink: 0;
        border: none !important;
    }

.pos-cart-body {
    flex: 1 1 auto;
    min-height: 100px;
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
    background-color: #FFFFFF;
    padding-bottom: 0.5rem;
}

.pos-cart-body .table {
    margin-bottom: 0;
}

.pos-cart-body .table thead {
    background-color: #F8FAFC;
    border-bottom: 1px solid #E2E8F0;
}

.pos-cart-body .table thead th {
    color: #64748B;
    font-weight: 600;
    border-bottom: 1px solid #E2E8F0;
}

.pos-cart-body .table tbody tr {
    cursor: pointer;
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #F1F5F9;
}

.pos-cart-body .table tbody tr:hover {
    background-color: #F8FAFC;
}


.pos-cart-panel .card-footer {
    flex: 0 0 auto;
    background-color: #FFFFFF;
    border-top: 1px solid #E2E8F0;
}

    /* Row form pembayaran harus mengikuti tinggi kontennya sendiri */
    .pos-cart-panel .card-footer .row {
        height: auto;
        min-height: auto;
        flex: initial;
    }

    .product-card {
        transition: all 0.2s ease-in-out;
        border-color: #dee2e6 !important;
    }

    .product-card:hover {
        border-color: #0d6efd !important;
        transform: translateY(-2px);
    }

    /* Hilangkan panah spinner di input number agar rapi */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
</style>

<script>
    // Autofocus kembali ke kolom search setelah tindakan
    function focusSearch() {
        const searchInput = document.getElementById('productSearch');
        searchInput.focus();
        searchInput.select();
    }

    // Direct add product tanpa prompt browser
    function addProductToCart(productId) {
        fetch('{{ route("sales.add-to-cart") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1 // Default langsung 1, ubah via tabel jika ingin tambah
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCartDisplay(data.cart);
                focusSearch();
            }
        })
        .catch(err => console.error(err));
    }

    // Render ulang isi tabel keranjang belanja
    function updateCartDisplay(cart) {
        const cartItemsBody = document.getElementById('cartItems');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const cartBadge = document.getElementById('cartBadge');

        if (!cart || Object.keys(cart).length === 0) {
            cartItemsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Keranjang masih kosong</td></tr>';
            checkoutBtn.disabled = true;
            cartBadge.textContent = '0';
            updateCartSummary({});
            return;
        }

        checkoutBtn.disabled = false;
        let html = '';

        for (const [productId, item] of Object.entries(cart)) {
            const autoDiscount = item.auto_discount || 0;
            const manualDiscount = item.manual_discount || 0;
            const totalDiscount = autoDiscount + manualDiscount;
            const subtotal = (item.selling_price - totalDiscount) * item.quantity;
            
            let priceDisplayHtml = '';
            if (autoDiscount > 0) {
                priceDisplayHtml = `
                    <small class="text-decoration-line-through" style="color: #94A3B8; font-size: 0.75rem;">Rp ${formatCurrency(item.selling_price)}</small>
                    <small class="text-muted" style="display: block; font-size: 0.75rem; color: #059669; font-weight: 600;">→ Rp ${formatCurrency(item.selling_price - autoDiscount)}</small>
                `;
            } else {
                priceDisplayHtml = `<small class="text-muted">@ Rp ${formatCurrency(item.selling_price)}</small>`;
            }
            
            html += `
                <tr id="cart-${productId}">
                    <td>
                        <div class="fw-bold text-truncate" style="max-width: 110px;" title="${item.name}">${item.name}</div>
                        ${priceDisplayHtml}
                    </td>
                    <td>
                        <input 
                            type="number" 
                            class="form-control form-control-sm px-1 text-center quantity-input" 
                            value="${item.quantity}" 
                            min="1"
                            data-product-id="${productId}"
                            onchange="updateCartItem(this)"
                        >
                    </td>
                    <td>
                        <input 
                            type="number" 
                            class="form-control form-control-sm px-1 text-center discount-input" 
                            value="${manualDiscount}" 
                            min="0"
                            placeholder="Tambah"
                            data-product-id="${productId}"
                            onchange="updateCartItem(this)"
                            style="font-size: 0.75rem;"
                        >
                    </td>
                    <td class="text-end fw-bold">
                        Rp ${formatCurrency(subtotal)}
                    </td>
                    <td class="text-center">
                        <button class=\"btn btn-link p-0\" onclick=\"removeFromCart(${productId})\" style=\"color: #E5484D; text-decoration: none; font-weight: bold; font-size: 1.3rem;\">&times;</button>
                    </td>
                </tr>
            `;
        }

        cartItemsBody.innerHTML = html;
        cartBadge.textContent = Object.keys(cart).length;
        updateCartSummary(cart);
    }

    function updateCartItem(input) {
        const productId = input.dataset.productId;
        const isQuantity = input.classList.contains('quantity-input');

        const data = { product_id: parseInt(productId) };
        if (isQuantity) {
            data.quantity = parseInt(input.value) || 1;
        } else {
            data.discount = parseInt(input.value) || 0;
        }

        fetch('{{ route("sales.update-cart") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) updateCartDisplay(data.cart);
        });
    }

    function removeFromCart(productId) {
        fetch('{{ route("sales.remove-from-cart") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) updateCartDisplay(data.cart);
        });
    }

    function updateCartSummary(cart) {
        let subtotalGross = 0;  // Sebelum diskon (KOTOR)
        let discountTotal = 0;
        let totalQty = 0;

        for (const [id, item] of Object.entries(cart)) {
            const totalDiscount = (item.auto_discount || 0) + (item.manual_discount || 0);
            subtotalGross += item.selling_price * item.quantity;  // KOTOR: harga asli
            discountTotal += totalDiscount * item.quantity;
            totalQty += item.quantity;
        }

        const total = subtotalGross - discountTotal;  // FINAL: Gross - Diskon

        document.getElementById('totalQtyDisplay').textContent = totalQty;
        document.getElementById('subtotalDisplay').textContent = 'Rp ' + formatCurrency(subtotalGross);
        document.getElementById('discountDisplay').textContent = '-Rp ' + formatCurrency(discountTotal);
        document.getElementById('totalDisplay').textContent = 'Rp ' + formatCurrency(total);

        calculateChange();
    }

    function calculateChange() {
        const paidAmount = parseInt(document.getElementById('paidAmount').value) || 0;
        const totalText = document.getElementById('totalDisplay').textContent.replace(/[^0-9]/g, '');
        const total = parseInt(totalText) || 0;
        
        const change = paidAmount - total;
        const changeDisplay = document.getElementById('changeDisplay');

        if (change >= 0) {
            changeDisplay.textContent = 'Rp ' + formatCurrency(change);
            changeDisplay.style.color = '#059669'; // Emerald
        } else {
            changeDisplay.textContent = 'Kurang Rp ' + formatCurrency(Math.abs(change));
            changeDisplay.style.color = '#DC3545'; // Soft red
        }
    }

    function processCheckout() {
        const paymentMethod = document.getElementById('paymentMethod').value;
        const paidAmount = parseInt(document.getElementById('paidAmount').value) || 0;
        const total = parseInt(document.getElementById('totalDisplay').textContent.replace(/[^0-9]/g, '')) || 0;

        if (paidAmount < total && paymentMethod === 'cash') {
            alert('Uang yang diterima kurang dari total belanja!');
            return;
        }

        fetch('{{ route("sales.checkout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                payment_method: paymentMethod,
                paid_amount: paidAmount
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = `/admin/sales/${data.sale_document_id}`;
            } else {
                alert(data.message || 'Transaksi gagal');
            }
        });
    }

    function clearCart() {
        if (!confirm('Kosongkan keranjang?')) return;
        updateCartDisplay({});
    }

    function setQuickCash(amount) {
        const totalText = document.getElementById('totalDisplay').textContent.replace(/[^0-9]/g, '');
        const total = parseInt(totalText) || 0;
        
        if (amount === 'exact') {
            document.getElementById('paidAmount').value = total;
        } else {
            document.getElementById('paidAmount').value = amount;
        }
        calculateChange();
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(amount);
    }

    // Live Search Event
    document.getElementById('productSearch').addEventListener('input', function(e) {
        const query = this.value.trim();
        const searchResults = document.getElementById('searchResults');

        if (query.length < 2) {
            searchResults.classList.add('d-none');
            return;
        }

        fetch(`{{ route('sales.search') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(products => {
                let html = '';
                if (products.length === 0) {
                    html = '<div class="p-3 text-center text-muted bg-white border">Produk tidak ditemukan</div>';
                } else {
                    products.forEach(product => {
                        html += `
                            <button type="button" class="list-group-item list-group-item-action p-2" onclick="addProductToCart(${product.id})">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">${product.name}</div>
                                        <small class="text-muted">${product.category ? product.category.name : 'Umum'}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-primary">Rp ${formatCurrency(product.selling_price)}</strong>
                                        <div><small class="text-muted">Stok: ${product.stock}</small></div>
                                    </div>
                                </div>
                            </button>
                        `;
                    });
                }
                document.getElementById('productList').innerHTML = html;
                searchResults.classList.remove('d-none');
            });
    });

    // Keyboard Shortcuts (Ergonomi Kasir)
    document.addEventListener('keydown', function(e) {
        // Tekan F2 untuk fokus ke pencarian
        if (e.key === 'F2') {
            e.preventDefault();
            focusSearch();
        }
        // Tekan Enter untuk checkout
        if (e.key === 'Enter' && document.activeElement.id === 'paidAmount') {
            e.preventDefault();
            const checkoutBtn = document.getElementById('checkoutBtn');
            if (!checkoutBtn.disabled) {
                processCheckout();
            }
        }
    });
</script>
@endsection
