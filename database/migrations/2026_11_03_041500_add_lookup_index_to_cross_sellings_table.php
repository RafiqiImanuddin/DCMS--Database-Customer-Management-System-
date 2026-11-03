<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS idx_cross_sellings_lookup ON cross_sellings (customer_vendor_code, item_code, brand);');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_cross_sellings_lookup;');
    }
};

