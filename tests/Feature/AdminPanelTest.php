<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Debt;
use App\Models\Debtor;
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
        $this->actingAs($admin)->get(route('admin.audit'))->assertOk()->assertSee('Audit log');
    }

    public function test_vendor_cannot_access_admin_panel(): void
    {
        $vendor = User::factory()->create(['is_admin' => false]);

        $this->actingAs($vendor)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($vendor)->post(route('admin.users.store'), [])->assertForbidden();
    }

    public function test_admin_can_create_and_deactivate_a_vendor_with_audit_entries(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Nova prodaja',
            'email' => 'nova@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => '0',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user = User::query()->where('email', 'nova@example.com')->firstOrFail();
        $this->assertTrue($user->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created', 'auditable_id' => $user->id]);

        $this->actingAs($admin)->post(route('admin.users.toggle', $user))->assertRedirect();
        $this->assertFalse($user->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated', 'auditable_id' => $user->id]);
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
}
