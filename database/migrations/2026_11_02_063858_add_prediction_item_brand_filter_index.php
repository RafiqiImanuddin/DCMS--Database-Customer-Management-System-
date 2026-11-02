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
            $table->index(
                ['customer_vendor_code', 'item_code', 'brand', 'cross_selling_import_id'],
                'cross_sellings_prediction_brand_filter_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_sellings', function (Blueprint $table) {
            $table->dropIndex('cross_sellings_prediction_brand_filter_index');
        });
    }
};
