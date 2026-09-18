<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function product(int $stock = 10, ?int $categoryId = null): Product
    {
        return Product::create([
            'category_id' => $categoryId,
            'barcode' => 'P-' . uniqid(),
            'name' => 'Produk ' . uniqid(),
            'cost_price' => 5000,
            'selling_price' => 12000,
            'discount' => 0,
            'stock' => $stock,
            'unit' => 'pcs',
        ]);
    }

    private function activeOpname(): StockOpname
    {
        return StockOpname::create([
            'code' => 'SO-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
            'status' => 'active',
            'started_at' => now(),
            'created_by' => $this->admin()->id,
        ]);
    }

    public function testFinishAdjustsStockToPhysicalCount(): void
    {
        $admin = $this->admin();
        $product = $this->product(10);
        $opname = $this->activeOpname();

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $product->id,
            'stok_sistem' => 10,
            'stok_fisik' => 8,
            'selisih' => -2,
            'terjual' => 1,
            'status' => 'selisih',
            'counted_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('stock-opname.finish', $opname))
            ->assertRedirect(route('stock-opname.show', $opname));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
        $this->assertDatabaseHas('stock_opnames', ['id' => $opname->id, 'status' => 'done']);
    }

    public function testFinishUsesPenetapanWhenPresent(): void
    {
        $admin = $this->admin();
        $product = $this->product(10);
        $opname = $this->activeOpname();

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $product->id,
            'stok_sistem' => 10,
            'stok_fisik' => 7,
            'stok_penetapan' => 9,
            'selisih' => -3,
            'terjual' => 0,
            'status' => 'selisih',
            'counted_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('stock-opname.finish', $opname));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 9]);
    }

    public function testFinishLeavesUncountedProductsUntouched(): void
    {
        $admin = $this->admin();
        $counted = $this->product(10);
        $skipped = $this->product(5);
        $opname = $this->activeOpname();

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $counted->id,
            'stok_sistem' => 10,
            'stok_fisik' => 12,
            'selisih' => 2,
            'terjual' => 0,
            'status' => 'selisih',
            'counted_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('stock-opname.finish', $opname));

        $this->assertDatabaseHas('products', ['id' => $counted->id, 'stock' => 12]);
        $this->assertDatabaseHas('products', ['id' => $skipped->id, 'stock' => 5]);
    }
}