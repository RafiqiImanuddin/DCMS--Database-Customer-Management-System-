<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE settings MODIFY COLUMN value LONGTEXT NULL');
            } elseif ($driver !== 'sqlite') {
                Schema::table('settings', function (Blueprint $table) {
                    $table->longText('value')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE settings MODIFY COLUMN value TEXT NULL');
            } elseif ($driver !== 'sqlite') {
                Schema::table('settings', function (Blueprint $table) {
                    $table->text('value')->nullable()->change();
                });
            }
        }
    }
};
