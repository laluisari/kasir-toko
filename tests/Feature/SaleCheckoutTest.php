<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function cartItem(Product $product, int $quantity, int $autoDiscount = 0, int $manualDiscount = 0): array
    {
        return [
            'product_id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'cost_price' => $product->cost_price,
            'selling_price' => $product->selling_price,
            'auto_discount' => $autoDiscount,
            'manual_discount' => $manualDiscount,
            'discount' => $autoDiscount + $manualDiscount,
            'quantity' => $quantity,
            'unit' => $product->unit,
        ];
    }

    public function testCheckoutSuccessDecrementsStockAndClearsCart(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Kopi Susu',
            'barcode' => '8990000000001',
            'cost_price' => 3000,
            'selling_price' => 5000,
            'discount' => 0,
            'stock' => 10,
            'unit' => 'pcs',
        ]);

        $this->actingAs($admin)
            ->withSession(['sale_cart' => [$product->id => $this->cartItem($product, 4)]])
            ->postJson(route('sales.checkout'), [
                'payment_type' => 'full',
                'payment_method' => 'cash',
                'paid_amount' => 20000,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 6]);
        $this->assertDatabaseCount('sale_documents', 1);
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseHas('sales', ['product_id' => $product->id, 'quantity' => 4, 'subtotal' => 20000]);
        $this->assertFalse(session()->has('sale_cart'));
    }

    public function testCheckoutRejectsQuantityExceedingStockWithoutWritingAnything(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Teh Botol',
            'barcode' => '8990000000002',
            'cost_price' => 2500,
            'selling_price' => 4000,
            'discount' => 0,
            'stock' => 3,
            'unit' => 'botol',
        ]);

        $this->actingAs($admin)
            ->withSession(['sale_cart' => [$product->id => $this->cartItem($product, 4)]])
            ->postJson(route('sales.checkout'), [
                'payment_type' => 'full',
                'payment_method' => 'cash',
                'paid_amount' => 16000,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 3]);
        $this->assertDatabaseCount('sale_documents', 0);
        $this->assertDatabaseCount('sales', 0);
    }

    public function testCheckoutRejectsDiscountReachingOrExceedingSellingPrice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Coklat Bar',
            'barcode' => '8990000000003',
            'cost_price' => 1000,
            'selling_price' => 2000,
            'discount' => 0,
            'stock' => 5,
            'unit' => 'pcs',
        ]);

        // Diskon total 2000 == harga jual → harus ditolak
        $this->actingAs($admin)
            ->withSession(['sale_cart' => [$product->id => $this->cartItem($product, 1, 0, 2000)]])
            ->postJson(route('sales.checkout'), [
                'payment_type' => 'full',
                'payment_method' => 'cash',
                'paid_amount' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('sale_documents', 0);
        $this->assertDatabaseCount('sales', 0);
    }

    public function testCheckoutRejectsDeletedProductLeavingNoOrphanDocument(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'name' => 'Minuman Sachet',
            'barcode' => '8990000000004',
            'cost_price' => 2000,
            'selling_price' => 3000,
            'discount' => 0,
            'stock' => 8,
            'unit' => 'sachet',
        ]);

        // Hapus produk setelah masuk keranjang (simulasi produk dihapus admin lain)
        $product->delete();

        $this->actingAs($admin)
            ->withSession(['sale_cart' => [$product->id => $this->cartItem($product, 2)]])
            ->postJson(route('sales.checkout'), [
                'payment_type' => 'full',
                'payment_method' => 'cash',
                'paid_amount' => 6000,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('sale_documents', 0);
        $this->assertDatabaseCount('sales', 0);
    }
}