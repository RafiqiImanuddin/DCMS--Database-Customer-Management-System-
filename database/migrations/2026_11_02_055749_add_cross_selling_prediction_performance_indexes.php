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
                ['customer_vendor_code', 'item_code', 'posting_date'],
                'cross_sellings_prediction_group_index',
            );
        });

        Schema::table('repeat_order_predictions', function (Blueprint $table) {
            $table->index(
                ['customer_vendor_code', 'item_code'],
                'repeat_order_predictions_customer_item_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_sellings', function (Blueprint $table) {
            $table->dropIndex('cross_sellings_prediction_group_index');
        });

        Schema::table('repeat_order_predictions', function (Blueprint $table) {
            $table->dropIndex('repeat_order_predictions_customer_item_index');
        });
    }
};
