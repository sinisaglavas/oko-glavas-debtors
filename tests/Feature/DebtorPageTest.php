<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_debtor_page_grouped_by_year(): void
    {
        $user = User::factory()->create();
        $debtor = Debtor::factory()->create(['first_name' => 'Milan', 'last_name' => 'Jovanović']);
        Debt::factory()->for($debtor)->create(['incurred_on' => '2025-06-01', 'original_amount_minor' => 100000]);
        Debt::factory()->for($debtor)->create(['incurred_on' => '2026-02-01', 'original_amount_minor' => 250000]);

        $response = $this->actingAs($user)->get(route('debtors.show', ['debtor' => $debtor, 'year' => 2026]));

        $response->assertOk()
            ->assertSee('Milan Jovanović')
            ->assertSee('2025')
            ->assertSee('2026')
            ->assertSee('Rata 1')
            ->assertSee('Otvorena')
            ->assertSee('3.500,00 RSD');
    }

    public function test_vendor_can_record_payment_only_for_debtors_debt(): void
    {
        $vendor = User::factory()->create(['is_admin' => false]);
        $debtor = Debtor::factory()->create();
        $debt = Debt::factory()->for($debtor)->create(['incurred_on' => '2026-01-01', 'original_amount_minor' => 2400000]);

        $response = $this->actingAs($vendor)->post(route('debtors.payments.store', $debtor), [
            'debt_id' => $debt->id,
            'amount' => '5000,00',
            'paid_on' => '2026-01-15',
            'note' => 'Gotovina',
            'submission_token' => 'page-payment-1',
        ]);

        $response->assertRedirect(route('debtors.show', ['debtor' => $debtor, 'year' => 2026]));
        $this->assertDatabaseHas('payments', ['debt_id' => $debt->id, 'amount_minor' => 500000, 'created_by' => $vendor->id]);
    }

    public function test_vendor_cannot_void_payment_but_admin_can(): void
    {
        $vendor = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);
        $debtor = Debtor::factory()->create();
        $debt = Debt::factory()->for($debtor)->create(['incurred_on' => '2026-01-01', 'original_amount_minor' => 100000]);
        $payment = Payment::factory()->for($debt)->create(['amount_minor' => 25000, 'created_by' => $vendor->id]);

        $this->actingAs($vendor)->post(route('debtors.payments.void', $debtor), [
            'debt_id' => $debt->id,
            'payment_id' => $payment->id,
            'reason' => 'Greška',
        ])->assertForbidden();

        $this->actingAs($admin)->post(route('debtors.payments.void', $debtor), [
            'debt_id' => $debt->id,
            'payment_id' => $payment->id,
            'reason' => 'Greška pri unosu',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'voided_by' => $admin->id, 'void_reason' => 'Greška pri unosu']);
    }

    public function test_payment_cannot_be_recorded_for_another_debtors_debt(): void
    {
        $user = User::factory()->create();
        $debtor = Debtor::factory()->create();
        $otherDebt = Debt::factory()->create(['incurred_on' => '2026-01-01', 'original_amount_minor' => 100000]);

        $this->actingAs($user)->post(route('debtors.payments.store', $debtor), [
            'debt_id' => $otherDebt->id,
            'amount' => '100,00',
            'paid_on' => '2026-01-02',
            'submission_token' => 'wrong-debt',
        ])->assertNotFound();

        $this->assertDatabaseCount('payments', 0);
    }
}
