<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cross_sellings', function (Blueprint $table) {
            $table->index(['brand', 'customer_vendor_code'], 'cross_sellings_brand_customer_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_sellings', function (Blueprint $table) {
            $table->dropIndex('cross_sellings_brand_customer_index');
        });
    }
};
