<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_debtor_records_or_record_payment(): void
    {
        $this->get(route('debtors.index'))->assertRedirect(route('login'));

        $this->post('/duznici/1/uplate', [
            'debt_id' => 1,
            'amount' => '100,00',
            'paid_on' => now()->toDateString(),
            'submission_token' => 'guest-token',
        ])->assertRedirect(route('login'));
    }

    public function test_vendor_can_access_work_area_but_not_admin_area(): void
    {
        $vendor = User::factory()->create(['is_admin' => false]);

        $this->actingAs($vendor)->get('/prodavac')->assertOk();
        $this->actingAs($vendor)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Admin panel');
    }

    public function test_user_can_log_in_and_is_redirected_to_dashboard(): void
    {
        $vendor = User::factory()->create([
            'email' => 'vendor@example.com',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'vendor@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($vendor);
    }

    public function test_login_page_uses_oko_glavas_branding(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('OKO Glavas');
    }

    public function test_vendor_cannot_view_a_debtor_from_another_store(): void
    {
        $storeOne = Store::factory()->create(['code' => 'NS-2']);
        $storeTwo = Store::factory()->create(['code' => 'NS-3']);
        $vendor = User::factory()->create(['is_admin' => false, 'store_id' => $storeOne->id]);
        $debtor = Debtor::factory()->create(['store_id' => $storeTwo->id, 'first_name' => 'DrugaOptika']);
        Debt::factory()->for($debtor)->create(['store_id' => $storeTwo->id, 'incurred_on' => '2026-01-01']);

        $this->actingAs($vendor)->get(route('debtors.show', $debtor))->assertNotFound();
    }
}
