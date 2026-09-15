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

                    <div id="editingLockedHint" class="alert alert-warning py-2 px-3 mb-2 d-none" style="font-size: 0.8rem;">
                        Tahap pembayaran aktif. Kembali ke keranjang untuk menambah atau mengubah item.
                    </div>

                    <!-- Tempat Hasil Pencarian (Muncul saat diketik) -->
                    <div id="searchResults" class="d-none">
                        <h6 class="fw-bold mb-2" style="color: #2563EB;">Hasil Pencarian</h6>
                        <div class="list-group shadow-sm" id="productList" style="border: 1px solid #E2E8F0;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: Keranjang & Panel Pembayaran -->
        <div class="col-md-5 col-lg-4" style="display: flex; flex-direction: column;">
            <div class="card border-0 shadow-sm pos-cart-panel">
                <div class="card-header text-white d-flex justify-content-between align-items-center py-1.5 px-3 border-0" style="background-color: #172033;">
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size: 1.1rem;">🧾</span>
                        <h6 class="mb-0 fw-bold text-white fs-6 lh-1" id="panelStageTitle" style="font-size: 0.95rem;">Keranjang Belanja</h6>
                        <span class="badge" id="cartBadge" style="background-color: #2563EB; font-size: 0.7rem; padding: 0.25rem 0.5rem;">{{ count($cart) }}</span>
                    </div>
                    <button class="btn btn-outline-light btn-sm py-1 px-1.5 fw-semibold d-flex align-items-center justify-content-center" id="clearCartBtn" onclick="clearCart()" title="Kosongkan Keranjang" style="font-size: 0.85rem; width: 1.75rem; height: 1.75rem;">
                        🗑️
                    </button>
                </div>

                <div class="card-body p-0 d-flex flex-column">
                    <div id="stage-cart" class="stage-panel d-flex flex-column h-100">
                        <div class="stage-body pos-cart-body">
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
                                                    <button class="btn btn-link p-0 remove-item-btn" onclick="removeFromCart({{ $item['product_id'] }})" style="color: #E5484D; text-decoration: none; font-weight: bold; font-size: 1.1rem; line-height: 1;">&times;</button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="stage-footer card-footer border-top p-2" style="background-color: #FFFFFF;">
                            @php
                            $subtotal_gross = 0;
                            $discount_total = 0;
                            $total_qty = 0;
                            foreach($cart as $item) {
                                $auto_discount = $item['auto_discount'] ?? 0;
                                $manual_discount = $item['manual_discount'] ?? 0;
                                $total_discount = $auto_discount + $manual_discount;
                                $subtotal_gross += $item['selling_price'] * $item['quantity'];
                                $discount_total += $total_discount * $item['quantity'];
                                $total_qty += $item['quantity'];
                            }
                            $total = $subtotal_gross - $discount_total;
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
                            <div class="d-flex justify-content-between align-items-center p-1.5 rounded" style="background-color: #EFF6FF; border: 1.5px solid #2563EB; margin-bottom: 0.5rem;">
                                <span class="fw-bold" style="color: #1E293B; font-size: 0.8rem;">💰 TOTAL:</span>
                                <span class="fw-bold" id="totalDisplay" style="font-size: 1.1rem; color: #2563EB;">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            <button
                                class="w-100 fw-bold shadow-sm"
                                id="proceedPaymentBtn"
                                onclick="goToPaymentStage()"
                                style="font-size: 0.8rem; padding: 0.45rem 0.5rem; background-color: #2563EB; color: white; border: none; border-radius: 0.375rem; transition: background-color 0.15s ease;"
                                onmouseover="this.style.backgroundColor='#1D4ED8'"
                                onmouseout="this.style.backgroundColor='#2563EB'"
                                @if(empty($cart)) disabled @endif
                            >
                                Lanjut Pembayaran
                            </button>
                        </div>
                    </div>

                    <div id="stage-payment" class="stage-panel d-none d-flex flex-column h-100">
                        <div class="stage-body payment-stage-body p-3">
                            <button type="button" class="btn btn-link btn-sm p-0 mb-3 text-decoration-none" onclick="backToCartStage()">← Kembali ke keranjang</button>

                            <div class="rounded p-2 mb-3" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                                <div class="d-flex justify-content-between" style="font-size: 0.8rem;">
                                    <span>Total Item:</span>
                                    <strong id="paymentSummaryQty">0</strong>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size: 0.95rem;">
                                    <span>Total Bayar:</span>
                                    <strong id="paymentSummaryTotal" style="color:#2563EB;">Rp 0</strong>
                                </div>
                            </div>

                            <input type="hidden" id="buyerId" value="">
                            <div class="mb-3 position-relative">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label for="buyerSearchInput" class="form-label fw-bold mb-0">Pelanggan <span class="text-muted fw-normal">- opsional</span></label>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="toggleNewBuyerForm()">+ Pelanggan baru</button>
                                </div>
                                <input type="text" id="buyerSearchInput" class="form-control form-control-sm" placeholder="Pelanggan umum (lewati atau cari)">
                                <small id="buyerSelectedHint" class="text-muted d-block mt-1">Bayar penuh boleh tanpa profil — lewati = Pelanggan Umum.</small>
                                <div id="buyerSearchResults" class="list-group d-none position-absolute w-100 shadow-sm" style="z-index: 20; max-height: 220px; overflow-y: auto;"></div>

                                <div id="newBuyerForm" class="mt-2 p-2 rounded border d-none" style="background-color: #F8FAFC;">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <input type="text" id="newBuyerName" class="form-control form-control-sm" placeholder="Nama pelanggan">
                                        </div>
                                        <div class="col-12">
                                            <input type="text" id="newBuyerPhone" class="form-control form-control-sm" placeholder="No. HP (opsional)">
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="saveNewBuyer()">Simpan Pelanggan</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleNewBuyerForm(false)">Batal</button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold mb-2">Jenis Pembayaran</label>
                                <input type="hidden" id="paymentType" value="full">
                                <div class="d-flex gap-2">
                                    <button type="button" id="paymentTypeBtnFull" class="btn btn-sm btn-primary grow" onclick="setPaymentType('full')">Bayar penuh</button>
                                    <button type="button" id="paymentTypeBtnDebt" class="btn btn-sm btn-outline-secondary grow" onclick="setPaymentType('debt')">Hutang</button>
                                </div>
                            </div>

                            <div id="fullPaymentSection">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label for="paymentMethod" class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Metode</label>
                                        <select class="form-select form-select-sm" id="paymentMethod">
                                            <option value="cash" selected>💵 Tunai</option>
                                            <option value="qris">📱 QRIS</option>
                                            <option value="transfer">🏦 Transfer</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label for="paidAmount" class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Uang Diterima</label>
                                        <input
                                            type="number"
                                            id="paidAmount"
                                            class="form-control form-control-sm text-end fw-bold"
                                            placeholder="0"
                                            oninput="calculateChange()"
                                        >
                                    </div>
                                </div>

                                <div class="quick-cash-options d-flex flex-wrap gap-2" style="font-size: 0.7rem;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary grow" onclick="setQuickCash('exact')">💵 Pas</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary grow" onclick="setQuickCash(50000)">💵 50rb</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary grow" onclick="setQuickCash(100000)">💵 100rb</button>
                                </div>

                                <div class="payment-result-card change-result d-flex justify-content-between align-items-center rounded" style="font-size: 0.8rem; background-color: #F8FAFC; border: 1px solid #E2E8F0;">
                                    <span class="fw-bold" style="color: #1E293B;">Kembalian:</span>
                                    <span class="fw-bold" id="changeDisplay" style="color: #059669;">Rp 0</span>
                                </div>
                            </div>

                            <div id="debtPaymentSection" class="d-none">
                                <div class="mb-2">
                                    <label for="debtDueDate" class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Jatuh Tempo *</label>
                                    <input type="date" id="debtDueDate" class="form-control form-control-sm">
                                </div>
                                <div class="mb-2">
                                    <label for="debtDownPayment" class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Pembayaran Awal</label>
                                    <input type="number" id="debtDownPayment" class="form-control form-control-sm" value="0" min="0" oninput="handleDebtDownPaymentInput()">
                                </div>

                                <div id="debtMethodWrapper" class="mb-2 d-none">
                                    <label for="debtPaymentMethod" class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Metode Pembayaran Awal</label>
                                    <select class="form-select form-select-sm" id="debtPaymentMethod">
                                        <option value="cash" selected>💵 Tunai</option>
                                        <option value="qris">📱 QRIS</option>
                                        <option value="transfer">🏦 Transfer</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="debtNote" class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Catatan</label>
                                    <textarea id="debtNote" class="form-control form-control-sm" rows="2" placeholder="Catatan opsional"></textarea>
                                </div>

                                <div class="payment-result-card debt-result d-flex justify-content-between align-items-center rounded" style="font-size: 0.8rem; background-color: #FFF7ED; border: 1px solid #FDBA74;">
                                    <span class="fw-bold" style="color: #9A3412;">Sisa Hutang:</span>
                                    <span class="fw-bold" id="debtRemainingDisplay" style="color: #C2410C;">Rp 0</span>
                                </div>
                            </div>

                            <div id="checkoutError" class="alert alert-danger py-2 px-3 mt-3 d-none" style="font-size: 0.8rem;"></div>
                        </div>

                        <div class="stage-footer card-footer border-top p-2" style="background-color: #FFFFFF; border-radius: 0 0 0.375rem 0.375rem;">
                            <button
                                class="w-100 fw-bold shadow-sm"
                                id="confirmCheckoutBtn"
                                onclick="processCheckout()"
                                style="font-size: 0.85rem; padding: 0.5rem; background-color: #059669; color: white; border: none; border-radius: 0.375rem;"
                                @if(empty($cart)) disabled @endif
                            >
                                Simpan Pembayaran
                            </button>
                        </div>
                    </div>

                    <div id="stage-success" class="stage-panel d-none d-flex flex-column h-100">
                        <div class="stage-body p-3 overflow-auto">
                            <div class="text-center mb-3">
                                <div style="font-size: 2rem;">✅</div>
                                <h6 class="fw-bold mb-1">Transaksi Berhasil</h6>
                                <p class="text-muted mb-0" id="successInvoice">-</p>
                            </div>

                            <div class="rounded p-3" style="background:#F8FAFC; border:1px solid #E2E8F0;">
                                <div class="d-flex justify-content-between mb-1"><span>Total:</span><strong id="successTotal">Rp 0</strong></div>
                                <div class="d-flex justify-content-between mb-1 d-none" id="successBuyerRow"><span>Pelanggan:</span><strong id="successBuyer">Pelanggan umum</strong></div>
                                <div class="d-flex justify-content-between mb-1"><span id="successMetaLabel">Kembalian:</span><strong id="successMetaValue">Rp 0</strong></div>
                                <div class="d-flex justify-content-between"><span>Status:</span><strong id="successStatus">Lunas</strong></div>
                            </div>
                        </div>

                        <div class="stage-footer card-footer border-top p-2" style="background-color: #FFFFFF;">
                            <div class="d-flex gap-2">
                                <a href="#" id="successReceiptLink" class="btn btn-outline-primary btn-sm w-50" target="_blank">Lihat Struk</a>
                                <button type="button" class="btn btn-success btn-sm w-50" onclick="startNewTransaction()">Transaksi Baru</button>
                            </div>
                        </div>
                    </div>
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
        overflow: visible;
    }

    /* Hanya row utama. Jangan gunakan .pos-wrapper .row karena akan
       membuat row pembayaran ikut memiliki height: 100%. */
    .pos-wrapper > .row {
        flex: 1 1 auto;
        height: auto;
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
    height: calc(100dvh - 180px);
    min-height: 0;
    overflow: hidden;
}

    .pos-cart-panel .card-header {
        flex-shrink: 0;
        border: none !important;
        position: sticky;
        top: 0;
        z-index: 7;
    }

.pos-cart-body {
    flex: 1 1 auto;
    min-height: 0;
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


.pos-cart-panel .card-body {
    min-height: 0;
    overflow: hidden;
}

.pos-cart-panel .card-footer {
    flex: 0 0 auto;
    background-color: #FFFFFF;
    border-top: 1px solid #E2E8F0;
}

.stage-panel {
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
}

.stage-body {
    flex: 1 1 auto;
    min-height: 0;
}

/* Form pembayaran adalah satu-satunya area yang bergulir. Footer tetap
   mengambil ruang normal sehingga tidak menutupi konten terakhir. */
.payment-stage-body {
    overflow-y: auto;
    overflow-x: hidden;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
    padding-bottom: 2rem !important;
    -webkit-overflow-scrolling: touch;
}

.quick-cash-options {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}

.quick-cash-options .btn {
    flex: 1 1 5rem;
    min-height: 2.25rem;
}

.payment-result-card {
    min-height: 3rem;
    padding: 0.5rem 0.75rem;
    gap: 0.5rem;
    font-size: 0.75rem !important;
}

.payment-result-card > span:last-child {
    min-width: 0;
    text-align: right;
    overflow-wrap: anywhere;
}

.debt-result {
    margin-bottom: 0.5rem;
}

.stage-footer {
    flex: 0 0 auto;
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

    @media (max-width: 767.98px) {
        .container-fluid.pos-wrapper {
            overflow: auto;
        }

        .pos-wrapper > .row {
            height: auto;
        }

        .pos-cart-panel {
            height: auto;
            min-height: 0;
            max-height: none;
        }

        .stage-panel {
            overflow: visible;
        }

        .payment-stage-body {
            overflow: visible;
        }
    }

    @media (max-width: 420px) {
        .payment-result-card {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 0.25rem;
        }

        .payment-result-card > span:last-child {
            width: 100%;
            text-align: left;
        }
    }
</style>

<script>
    let currentStage = 'cart';
    let isSubmittingCheckout = false;
    let buyerSearchTimeout = null;

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(amount || 0);
    }

    function focusSearch() {
        const searchInput = document.getElementById('productSearch');
        if (!searchInput.disabled) {
            searchInput.focus();
            searchInput.select();
        }
    }

    function getCurrentTotal() {
        const totalText = document.getElementById('totalDisplay').textContent.replace(/[^0-9]/g, '');
        return parseInt(totalText) || 0;
    }

    function getCurrentQty() {
        return parseInt(document.getElementById('totalQtyDisplay').textContent) || 0;
    }

    function setStage(stage) {
        currentStage = stage;
        document.getElementById('stage-cart').classList.toggle('d-none', stage !== 'cart');
        document.getElementById('stage-payment').classList.toggle('d-none', stage !== 'payment');
        document.getElementById('stage-success').classList.toggle('d-none', stage !== 'success');
        updatePanelHeader(stage);

        const lockEditing = stage === 'payment';
        document.getElementById('productSearch').disabled = lockEditing;
        document.getElementById('editingLockedHint').classList.toggle('d-none', !lockEditing);
        if (lockEditing) {
            document.getElementById('searchResults').classList.add('d-none');
        }

        if (stage === 'payment') {
            updatePaymentSummary();
            calculateChange();
            handleDebtDownPaymentInput();
        }
    }

    function updatePanelHeader(stage) {
        const title = document.getElementById('panelStageTitle');
        const clearBtn = document.getElementById('clearCartBtn');
        const badge = document.getElementById('cartBadge');

        if (stage === 'payment') {
            title.textContent = 'Pembayaran';
            clearBtn.classList.add('d-none');
            badge.classList.add('d-none');
            return;
        }

        if (stage === 'success') {
            title.textContent = 'Transaksi Berhasil';
            clearBtn.classList.add('d-none');
            badge.classList.add('d-none');
            return;
        }

        title.textContent = 'Keranjang Belanja';
        clearBtn.classList.remove('d-none');
        badge.classList.remove('d-none');
    }

    function showCheckoutError(message) {
        const el = document.getElementById('checkoutError');
        el.textContent = message;
        el.classList.remove('d-none');
    }

    function clearCheckoutError() {
        const el = document.getElementById('checkoutError');
        el.textContent = '';
        el.classList.add('d-none');
    }

    function addProductToCart(productId) {
        if (currentStage !== 'cart') {
            alert('Sedang di tahap pembayaran. Kembali ke keranjang untuk menambah item.');
            return;
        }

        fetch('{{ route("sales.add-to-cart") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCartDisplay(data.cart);
                focusSearch();
            } else if (data.message) {
                alert(data.message);
            }
        })
        .catch(err => console.error(err));
    }

    function updateCartDisplay(cart) {
        const cartItemsBody = document.getElementById('cartItems');
        const proceedPaymentBtn = document.getElementById('proceedPaymentBtn');
        const confirmCheckoutBtn = document.getElementById('confirmCheckoutBtn');
        const cartBadge = document.getElementById('cartBadge');

        if (!cart || Object.keys(cart).length === 0) {
            cartItemsBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Keranjang masih kosong</td></tr>';
            proceedPaymentBtn.disabled = true;
            confirmCheckoutBtn.disabled = true;
            cartBadge.textContent = '0';
            updateCartSummary({});
            if (currentStage === 'payment') {
                backToCartStage();
            }
            return;
        }

        proceedPaymentBtn.disabled = false;
        confirmCheckoutBtn.disabled = false;
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
                    <td class="text-end fw-bold">Rp ${formatCurrency(subtotal)}</td>
                    <td class="text-center">
                        <button class="btn btn-link p-0 remove-item-btn" onclick="removeFromCart(${productId})" style="color: #E5484D; text-decoration: none; font-weight: bold; font-size: 1.3rem;">&times;</button>
                    </td>
                </tr>
            `;
        }

        cartItemsBody.innerHTML = html;
        cartBadge.textContent = Object.keys(cart).length;
        updateCartSummary(cart);
    }

    function updateCartItem(input) {
        if (currentStage !== 'cart') {
            alert('Sedang di tahap pembayaran. Kembali ke keranjang untuk mengubah item.');
            return;
        }

        const productId = input.dataset.productId;
        const isQuantity = input.classList.contains('quantity-input');
        const prevValue = input.value;
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
            if (data.success) {
                updateCartDisplay(data.cart);
            } else if (data.message) {
                input.value = prevValue;
                alert(data.message);
            }
        });
    }

    function removeFromCart(productId) {
        if (currentStage !== 'cart') {
            alert('Sedang di tahap pembayaran. Kembali ke keranjang untuk mengubah item.');
            return;
        }

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
            if (data.success) {
                updateCartDisplay(data.cart);
            }
        });
    }

    function updateCartSummary(cart) {
        let subtotalGross = 0;
        let discountTotal = 0;
        let totalQty = 0;

        for (const item of Object.values(cart)) {
            const totalDiscount = (item.auto_discount || 0) + (item.manual_discount || 0);
            subtotalGross += item.selling_price * item.quantity;
            discountTotal += totalDiscount * item.quantity;
            totalQty += item.quantity;
        }

        const total = subtotalGross - discountTotal;

        document.getElementById('totalQtyDisplay').textContent = totalQty;
        document.getElementById('subtotalDisplay').textContent = 'Rp ' + formatCurrency(subtotalGross);
        document.getElementById('discountDisplay').textContent = '-Rp ' + formatCurrency(discountTotal);
        document.getElementById('totalDisplay').textContent = 'Rp ' + formatCurrency(total);

        updatePaymentSummary();
        calculateChange();
        handleDebtDownPaymentInput();
    }

    function updatePaymentSummary() {
        document.getElementById('paymentSummaryQty').textContent = getCurrentQty();
        document.getElementById('paymentSummaryTotal').textContent = 'Rp ' + formatCurrency(getCurrentTotal());
    }

    function calculateChange() {
        const paidAmount = parseInt(document.getElementById('paidAmount').value) || 0;
        const total = getCurrentTotal();
        const change = paidAmount - total;
        const changeDisplay = document.getElementById('changeDisplay');

        if (change >= 0) {
            changeDisplay.textContent = 'Rp ' + formatCurrency(change);
            changeDisplay.style.color = '#059669';
        } else {
            changeDisplay.textContent = 'Kurang Rp ' + formatCurrency(Math.abs(change));
            changeDisplay.style.color = '#DC3545';
        }
    }

    function handleDebtDownPaymentInput() {
        const total = getCurrentTotal();
        const downPayment = parseInt(document.getElementById('debtDownPayment').value) || 0;
        const debtMethodWrapper = document.getElementById('debtMethodWrapper');
        debtMethodWrapper.classList.toggle('d-none', downPayment <= 0);

        const remaining = Math.max(total - downPayment, 0);
        document.getElementById('debtRemainingDisplay').textContent = 'Rp ' + formatCurrency(remaining);
    }

    function goToPaymentStage() {
        if (getCurrentTotal() <= 0) {
            alert('Keranjang masih kosong.');
            return;
        }
        setStage('payment');
        clearCheckoutError();
    }

    function backToCartStage() {
        setStage('cart');
        clearCheckoutError();
        focusSearch();
    }

    function setQuickCash(amount) {
        const total = getCurrentTotal();
        document.getElementById('paidAmount').value = amount === 'exact' ? total : amount;
        calculateChange();
    }

    function getSelectedPaymentType() {
        return document.getElementById('paymentType').value || 'full';
    }

    function setPaymentType(type) {
        document.getElementById('paymentType').value = type;
        syncPaymentTypeView();
        clearCheckoutError();
    }

    function syncPaymentTypeView() {
        const type = getSelectedPaymentType();
        document.getElementById('fullPaymentSection').classList.toggle('d-none', type !== 'full');
        document.getElementById('debtPaymentSection').classList.toggle('d-none', type !== 'debt');

        document.getElementById('paymentTypeBtnFull').classList.toggle('btn-primary', type === 'full');
        document.getElementById('paymentTypeBtnFull').classList.toggle('btn-outline-secondary', type !== 'full');
        document.getElementById('paymentTypeBtnDebt').classList.toggle('btn-primary', type === 'debt');
        document.getElementById('paymentTypeBtnDebt').classList.toggle('btn-outline-secondary', type !== 'debt');

        const confirmBtn = document.getElementById('confirmCheckoutBtn');
        confirmBtn.textContent = type === 'debt' ? 'Simpan Transaksi Hutang' : 'Simpan Pembayaran';

        // Reset scroll ke atas saat ganti mode
        const paymentBody = document.querySelector('.payment-stage-body');
        if (paymentBody) paymentBody.scrollTop = 0;

        // Sesuaikan keterangan + placeholder pelanggan sesuai jenis pembayaran
        const buyerInput = document.getElementById('buyerSearchInput');
        const hint = document.getElementById('buyerSelectedHint');
        const hasBuyer = document.getElementById('buyerId').value !== '';

        if (type === 'debt') {
            buyerInput.placeholder = 'Cari pelanggan terdaftar (wajib utk hutang)';
            if (!hasBuyer && hint) {
                hint.textContent = 'Hutang wajib memilih atau membuat pelanggan terdaftar.';
            }
        } else {
            buyerInput.placeholder = 'Pelanggan umum (lewati atau cari)';
            if (!hasBuyer && hint) {
                hint.textContent = 'Bayar penuh boleh tanpa profil — lewati = Pelanggan Umum.';
            }
        }
    }

    function selectBuyer(buyer) {
        document.getElementById('buyerId').value = buyer?.id || '';
        document.getElementById('buyerSearchInput').value = buyer ? buyer.name : '';

        const hint = document.getElementById('buyerSelectedHint');
        if (buyer) {
            const phoneInfo = buyer.phone ? ' (' + buyer.phone + ')' : '';
            if (hint) hint.textContent = 'Dipilih: ' + buyer.name + phoneInfo;
        } else {
            if (hint) hint.textContent = 'Bayar penuh boleh tanpa profil — lewati = Pelanggan Umum.';
        }

        hideBuyerSearchResults();
    }

    function hideBuyerSearchResults() {
        const results = document.getElementById('buyerSearchResults');
        results.classList.add('d-none');
        results.innerHTML = '';
    }

    function renderBuyerSearchResults(buyers) {
        const results = document.getElementById('buyerSearchResults');
        if (!buyers.length) {
            results.innerHTML = '<div class="list-group-item small text-muted">Tidak ada pelanggan ditemukan</div>';
            results.classList.remove('d-none');
            return;
        }

        results.innerHTML = buyers.map((buyer) => {
            const phone = buyer.phone ? ' - ' + buyer.phone : '';
            const safeName = String(buyer.name).replace(/"/g, '&quot;');
            const safePhone = String(buyer.phone || '').replace(/"/g, '&quot;');
            return `<button type="button" class="list-group-item list-group-item-action buyer-option" data-id="${buyer.id}" data-name="${safeName}" data-phone="${safePhone}">${buyer.name}${phone}</button>`;
        }).join('');
        results.classList.remove('d-none');
    }

    function searchBuyers(query) {
        fetch(`{{ route('buyers.search') }}?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                renderBuyerSearchResults(Array.isArray(data) ? data : []);
            })
            .catch(() => {
                hideBuyerSearchResults();
            });
    }

    function toggleNewBuyerForm(show) {
        const form = document.getElementById('newBuyerForm');
        const shouldShow = typeof show === 'boolean' ? show : form.classList.contains('d-none');
        form.classList.toggle('d-none', !shouldShow);
        if (shouldShow) {
            document.getElementById('newBuyerName').focus();
        }
    }

    function saveNewBuyer() {
        const name = document.getElementById('newBuyerName').value.trim();
        const phone = document.getElementById('newBuyerPhone').value.trim();

        if (!name) {
            showCheckoutError('Nama pelanggan baru wajib diisi.');
            return;
        }

        fetch('{{ route("buyers.quick-store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, phone })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Gagal menambahkan pelanggan.');
            }
            return data.buyer;
        })
        .then((buyer) => {
            selectBuyer(buyer);
            document.getElementById('newBuyerName').value = '';
            document.getElementById('newBuyerPhone').value = '';
            toggleNewBuyerForm(false);
            clearCheckoutError();
        })
        .catch((err) => {
            showCheckoutError(err.message || 'Gagal menambahkan pelanggan.');
        });
    }

    function startCheckoutLoading(loading) {
        isSubmittingCheckout = loading;
        const btn = document.getElementById('confirmCheckoutBtn');
        if (loading) {
            btn.disabled = true;
            btn.dataset.originalText = btn.textContent;
            btn.textContent = 'Menyimpan...';
        } else {
            btn.disabled = getCurrentTotal() <= 0;
            btn.textContent = btn.dataset.originalText || btn.textContent;
        }
    }

    function processCheckout() {
        if (isSubmittingCheckout) {
            return;
        }

        clearCheckoutError();

        const total = getCurrentTotal();
        const paymentType = getSelectedPaymentType();
        const payload = { payment_type: paymentType };
        const buyerId = parseInt(document.getElementById('buyerId').value) || null;
        const buyerText = document.getElementById('buyerSearchInput').value.trim();

        if (buyerText !== '' && !buyerId) {
            showCheckoutError('Pilih pelanggan dari daftar atau gunakan tombol + Pelanggan baru.');
            return;
        }

        if (buyerId) {
            payload.buyer_id = buyerId;
        }

        if (paymentType === 'full') {
            const paymentMethod = document.getElementById('paymentMethod').value;
            const paidAmount = parseInt(document.getElementById('paidAmount').value) || 0;

            if (paidAmount < total) {
                showCheckoutError('Pembayaran kurang. Kekurangan tidak otomatis menjadi hutang.');
                return;
            }

            payload.payment_method = paymentMethod;
            payload.paid_amount = paidAmount;
        } else {
            const dueDate = document.getElementById('debtDueDate').value;
            const downPayment = parseInt(document.getElementById('debtDownPayment').value) || 0;
            const debtNote = document.getElementById('debtNote').value.trim();

            if (!buyerId) {
                showCheckoutError('Transaksi hutang wajib memilih pelanggan.');
                return;
            }
            if (!dueDate) {
                showCheckoutError('Tanggal jatuh tempo wajib diisi.');
                return;
            }
            if (downPayment < 0) {
                showCheckoutError('Pembayaran awal minimal 0.');
                return;
            }
            if (downPayment > total) {
                showCheckoutError('Pembayaran awal tidak boleh lebih besar dari total.');
                return;
            }
            if (downPayment === total) {
                setPaymentType('full');
                document.getElementById('paidAmount').value = downPayment;
                calculateChange();
                showCheckoutError('Pembayaran awal sama dengan total. Silakan gunakan Bayar Penuh.');
                return;
            }

            payload.due_date = dueDate;
            payload.down_payment = downPayment;
            payload.debt_note = debtNote;

            if (downPayment > 0) {
                payload.payment_method = document.getElementById('debtPaymentMethod').value;
            }
        }

        startCheckoutLoading(true);

        fetch('{{ route("sales.checkout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Transaksi gagal disimpan');
            }
            return data;
        })
        .then(data => {
            document.getElementById('successInvoice').textContent = data.invoice_number;
            document.getElementById('successTotal').textContent = 'Rp ' + formatCurrency(data.total_price || total);
            document.getElementById('successReceiptLink').href = `/admin/sales/${data.sale_document_id}`;

            const buyerRow = document.getElementById('successBuyerRow');
            if (data.buyer_name) {
                buyerRow.classList.remove('d-none');
                document.getElementById('successBuyer').textContent = data.buyer_name;
            } else {
                buyerRow.classList.add('d-none');
                document.getElementById('successBuyer').textContent = 'Pelanggan umum';
            }

            if (data.payment_type === 'debt') {
                document.getElementById('successMetaLabel').textContent = 'Sisa Hutang:';
                document.getElementById('successMetaValue').textContent = 'Rp ' + formatCurrency(data.debt_remaining || 0);
                document.getElementById('successStatus').textContent = 'Pending Hutang';
            } else {
                document.getElementById('successMetaLabel').textContent = 'Kembalian:';
                document.getElementById('successMetaValue').textContent = 'Rp ' + formatCurrency(data.change_amount || 0);
                document.getElementById('successStatus').textContent = 'Lunas';
            }

            setStage('success');
            updateCartDisplay({});
        })
        .catch(err => {
            showCheckoutError(err.message || 'Gagal memproses checkout.');
        })
        .finally(() => {
            startCheckoutLoading(false);
        });
    }

    function clearCart() {
        if (currentStage !== 'cart') {
            alert('Kembali ke keranjang untuk mengosongkan item.');
            return;
        }
        if (!confirm('Kosongkan keranjang?')) {
            return;
        }

        fetch('{{ route("sales.clear-cart") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCartDisplay({});
            }
        });
    }

    function startNewTransaction() {
        window.location.href = '{{ route("sales.index") }}';
    }

    document.getElementById('productSearch').addEventListener('input', function() {
        if (currentStage !== 'cart') {
            this.value = '';
            document.getElementById('searchResults').classList.add('d-none');
            return;
        }

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

    document.getElementById('buyerSearchInput').addEventListener('input', function() {
        const query = this.value.trim();

        if (query === '') {
            selectBuyer(null);
            return;
        }

        document.getElementById('buyerId').value = '';

        clearTimeout(buyerSearchTimeout);
        buyerSearchTimeout = setTimeout(() => {
            searchBuyers(query);
        }, 250);
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('buyer-option')) {
            selectBuyer({
                id: e.target.dataset.id,
                name: e.target.dataset.name,
                phone: e.target.dataset.phone || null,
            });
            return;
        }

        const container = document.getElementById('buyerSearchInput').closest('.position-relative');
        if (!container.contains(e.target)) {
            hideBuyerSearchResults();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'F2') {
            e.preventDefault();
            if (currentStage === 'cart') {
                focusSearch();
            }
        }

        if (e.key === 'Enter' && currentStage === 'payment' && document.activeElement.id === 'paidAmount') {
            e.preventDefault();
            processCheckout();
        }
    });

    updatePanelHeader('cart');
    syncPaymentTypeView();
    updatePaymentSummary();
    handleDebtDownPaymentInput();
</script>
@endsection
