<?php

namespace Database\Seeders;

use App\Models\Debtor;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $store = Store::firstOrCreate(
            ['code' => 'NS-1'],
            [
                'name' => 'Optika Glavas NS 1',
                'is_active' => true,
            ],
        );

        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Prodavac',
            'email' => 'prodavac@example.com',
            'password' => 'password',
            'store_id' => $store->id,
        ]);

        if (app()->environment('local')) {
            $debtor = Debtor::factory()->create([
                'first_name' => 'Milan',
                'last_name' => 'Jovanović',
                'city' => 'Beograd',
                'store_id' => $store->id,
            ]);

            $debt = $debtor->debts()->create([
                'incurred_on' => now()->startOfYear()->toDateString(),
                'original_amount_minor' => 2400000,
                'installment_count' => 12,
                'store_id' => $store->id,
                'created_by' => $admin->id,
            ]);

            $debt->payments()->create([
                'amount_minor' => 200000,
                'paid_on' => now()->startOfYear()->addDays(7)->toDateString(),
                'note' => 'Probna uplata',
                'created_by' => $admin->id,
                'submission_token' => (string) Str::uuid(),
            ]);
        }
    }
}
