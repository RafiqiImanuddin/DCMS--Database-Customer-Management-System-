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
        Schema::create('repeat_order_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cross_selling_import_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_vendor_code', 100)->index();
            $table->string('customer_vendor_name')->nullable();
            $table->string('no_hp_customer', 50)->nullable()->index();
            $table->string('cabang', 100)->nullable()->index();
            $table->string('item_code', 100)->nullable()->index();
            $table->text('dscription')->nullable();
            $table->date('first_order_date')->nullable();
            $table->date('last_order_date')->nullable()->index();
            $table->unsignedInteger('transaction_count')->default(0);
            $table->decimal('total_quantity', 18, 4)->default(0);
            $table->decimal('total_value', 18, 2)->default(0);
            $table->decimal('average_gap_days', 8, 2)->nullable();
            $table->date('predicted_repeat_date')->nullable()->index();
            $table->unsignedTinyInteger('confidence')->default(0);
            $table->string('classification', 30)->default('baru')->index();
            $table->string('period_label', 7)->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repeat_order_predictions', function (Blueprint $table) {
            Schema::dropIfExists('repeat_order_predictions');
        });
    }
};
