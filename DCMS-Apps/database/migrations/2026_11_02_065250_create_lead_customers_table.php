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
        Schema::create('lead_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repeat_order_prediction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_vendor_code', 100)->index();
            $table->string('customer_vendor_name')->nullable();
            $table->string('no_hp_customer', 50)->nullable();
            $table->string('classification', 30)->default('baru')->index();
            $table->string('item_code', 100)->nullable();
            $table->text('dscription')->nullable();
            $table->string('status', 30)->default('follow_up')->index();
            $table->timestamp('assigned_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['user_id', 'repeat_order_prediction_id'], 'lead_customer_prediction_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_customers');
    }
};
