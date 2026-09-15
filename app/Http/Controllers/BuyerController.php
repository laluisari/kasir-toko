<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    /**
     * Display a listing of buyers with debt summary.
     */
    public function index(Request $request)
    {
        $query = Buyer::withCount('saleDocuments')
            ->with(['saleDocuments' => function ($q) {
                $q->where('is_debt', true)
                  ->where('status', 'pending')
                  ->latest();
            }]);

        // Search by name atau phone
        if ($request->has('q') && $request->q) {
            $searchTerm = '%' . $request->q . '%';
            $query->where('name', 'like', $searchTerm)
                  ->orWhere('phone', 'like', $searchTerm);
        }

        $buyers = $query->orderBy('name', 'asc')->paginate(20);

        // Hitung total outstanding hutang per buyer (untuk summary)
        $buyers->each(function ($buyer) {
            $buyer->total_debt_outstanding = $buyer->saleDocuments
                ->sum(function ($sale) {
                    return $sale->debt_remaining ?? 0;
                });
        });

        return view('admin.buyers.index', compact('buyers'));
    }

    /**
     * Show buyer detail + debt history.
     */
    public function show(Buyer $buyer)
    {
        $buyer->load(['saleDocuments' => function ($q) {
            $q->where('is_debt', true)
              ->with('sales.product', 'debtPayments.user')
              ->orderBy('created_at', 'desc');
        }]);

        // Hitung summary
        $totalDebt = 0;
        $totalOutstanding = 0;
        $totalPaid = 0;

        foreach ($buyer->saleDocuments as $sale) {
            $totalDebt += $sale->total_price;
            $totalOutstanding += $sale->debt_remaining ?? 0;
            $totalPaid += ($sale->down_payment ?? 0) + $sale->debtPayments->sum('amount');
        }

        return view('admin.buyers.show', compact('buyer', 'totalDebt', 'totalOutstanding', 'totalPaid'));
    }

    /**
     * Store a newly created buyer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        Buyer::create([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return redirect()->route('buyers.index')
                        ->with('success', 'Buyer berhasil ditambahkan.');
    }

    /**
     * Quick store buyer via AJAX (checkout flow).
     */
    public function quickStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $buyer = Buyer::create([
            'name' => trim((string) $request->name),
            'phone' => $request->filled('phone') ? trim((string) $request->phone) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buyer berhasil ditambahkan.',
            'buyer' => $buyer,
        ]);
    }

    /**
     * Update the specified buyer.
     */
    public function update(Request $request, Buyer $buyer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $buyer->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return redirect()->route('buyers.index')
                        ->with('success', 'Buyer berhasil diperbarui.');
    }

    /**
     * Remove the specified buyer (soft delete / check dependencies).
     */
    public function destroy(Buyer $buyer)
    {
        // Cek apakah ada hutang outstanding
        $hasOutstandingDebt = $buyer->saleDocuments()
            ->where('is_debt', true)
            ->where('status', 'pending')
            ->where('debt_remaining', '>', 0)
            ->exists();

        if ($hasOutstandingDebt) {
            return redirect()->route('buyers.index')
                            ->with('error', 'Tidak bisa menghapus buyer yang masih memiliki hutang outstanding.');
        }

        $buyer->delete();

        return redirect()->route('buyers.index')
                        ->with('success', 'Buyer berhasil dihapus.');
    }

    /**
     * Search buyer (used by checkout form autocomplete nanti).
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        $buyers = Buyer::query()
            ->when($query !== '', function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('phone', 'like', '%' . $query . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($buyers);
    }
}
