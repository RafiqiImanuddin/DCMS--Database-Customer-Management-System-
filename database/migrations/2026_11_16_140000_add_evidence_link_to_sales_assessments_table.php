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
        if (Schema::hasTable('sales_assessments') && !Schema::hasColumn('sales_assessments', 'evidence_link')) {
            Schema::table('sales_assessments', function (Blueprint $table) {
                $table->text('evidence_link')->nullable()->after('recommendations');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_assessments') && Schema::hasColumn('sales_assessments', 'evidence_link')) {
            Schema::table('sales_assessments', function (Blueprint $table) {
                $table->dropColumn('evidence_link');
            });
        }
    }
};
