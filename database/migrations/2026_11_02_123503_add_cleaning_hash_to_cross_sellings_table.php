<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cross_sellings', function (Blueprint $table) {
            $table->string('cleaning_hash', 64)->nullable()->after('source_row');
            $table->unique(['cross_selling_import_id', 'cleaning_hash'], 'cross_selling_import_cleaning_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cross_sellings', function (Blueprint $table) {
            $table->dropUnique('cross_selling_import_cleaning_hash_unique');
            $table->dropColumn('cleaning_hash');
        });
    }
};
