<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDocument;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        // Jika produk sudah di cart, increment quantity
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $request->quantity;
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
                $cart[$productId]['quantity'] = $request->quantity;
            }
            if ($request->has('discount')) {
                // Diskon yang diinput kasir (manual)
                $manual_discount = $request->discount;
                $auto_discount = $cart[$productId]['auto_discount'] ?? 0;
                
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

        // Buat SaleDocument (nota)
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

        // Buat Sale items & kurangi stock
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

            // Kurangi stock produk
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->decrement('stock', $item['quantity']);
            }
        }

        // Clear cart session
        session()->forget('sale_cart');

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
        $saleDocument->load('user', 'sales.product', 'buyer');

        return view('admin.sales.show', compact('saleDocument'));
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
