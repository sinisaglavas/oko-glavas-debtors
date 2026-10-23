<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_and_user_management(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin panel');
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee('Korisnici');
        $this->actingAs($admin)->get(route('admin.stores.index'))->assertOk()->assertSee('Optike');
        $this->actingAs($admin)->get(route('admin.stores.create'))->assertOk()->assertSee('Nova optika');
        $this->actingAs($admin)->get(route('admin.audit'))->assertOk()->assertSee('Audit log');
    }

    public function test_vendor_cannot_access_admin_panel(): void
    {
        $vendor = User::factory()->create(['is_admin' => false]);

        $this->actingAs($vendor)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($vendor)->post(route('admin.users.store'), [])->assertForbidden();
        $this->actingAs($vendor)->get(route('admin.stores.index'))->assertForbidden();
    }

    public function test_admin_can_create_and_deactivate_a_vendor_with_audit_entries(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $store = Store::factory()->create(['code' => 'NS-2']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Nova prodaja',
            'email' => 'nova@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => '0',
            'store_id' => $store->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user = User::query()->where('email', 'nova@example.com')->firstOrFail();
        $this->assertTrue($user->is_active);
        $this->assertSame($store->id, $user->store_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created', 'auditable_id' => $user->id]);

        $this->actingAs($admin)->post(route('admin.users.toggle', $user))->assertRedirect();
        $this->assertFalse($user->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated', 'auditable_id' => $user->id]);
    }

    public function test_admin_can_create_and_deactivate_a_store_with_audit_entries(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.stores.store'), [
            'name' => 'Optika Glavas Futog',
            'code' => 'FUT-1',
            'city' => 'Futog',
        ]);

        $response->assertRedirect(route('admin.stores.index'));
        $store = Store::query()->where('code', 'FUT-1')->firstOrFail();
        $this->assertTrue($store->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'store.created', 'auditable_id' => $store->id]);

        $this->actingAs($admin)->post(route('admin.stores.toggle', $store))->assertRedirect();
        $this->assertFalse($store->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'store.deactivated', 'auditable_id' => $store->id]);
    }

    public function test_payment_actions_are_written_to_audit_log(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $debtor = Debtor::factory()->create();
        $debt = Debt::factory()->for($debtor)->create(['original_amount_minor' => 100000, 'incurred_on' => '2026-01-01']);

        $this->actingAs($admin)->post(route('debtors.payments.store', $debtor), [
            'debt_id' => $debt->id,
            'amount' => '100,00',
            'paid_on' => '2026-01-02',
            'submission_token' => 'admin-audit-payment',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.created']);
        $this->assertSame(1, AuditLog::query()->where('action', 'payment.created')->count());
    }

    public function test_admin_can_update_debtor_contact_data_and_change_is_logged(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $debtor = Debtor::factory()->create(['city' => 'Novi Sad']);

        $this->actingAs($admin)->put(route('admin.debtors.update', $debtor), [
            'first_name' => $debtor->first_name,
            'last_name' => $debtor->last_name,
            'address' => 'Nova 12',
            'city' => 'Beograd',
            'phone' => $debtor->phone,
        ])->assertRedirect(route('admin.debtors.index'));

        $this->assertDatabaseHas('debtors', ['id' => $debtor->id, 'address' => 'Nova 12', 'city' => 'Beograd']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'debtor.updated', 'auditable_id' => $debtor->id]);
    }

    public function test_audit_log_renders_nested_change_details(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        AuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'debtor.updated',
            'metadata' => ['before' => ['city' => 'Novi Sad'], 'after' => ['city' => 'Beograd']],
        ]);

        $this->actingAs($admin)->get(route('admin.audit'))
            ->assertOk()
            ->assertSee('Novi Sad')
            ->assertSee('Beograd');
    }
}
