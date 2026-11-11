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
        Schema::create('sosmed_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('admin_name');
            $table->date('shift_date')->index();
            $table->string('shift_type', 30); // pagi, siang, lembur, off
            $table->string('start_time', 10)->nullable(); // 09:00, 14:30
            $table->string('end_time', 10)->nullable();   // 16:30, 22:00
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['shift_date', 'shift_type']);
            $table->index(['admin_name', 'shift_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sosmed_shifts');
    }
};
