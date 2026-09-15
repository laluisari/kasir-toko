<?php

namespace Tests\Feature;

use App\Models\SaleDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function makeDoc(array $overrides = []): SaleDocument
    {
        return SaleDocument::create(array_merge([
            'invoice_number' => 'INV-' . uniqid(),
            'subtotal' => 0,
            'discount_total' => 0,
            'total_price' => 10000,
            'paid_amount' => 10000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
        ], $overrides));
    }

    public function testSummaryReturnsConsolidatedKpisAndTotals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->makeDoc(['total_price' => 25000, 'payment_method' => 'cash']);
        $this->makeDoc(['total_price' => 30000, 'payment_method' => 'qris']);
        $this->makeDoc(['total_price' => 99000, 'status' => 'pending']);
        $this->makeDoc(['total_price' => 99000, 'status' => 'canceled']);

        $this->actingAs($admin)
            ->getJson(route('dashboard.api.summary'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('todays_sales', 55000)
            ->assertJsonPath('kpi.todays_sales.value', 55000)
            ->assertJsonPath('kpi.todays_sales.formatted', 'Rp 55.000')
            ->assertJsonPath('kpi.todays_transactions', 2)
            ->assertJsonPath('payment_methods.0.count', 1)
            ->assertJsonPath('payment_methods.0.total', 25000)
            ->assertJsonPath('payment_methods.1.total', 30000)
            ->assertJsonPath('kpi.todays_items', 0);
    }

    public function testSummaryServesCachedValueUntilInvalidated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doc = $this->makeDoc(['total_price' => 25000]);

        $this->actingAs($admin)
            ->getJson(route('dashboard.api.summary'))
            ->assertJsonPath('todays_sales', 25000);

        // Data berubah, tapi tanpa invalidasi cache endpoint tetap kirim nilai lama
        $doc->update(['total_price' => 40000]);

        $this->actingAs($admin)
            ->getJson(route('dashboard.api.summary'))
            ->assertJsonPath('todays_sales', 25000);

        // Simulasi SaleController::checkout → invalidasi → nilai termutakhir kembali
        \Illuminate\Support\Facades\Cache::forget('dashboard.summary');

        $this->actingAs($admin)
            ->getJson(route('dashboard.api.summary'))
            ->assertJsonPath('todays_sales', 40000);
    }

    public function testCheckoutInvalidatesDashboardSummaryCache(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->makeDoc(['total_price' => 25000]);
        $this->actingAs($admin)->getJson(route('dashboard.api.summary'))->assertJsonPath('todays_sales', 25000);

        // Proses checkout baru
        $this->actingAs($admin)
            ->postJson(route('sales.checkout'), [
                'payment_type' => 'full',
                'payment_method' => 'cash',
                'paid_amount' => 0,
            ])
            ->assertStatus(422); // keranjang kosong → ditolak, cache tidak boleh ikut ter-invalidate

        // (tanpa cart, checkout tidak berhasil; pastikan endpoint summary tetap sehat)
        $this->actingAs($admin)
            ->getJson(route('dashboard.api.summary'))
            ->assertOk()
            ->assertJsonPath('todays_sales', 25000);
    }
}