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
        Schema::create('cross_selling_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_path')->nullable();
            $table->string('batch_label')->nullable()->index();
            $table->date('period_start')->nullable()->index();
            $table->date('period_end')->nullable()->index();
            $table->string('status')->default('processing')->index();
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->longText('error_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_selling_imports', function (Blueprint $table) {
            Schema::dropIfExists('cross_selling_imports');
        });
    }
};
