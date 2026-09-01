<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDocument;
use Illuminate\Http\Request;
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

    // Checkout - proses pembayaran
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
            'paid_amount' => 'required|integer|min:0',
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
        $paid_amount = $request->paid_amount;
        $change_amount = $paid_amount - $total_price;

        // Buat SaleDocument (nota)
        $saleDocument = SaleDocument::create([
            'user_id' => auth()->id(),
            'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . Str::random(4),
            'subtotal' => $subtotal,
            'discount_total' => $discount_total,
            'total_price' => $total_price,
            'paid_amount' => $paid_amount,
            'change_amount' => $change_amount,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
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
        ]);
    }

    // Lihat detail nota (untuk print)
    public function show(SaleDocument $saleDocument)
    {
        $saleDocument->load('user', 'sales.product');

        return view('admin.sales.show', compact('saleDocument'));
    }

    // History penjualan kasir
    public function history(Request $request)
    {
        $query = SaleDocument::with('user', 'sales');

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
