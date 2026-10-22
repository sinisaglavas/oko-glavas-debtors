<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\Debtor;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnualOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_annual_overview_shows_whole_year_totals_and_search_results(): void
    {
        $vendor = User::factory()->create();
        $debtor = Debtor::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Petrović',
            'phone' => '+38164111222',
        ]);
        $debt = Debt::factory()->for($debtor)->create([
            'incurred_on' => '2026-03-10',
            'original_amount_minor' => 2400000,
        ]);
        app(PaymentService::class)->record($debt, 500000, CarbonImmutable::parse('2026-03-20'), null, $vendor, 'annual-payment');

        $response = $this->actingAs($vendor)->get(route('debtors.index', ['year' => 2026, 'search' => 'Ana']));

        $response->assertOk();
        $response->assertSee('24.000,00 RSD');
        $response->assertSee('5.000,00 RSD');
        $response->assertSee('19.000,00 RSD');
        $response->assertSee('Ana Petrović');
    }

    public function test_status_filter_shows_only_settled_debtors(): void
    {
        $vendor = User::factory()->create();
        $settled = Debt::factory()->for(Debtor::factory()->create(['first_name' => 'Izmiren']))->create([
            'incurred_on' => '2026-01-01',
            'original_amount_minor' => 100000,
        ]);
        app(PaymentService::class)->record($settled, 100000, CarbonImmutable::parse('2026-01-02'), null, $vendor, 'settled-filter');
        Debt::factory()->for(Debtor::factory()->create(['first_name' => 'Aktivan']))->create([
            'incurred_on' => '2026-01-01',
            'original_amount_minor' => 100000,
        ]);

        $response = $this->actingAs($vendor)->get(route('debtors.index', ['year' => 2026, 'status' => 'settled']));

        $response->assertOk();
        $response->assertSee('Izmiren');
        $response->assertDontSee('Aktivan');
    }

    public function test_vendor_can_create_a_new_debtor_and_debt(): void
    {
        $vendor = User::factory()->create();

        $response = $this->actingAs($vendor)->post(route('debtors.store'), [
            'first_name' => 'Nikola',
            'last_name' => 'Nikolić',
            'address' => 'Glavna 1',
            'city' => 'Novi Sad',
            'phone' => '+381641234567',
            'incurred_on' => '2026-04-01',
            'amount' => '24000,00',
            'installment_count' => 12,
        ]);

        $response->assertRedirect(route('debtors.index', ['year' => 2026]));
        $this->assertDatabaseHas('debtors', ['first_name' => 'Nikola', 'last_name' => 'Nikolić']);
        $this->assertDatabaseHas('debts', ['original_amount_minor' => 2400000, 'installment_count' => 12, 'created_by' => $vendor->id]);
    }

    public function test_existing_debtor_can_receive_a_new_debt(): void
    {
        $vendor = User::factory()->create();
        $debtor = Debtor::factory()->create();

        $response = $this->actingAs($vendor)->post(route('debtors.store'), [
            'debtor_id' => $debtor->id,
            'incurred_on' => '2026-05-01',
            'amount' => '10000',
            'installment_count' => 3,
        ]);

        $response->assertRedirect(route('debtors.index', ['year' => 2026]));
        $this->assertDatabaseHas('debts', ['debtor_id' => $debtor->id, 'original_amount_minor' => 1000000]);
    }

    public function test_invalid_installment_count_is_rejected(): void
    {
        $vendor = User::factory()->create();

        $response = $this->actingAs($vendor)->post(route('debtors.store'), [
            'first_name' => 'Test',
            'last_name' => 'Korisnik',
            'address' => 'Adresa',
            'city' => 'Beograd',
            'phone' => '0601234567',
            'incurred_on' => '2026-05-01',
            'amount' => '10000',
            'installment_count' => 25,
        ]);

        $response->assertSessionHasErrors('installment_count');
        $this->assertDatabaseCount('debtors', 0);
    }
}
