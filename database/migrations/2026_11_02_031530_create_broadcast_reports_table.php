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
        Schema::create('broadcast_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repeat_order_prediction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_vendor_code', 100)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('channel', 30)->default('whatsapp')->index();
            $table->text('message');
            $table->string('status', 30)->default('pending')->index();
            $table->text('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_reports', function (Blueprint $table) {
            Schema::dropIfExists('broadcast_reports');
        });
    }
};
