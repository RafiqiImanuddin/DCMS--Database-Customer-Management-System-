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
            $table->foreignId('cross_selling_import_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->date('period_start')->nullable()->after('posting_date')->index();
            $table->date('period_end')->nullable()->after('period_start')->index();
            $table->string('period_label', 7)->nullable()->after('period_end')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cross_sellings', function (Blueprint $table) {
            $table->dropForeign(['cross_selling_import_id']);
            $table->dropIndex(['period_start']);
            $table->dropIndex(['period_end']);
            $table->dropIndex(['period_label']);
            $table->dropColumn([
                'cross_selling_import_id',
                'period_start',
                'period_end',
                'period_label',
            ]);
        });
    }
};
