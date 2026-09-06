<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;  

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::orderBy('id', 'desc')->paginate(20);
        return view('admin.expense.list', compact('expenses'));
    }

    public function search(Request $request)
    {
        $expenses = Expense::where('name', 'like', '%'.$request->q.'%')
            ->orWhere('category', 'like', '%'.$request->q.'%')
            ->orWhere('amount', 'like', '%'.$request->q.'%')
            ->orWhere('note', 'like', '%'.$request->q.'%')
            ->latest()
            ->paginate(20)
            ->appends(['q' => $request->q]);
        return view('admin.expense.list', compact('expenses'));
    }

    public function addExpense(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'amount' => 'required|numeric',
            'periode' => 'required|in:1,3,6,12', // Validasi periode (3, 6, atau 12 bulan)
            'note' => 'nullable',
        ]);

        $amountPerMonth = $request->amount / $request->periode; // Bagi jumlah ke periode yang dipilih
        $createdAt = now();

        for ($i = 0; $i < $request->periode; $i++) {
            
            Expense::create([
                'name' => $request->name,
                'category' => $request->category,
                'amount' => $amountPerMonth,
                'note' => $request->note ?? null,
                'created_at' => $createdAt->copy()->addMonths($i)->setTime(
                                    $createdAt->hour,
                                    $createdAt->minute,
                                    $createdAt->second
                                ),
                'updated_at' => $createdAt->copy()->addMonths($i), // Tambahkan updated_at jika perlu
            ]);
        }

        return redirect()->back()->with('success', 'Expense added successfully');
    }

    public function delete($id)
    {
        Expense::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Expense deleted successfully');
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'amount' => 'required|numeric',
            'note' => 'nullable',
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update([
            'name' => $request->name,
            'category' => $request->category,
            'amount' => $request->amount,
            'note' => $request->note ?? null,
        ]);

        return redirect()->back()->with('success', 'Expense updated successfully');
    }
}
