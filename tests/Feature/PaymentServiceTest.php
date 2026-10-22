<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\Debtor;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_updates_paid_and_outstanding_amounts(): void
    {
        $vendor = User::factory()->create();
        $debt = Debt::factory()->for(Debtor::factory())->create([
            'incurred_on' => '2026-01-01',
            'original_amount_minor' => 2400000,
            'installment_count' => 12,
        ]);

        $payment = app(PaymentService::class)->record($debt, 500000, CarbonImmutable::parse('2026-01-15'), 'Prva uplata', $vendor, 'payment-1');

        $debt->refresh();

        $this->assertModelExists($payment);
        $this->assertSame(500000, $debt->paidAmountMinor());
        $this->assertSame(1900000, $debt->outstandingAmountMinor());
        $this->assertFalse($debt->isSettled());
    }

    public function test_duplicate_submission_token_is_idempotent(): void
    {
        $vendor = User::factory()->create();
        $debt = Debt::factory()->create(['incurred_on' => '2026-01-01', 'original_amount_minor' => 100000]);
        $service = app(PaymentService::class);

        $first = $service->record($debt, 25000, CarbonImmutable::parse('2026-01-02'), null, $vendor, 'same-token');
        $second = $service->record($debt, 25000, CarbonImmutable::parse('2026-01-02'), null, $vendor, 'same-token');

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_payment_cannot_exceed_outstanding_balance(): void
    {
        $vendor = User::factory()->create();
        $debt = Debt::factory()->create(['incurred_on' => '2026-01-01', 'original_amount_minor' => 100000]);

        $this->expectException(ValidationException::class);

        app(PaymentService::class)->record($debt, 100001, CarbonImmutable::parse('2026-01-02'), null, $vendor, 'too-much');
    }

    public function test_only_admin_can_void_a_payment(): void
    {
        $vendor = User::factory()->create(['is_admin' => false]);
        $debt = Debt::factory()->create(['incurred_on' => '2026-01-01', 'original_amount_minor' => 100000]);
        $payment = app(PaymentService::class)->record($debt, 25000, CarbonImmutable::parse('2026-01-02'), null, $vendor, 'void-test');

        $this->expectException(AuthorizationException::class);

        app(PaymentService::class)->void($payment, $vendor, 'Pogrešan unos');
    }

    public function test_admin_can_void_a_payment_and_balance_reopens(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $debt = Debt::factory()->create(['incurred_on' => '2026-01-01', 'original_amount_minor' => 100000]);
        $payment = app(PaymentService::class)->record($debt, 25000, CarbonImmutable::parse('2026-01-02'), null, $admin, 'void-admin');

        app(PaymentService::class)->void($payment, $admin, 'Pogrešan unos');

        $this->assertSame(0, $debt->fresh()->paidAmountMinor());
        $this->assertSame(100000, $debt->fresh()->outstandingAmountMinor());
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'void_reason' => 'Pogrešan unos']);
    }
}
