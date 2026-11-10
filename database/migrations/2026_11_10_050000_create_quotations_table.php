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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('quotation_number')->unique();
            $table->string('customer_name');
            $table->string('customer_destination')->default('di-tempat');
            $table->date('letter_date');
            $table->string('letter_city')->default('Banjarmasin');
            $table->text('greeting_text')->nullable();
            $table->json('items');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->text('notes')->nullable();
            $table->text('closing_text')->nullable();
            $table->string('signer_name')->default('RAFIQI IMANUDDIN');
            $table->string('signer_title')->default('Marketing Gadget Mart');
            $table->boolean('include_signature')->default(true);
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['letter_date', 'customer_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
