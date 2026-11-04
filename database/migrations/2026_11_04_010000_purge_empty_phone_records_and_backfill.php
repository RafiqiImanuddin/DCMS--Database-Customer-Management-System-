<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Purge all records without a WhatsApp phone number to lighten the workload & database size
        if (Schema::hasTable('repeat_order_predictions')) {
            DB::table('repeat_order_predictions')
                ->where(function ($q) {
                    $q->whereNull('no_hp_customer')->orWhereRaw("TRIM(no_hp_customer) = ''");
                })
                ->delete();
        }

        if (Schema::hasTable('cross_sellings')) {
            DB::table('cross_sellings')
                ->where(function ($q) {
                    $q->whereNull('no_hp_customer')->orWhereRaw("TRIM(no_hp_customer) = ''");
                })
                ->delete();
        }

        if (Schema::hasTable('lead_customers')) {
            DB::table('lead_customers')
                ->where(function ($q) {
                    $q->whereNull('no_hp_customer')->orWhereRaw("TRIM(no_hp_customer) = ''");
                })
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Purged empty phone records cannot be restored
    }
};
