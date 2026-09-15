<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDocument;
use App\Models\DebtPayment;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    // Halaman kasir - form input penjualan
    public function index()
    {
        $products = Product::with('category')->get();
        $cart = session()->get('sale_cart', []);
        
        return view('admin.sales.index', compact('products', 'cart'));
    }

    // Search product by name atau barcode
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $products = Product::with('category')
            ->where('name', 'like', '%' . $query . '%')
            ->orWhere('barcode', 'like', '%' . $query . '%')
            ->get();

        return response()->json($products);
    }

    // Add product ke cart (session)
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('sale_cart', []);

        // Cek stok maksimal sebelum ditambah ke keranjang
        $newQty = isset($cart[$product->id])
            ? $cart[$product->id]['quantity'] + $request->quantity
            : $request->quantity;

        if ($product->stock < $newQty) {
            return response()->json([
                'success' => false,
                'message' => "Stok {$product->name} tidak cukup (tersisa {$product->stock}).",
            ], 422);
        }

        // Jika produk sudah di cart, increment quantity
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = $newQty;
        } else {
            // Ambil discount otomatis dari produk
            $auto_discount = $product->discount;
            
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'auto_discount' => $auto_discount,        // Diskon otomatis dari produk
                'manual_discount' => 0,                    // Diskon tambahan dari kasir
                'discount' => $auto_discount,              // Total diskon (untuk compatibility)
                'quantity' => $request->quantity,
                'unit' => $product->unit,
            ];
        }

        session()->put('sale_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang',
            'cart_count' => count($cart),
            'cart' => $cart,
        ]);
    }

    // Update quantity atau diskon di cart
    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
            'discount' => 'nullable|integer|min:0',
        ]);

        $productId = $request->product_id;
        $cart = session()->get('sale_cart', []);

        if (isset($cart[$productId])) {
            if ($request->has('quantity')) {
                $newQty = (int) $request->quantity;
                $product = Product::find($productId);

                if ($product && $newQty > $product->stock) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$product->name} tidak cukup (tersisa {$product->stock}).",
                    ], 422);
                }

                $cart[$productId]['quantity'] = $newQty;
            }
            if ($request->has('discount')) {
                // Diskon yang diinput kasir (manual)
                $manual_discount = (int) $request->discount;
                $auto_discount = $cart[$productId]['auto_discount'] ?? 0;
                $selling = $cart[$productId]['selling_price'] ?? 0;

                if ($manual_discount > 0 && $selling <= ($auto_discount + $manual_discount)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Diskon tidak boleh lebih besar atau sama dengan harga jual.',
                    ], 422);
                }

                $cart[$productId]['manual_discount'] = $manual_discount;
                $cart[$productId]['discount'] = $auto_discount + $manual_discount; // Total
            }
        }

        session()->put('sale_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Keranjang diupdate',
            'cart' => $cart,
        ]);
    }

    // Remove item dari cart
    public function removeFromCart(Request $request)
    {
        $productId = $request->product_id;
        $cart = session()->get('sale_cart', []);

        unset($cart[$productId]);
        session()->put('sale_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Produk dihapus dari keranjang',
            'cart_count' => count($cart),
            'cart' => $cart,
        ]);
    }

    public function clearCart()
    {
        session()->forget('sale_cart');

        return response()->json([
            'success' => true,
            'message' => 'Keranjang dikosongkan',
            'cart' => [],
        ]);
    }

    // Checkout - proses pembayaran
    public function checkout(Request $request)
    {
        if (StockOpname::active()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Stock Opname sedang berlangsung. Transaksi penjualan ditunda sampai opname selesai.',
            ], 403);
        }

        $request->validate([
            'payment_type' => 'nullable|in:full,debt',
            'payment_method' => 'nullable|in:cash,qris,transfer',
            'paid_amount' => 'nullable|integer|min:0',
            'buyer_id' => 'nullable|exists:buyers,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'down_payment' => 'nullable|integer|min:0',
            'debt_note' => 'nullable|string|max:2000',
        ]);

        $cart = session()->get('sale_cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong',
            ], 422);
        }

        // Hitung totals
        $subtotal = 0;
        $discount_total = 0;

        foreach ($cart as $item) {
            $auto_discount = $item['auto_discount'] ?? 0;
            $manual_discount = $item['manual_discount'] ?? 0;
            $total_discount = $auto_discount + $manual_discount;
            
            $item_subtotal = ($item['selling_price'] - $total_discount) * $item['quantity'];
            $subtotal += $item_subtotal;
            $discount_total += $total_discount * $item['quantity'];
        }

        $total_price = $subtotal;

        // Validasi diskon item: tidak boleh meniadakan harga atau membuat harga negatif
        foreach ($cart as $item) {
            $netDiscount = ($item['auto_discount'] ?? 0) + ($item['manual_discount'] ?? 0);
            $selling = $item['selling_price'] ?? 0;

            if ($netDiscount > 0 && $selling <= $netDiscount) {
                return response()->json([
                    'success' => false,
                    'message' => "Diskon {$item['name']} tidak boleh lebih besar atau sama dengan harga jual (Rp {$selling}).",
                ], 422);
            }
        }

        if ($total_price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Total belanja tidak valid.',
            ], 422);
        }

        $paymentType = $request->input('payment_type', 'full');
        $isDebt = $paymentType === 'debt';

        $paymentMethod = 'cash';
        $paidAmount = 0;
        $changeAmount = 0;
        $status = 'completed';

        $buyerId = null;
        $dueDate = null;
        $downPayment = 0;
        $debtRemaining = 0;
        $debtNote = null;
        $buyerId = $request->filled('buyer_id') ? (int) $request->buyer_id : null;

        if (!$isDebt) {
            if (!$request->filled('payment_method')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metode pembayaran wajib dipilih untuk bayar penuh.',
                ], 422);
            }

            $paymentMethod = $request->payment_method;
            $paidAmount = (int) ($request->paid_amount ?? 0);

            if ($paidAmount < $total_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tunai kurang dari total belanja.',
                ], 422);
            }

            $changeAmount = $paidAmount - $total_price;
            $downPayment = $paidAmount;
        } else {
            $dueDate = $request->input('due_date');
            $debtNote = $request->input('debt_note');
            $downPayment = (int) ($request->input('down_payment', 0));

            if (!$buyerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelanggan wajib dipilih untuk transaksi hutang.',
                ], 422);
            }

            if (!$dueDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal jatuh tempo wajib diisi untuk transaksi hutang.',
                ], 422);
            }

            if ($downPayment < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran awal tidak boleh negatif.',
                ], 422);
            }

            if ($downPayment > $total_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran awal tidak boleh lebih besar dari total.',
                ], 422);
            }

            if ($downPayment === $total_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran awal sama dengan total. Gunakan mode Bayar Penuh.',
                ], 422);
            }

            if ($downPayment > 0 && !$request->filled('payment_method')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metode pembayaran awal wajib dipilih jika ada pembayaran awal.',
                ], 422);
            }

            $paymentMethod = $downPayment > 0 ? $request->payment_method : 'debt';
            $paidAmount = $downPayment;
            $changeAmount = 0;
            $status = 'pending';
            $debtRemaining = $total_price - $downPayment;
        }

        // Buat nota + item + kurangi stok dalam satu transaksi DB (cegah stok minus & data yatim)
        $stockError = null;

        $saleDocument = DB::transaction(function () use (
            $cart, $subtotal, $discount_total, $total_price,
            $paidAmount, $changeAmount, $paymentMethod, $paymentType, $isDebt, $status,
            $buyerId, $dueDate, $downPayment, $debtRemaining, $debtNote, &$stockError
        ) {
            $ids = array_keys($cart);
            $lockedProducts = Product::whereIn('id', $ids)
                ->when(config('database.default') !== 'sqlite', fn ($q) => $q->lockForUpdate())
                ->get()
                ->keyBy('id');

            // Validasi stok dengan kunci baris (cegah oversell saat transaksi bersamaan)
            foreach ($cart as $productId => $item) {
                $product = $lockedProducts->get($productId);

                if (!$product) {
                    $stockError = 'Ada produk yang tidak tersedia lagi. Bersihkan keranjang lalu ulangi.';
                    return null;
                }
                if ($product->stock < $item['quantity']) {
                    $stockError = "Stok {$product->name} tidak cukup (tersisa {$product->stock}).";
                    return null;
                }
            }

            $saleDocument = SaleDocument::create([
                'user_id' => Auth::id(),
                'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . Str::random(4),
                'subtotal' => $subtotal,
                'discount_total' => $discount_total,
                'total_price' => $total_price,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'payment_type' => $paymentType,
                'is_debt' => $isDebt,
                'status' => $status,
                'buyer_id' => $buyerId,
                'due_date' => $dueDate,
                'down_payment' => $downPayment,
                'debt_remaining' => $debtRemaining,
                'debt_note' => $debtNote,
            ]);

            // Buat Sale items & kurangi stock (hanya setelah semua validasi lolos)
            foreach ($cart as $item) {
                Sale::create([
                    'sale_document_id' => $saleDocument->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'cost_price' => $item['cost_price'],
                    'selling_price' => $item['selling_price'],
                    'discount' => $item['discount'],
                    'quantity' => $item['quantity'],
                    'subtotal' => ($item['selling_price'] - $item['discount']) * $item['quantity'],
                ]);

                $lockedProducts->get($item['product_id'])->decrement('stock', $item['quantity']);
            }

            return $saleDocument;
        });

        if ($stockError !== null) {
            return response()->json([
                'success' => false,
                'message' => $stockError,
            ], 422);
        }

        // Clear cart session hanya setelah transaksi berhasil
        session()->forget('sale_cart');

        // Dashboard statistik harus langsung up-to-date setelah ada transaksi
        Cache::forget('dashboard.summary');
        Cache::forget('dashboard.top_products');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil',
            'sale_document_id' => $saleDocument->id,
            'invoice_number' => $saleDocument->invoice_number,
            'payment_type' => $saleDocument->payment_type,
            'buyer_name' => optional($saleDocument->buyer)->name,
            'change_amount' => $saleDocument->change_amount,
            'debt_remaining' => $saleDocument->debt_remaining,
            'total_price' => $saleDocument->total_price,
        ]);
    }

    // Lihat detail nota (untuk print)
    public function show(SaleDocument $saleDocument)
    {
        $saleDocument->load('user', 'sales.product', 'buyer', 'debtPayments.user');

        return view('admin.sales.show', compact('saleDocument'));
    }

    // Terima pembayaran (cicilan/pelunasan) hutang
    public function payDebt(Request $request, SaleDocument $saleDocument)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:cash,qris,transfer',
            'note' => 'nullable|string|max:2000',
        ]);

        $debtPaid = false;
        $remaining = 0;

        DB::transaction(function () use (&$debtPaid, &$remaining, $saleDocument, $validated) {
            $doc = SaleDocument::whereKey($saleDocument->id)
                ->when(config('database.default') !== 'sqlite', fn ($q) => $q->lockForUpdate())
                ->first();

            if (!$doc || !$doc->is_debt) {
                throw ValidationException::withMessages(['amount' => 'Transaksi ini bukan transaksi hutang.']);
            }
            if ($doc->status !== 'pending' || $doc->debt_remaining <= 0) {
                throw ValidationException::withMessages(['amount' => 'Hutang pada nota ini sudah lunas.']);
            }
            if ($validated['amount'] > $doc->debt_remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah pembayaran melebihi sisa hutang (Rp ' . number_format($doc->debt_remaining, 0, ',', '.') . ').',
                ]);
            }

            DebtPayment::create([
                'sale_document_id' => $doc->id,
                'user_id' => Auth::id(),
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'note' => $validated['note'] ?? null,
                'paid_at' => now(),
            ]);

            $doc->debt_remaining -= $validated['amount'];
            if ($doc->debt_remaining === 0) {
                $doc->status = 'completed';
                $debtPaid = true;
            }
            $doc->save();
            $remaining = $doc->debt_remaining;
        });

        return response()->json([
            'success' => true,
            'message' => $debtPaid
                ? 'Hutang lunas. Transaksi ditandai selesai.'
                : 'Pembayaran hutang berhasil dicatat.',
            'debt_remaining' => $remaining,
            'paid' => $debtPaid,
        ]);
    }

    // History penjualan kasir
    public function history(Request $request)
    {
        $query = SaleDocument::with('user', 'sales', 'buyer');

        // Text Search (Invoice)
        if ($request->has('invoice') && $request->invoice) {
            $query->where('invoice_number', 'like', '%' . $request->invoice . '%');
        }

        // Quick Range Filter
        if ($request->has('range') && $request->range) {
            if ($request->range == 'today') {
                $query->whereDate('created_at', today());
            } elseif ($request->range == '7days') {
                $query->whereDate('created_at', '>=', today()->subDays(7));
            } elseif ($request->range == '30days') {
                $query->whereDate('created_at', '>=', today()->subDays(30));
            }
        } else {
            // Advanced Date Range
            if ($request->has('from_date') && $request->from_date) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->has('to_date') && $request->to_date) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }
        }

        // Dropdown Filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $saleDocuments = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.sales.history', compact('saleDocuments'));
    }
}
