<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::with('creator', 'items')->orderByDesc('id')->get();

        return view('admin.stock-opname.index', compact('opnames'));
    }

    public function store(Request $request)
    {
        if (StockOpname::active()->exists()) {
            return redirect()->route('stock-opname.index')
                ->with('error', 'Masih ada periode Stock Opname yang aktif. Selesaikan atau batalkan dulu.');
        }

        $request->validate([
            'note' => 'nullable|string|max:255',
        ]);

        $opname = StockOpname::create([
            'code' => 'SO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'note' => $request->input('note'),
            'status' => 'active',
            'started_at' => now(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('stock-opname.counting', $opname)
            ->with('success', "Periode {$opname->code} dimulai.");
    }

    public function counting(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'active') {
            return redirect()->route('stock-opname.show', $stockOpname);
        }

        $products = Product::with('category')->get();
        $items = $stockOpname->items->keyBy('product_id');
        $total = $stockOpname->items->count();

        return view('admin.stock-opname.counting', compact('stockOpname', 'products', 'items', 'total'));
    }

    public function item(Request $request, StockOpname $stockOpname, Product $product)
    {
        if ($stockOpname->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Periode Stock Opname ini sudah selesai.',
            ], 409);
        }

        $request->validate([
            'status' => 'required|in:pas,selisih',
            'stok_fisik' => 'required_if:status,selisih|integer|min:0',
            'stok_penetapan' => 'nullable|integer|min:0',
            'catatan' => 'nullable|string|max:255',
        ]);

        $stokSistem = (int) $product->stock;
        $selisih = null;

        if ($request->status === 'pas') {
            $stokFisik = $stokSistem;
            $selisih = 0;
        } else {
            $stokFisik = (int) $request->stok_fisik;
            $selisih = $stokFisik - $stokSistem;
        }

        $terjual = (int) Sale::where('product_id', $product->id)->sum('quantity');

        $item = $stockOpname->items()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'stok_sistem' => $stokSistem,
                'stok_fisik' => $stokFisik,
                'selisih' => $selisih,
                'terjual' => $terjual,
                'status' => $request->status,
                'stok_penetapan' => $request->filled('stok_penetapan') ? $request->stok_penetapan : null,
                'catatan' => $request->input('catatan'),
                'counted_by' => auth()->id(),
                'counted_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $request->status === 'pas'
                ? 'Stok sudah dicocokkan.'
                : 'Selisih tercatat: ' . ($selisih >= 0 ? '+' : '') . $selisih . '.',
            'item' => [
                'status' => $item->status,
                'stok_fisik' => $item->stok_fisik,
                'selisih' => $item->selisih,
                'stok_penetapan' => $item->stok_penetapan,
            ],
        ]);
    }

    public function finish(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'active') {
            return redirect()->route('stock-opname.index')
                ->with('error', 'Periode Stock Opname ini sudah selesai.');
        }

        $stockOpname->update([
            'status' => 'done',
            'ended_at' => now(),
        ]);

        return redirect()->route('stock-opname.show', $stockOpname)
            ->with('success', "Periode {$stockOpname->code} selesai. Selisih dicatat tanpa mengubah stok.");
    }

    public function cancel(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'active') {
            return redirect()->route('stock-opname.index')
                ->with('error', 'Hanya periode aktif yang bisa dibatalkan.');
        }

        $stockOpname->items()->delete();
        $stockOpname->delete();

        return redirect()->route('stock-opname.index')
            ->with('success', 'Periode Stock Opname dibatalkan. Item tidak dicatat.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['creator', 'items.product']);

        $summary = $stockOpname->summary();
        $summary['total_rp_selisih'] = $stockOpname->items
            ->sum(fn ($item) => (int) $item->selisih * (int) $item->product?->selling_price);

        return view('admin.stock-opname.show', compact('stockOpname', 'summary'));
    }
}