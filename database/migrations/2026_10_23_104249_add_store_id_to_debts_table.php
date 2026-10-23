<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table): void {
            $table->foreignId('store_id')
                ->nullable()
                ->after('debtor_id')
                ->constrained('stores')
                ->restrictOnDelete();
        });

        $storeId = DB::table('stores')->where('code', 'NS-1')->value('id');

        if ($storeId === null) {
            $storeId = DB::table('stores')->insertGetId([
                'name' => 'Optika Glavas NS 1',
                'code' => 'NS-1',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')
            ->where('is_admin', false)
            ->whereNull('store_id')
            ->update(['store_id' => $storeId]);

        DB::table('debtors')
            ->whereNull('store_id')
            ->update(['store_id' => $storeId]);

        DB::table('debts')
            ->whereNull('store_id')
            ->update(['store_id' => $storeId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table): void {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
