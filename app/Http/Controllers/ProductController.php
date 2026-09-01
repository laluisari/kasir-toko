<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        $products = Product::with('category')->get();
        $categories = Category::all();
        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'name' => 'required|string|max:255',
            'cost_price' => 'required|integer|min:0',
            'selling_price' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|in:pcs,bks,botol,kg',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Set default values for nullable fields
        if (!isset($validated['discount'])) {
            $validated['discount'] = 0;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('products', $imageName, 'public');
            $validated['image'] = 'products/' . $imageName;
        }

        Product::create($validated);

        return redirect()->route('products.index')
                        ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Update the product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'name' => 'required|string|max:255',
            'cost_price' => 'required|integer|min:0',
            'selling_price' => 'required|integer|min:0',
            'discount' => 'nullable|integer|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|in:pcs,bks,botol,kg',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Set default values for nullable fields
        if (!isset($validated['discount'])) {
            $validated['discount'] = 0;
        }

        // Handle image upload - delete old image if exists
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && \Storage::disk('public')->exists($product->image)) {
                \Storage::disk('public')->delete($product->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('products', $imageName, 'public');
            $validated['image'] = 'products/' . $imageName;
        }

        $product->update($validated);

        return redirect()->route('products.index')
                        ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the product from storage.
     */
    public function destroy(Product $product)
    {
        // Delete image if exists
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
                        ->with('success', 'Produk berhasil dihapus.');
    }
}
