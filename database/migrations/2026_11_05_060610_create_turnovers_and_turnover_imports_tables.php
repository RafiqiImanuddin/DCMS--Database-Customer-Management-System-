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
        if (! Schema::hasTable('turnover_imports')) {
            Schema::create('turnover_imports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('original_name');
                $table->string('stored_path');
                $table->string('batch_label')->nullable()->index();
                $table->date('period_start')->nullable()->index();
                $table->date('period_end')->nullable()->index();
                $table->string('status')->default('processing')->index();
                $table->integer('row_count')->default(0);
                $table->integer('success_count')->default(0);
                $table->integer('failed_count')->default(0);
                $table->text('error_log')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('turnovers')) {
            Schema::create('turnovers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('turnover_import_id')->nullable()->constrained('turnover_imports')->nullOnDelete();
                $table->string('cabang', 100)->nullable()->index();
                $table->date('posting_date')->index();
                $table->string('document_number', 100)->index();
                $table->string('slp_code', 50)->nullable();
                $table->string('slp_name', 255)->index();
                $table->text('remarks')->nullable();
                $table->string('sales_cabang', 100)->nullable();
                $table->decimal('dpp', 18, 2)->default(0);
                $table->decimal('gross_profit', 18, 2)->default(0);
                $table->decimal('total', 18, 2)->default(0);
                $table->string('period_label', 7)->nullable()->index(); // e.g. '2026-11'
                $table->string('cleaning_hash', 64)->nullable()->index();
                $table->timestamps();

                $table->index(['posting_date', 'slp_name']);
                $table->index(['document_number', 'posting_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnovers');
        Schema::dropIfExists('turnover_imports');
    }
};
