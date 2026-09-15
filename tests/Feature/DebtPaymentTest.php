<?php

namespace Tests\Feature;

use App\Models\Buyer;
use App\Models\DebtPayment;
use App\Models\SaleDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function makeDebtDocument(int $total = 100000, int $downPayment = 20000, int $remaining = null): SaleDocument
    {
        $buyer = Buyer::create([
            'name' => 'Budi Hutang',
            'phone' => '081234567890',
        ]);

        return SaleDocument::create([
            'user_id' => $this->makeUser()->id,
            'invoice_number' => 'INV-TEST-' . uniqid(),
            'subtotal' => $total,
            'discount_total' => 0,
            'total_price' => $total,
            'paid_amount' => $downPayment,
            'change_amount' => 0,
            'payment_method' => 'debt',
            'payment_type' => 'debt',
            'is_debt' => true,
            'status' => 'pending',
            'buyer_id' => $buyer->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'down_payment' => $downPayment,
            'debt_remaining' => $remaining ?? ($total - $downPayment),
        ]);
    }

    public function testPartialPaymentDecreasesRemainingAndKeepsPending(): void
    {
        $admin = $this->makeUser();
        $doc = $this->makeDebtDocument();

        $this->actingAs($admin)
            ->postJson(route('sales.pay-debt', $doc), [
                'amount' => 30000,
                'payment_method' => 'cash',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('paid', false)
            ->assertJsonPath('debt_remaining', 50000);

        $this->assertDatabaseHas('debt_payments', [
            'sale_document_id' => $doc->id,
            'user_id' => $admin->id,
            'amount' => 30000,
        ]);
        $this->assertDatabaseHas('sale_documents', [
            'id' => $doc->id,
            'debt_remaining' => 50000,
            'status' => 'pending',
        ]);
    }

    public function testFullPaymentSetsStatusCompleted(): void
    {
        $admin = $this->makeUser();
        $doc = $this->makeDebtDocument(total: 50000, downPayment: 0);

        $this->actingAs($admin)
            ->postJson(route('sales.pay-debt', $doc), [
                'amount' => 50000,
                'payment_method' => 'transfer',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('paid', true)
            ->assertJsonPath('debt_remaining', 0);

        $this->assertDatabaseHas('sale_documents', [
            'id' => $doc->id,
            'debt_remaining' => 0,
            'status' => 'completed',
        ]);
    }

    public function testOverpaymentIsRejectedWithoutWritingAnything(): void
    {
        $admin = $this->makeUser();
        $doc = $this->makeDebtDocument(total: 100000, downPayment: 20000);

        $this->actingAs($admin)
            ->postJson(route('sales.pay-debt', $doc), [
                'amount' => 90000,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('debt_payments', 0);
        $this->assertDatabaseHas('sale_documents', [
            'id' => $doc->id,
            'debt_remaining' => 80000,
            'status' => 'pending',
        ]);
    }

    public function testRejectsPaymentOnNonDebtDocument(): void
    {
        $admin = $this->makeUser();
        $doc = SaleDocument::create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-FULL-' . uniqid(),
            'subtotal' => 50000,
            'discount_total' => 0,
            'total_price' => 50000,
            'paid_amount' => 50000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'payment_type' => 'full',
            'is_debt' => false,
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->postJson(route('sales.pay-debt', $doc), [
                'amount' => 10000,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('debt_payments', 0);
    }

    public function testRejectsPaymentOnAlreadyPaidDebt(): void
    {
        $admin = $this->makeUser();
        $doc = $this->makeDebtDocument(total: 30000, downPayment: 30000, remaining: 0);
        $doc->update(['status' => 'completed']);

        $this->actingAs($admin)
            ->postJson(route('sales.pay-debt', $doc), [
                'amount' => 5000,
                'payment_method' => 'cash',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('debt_payments', 0);
    }
}