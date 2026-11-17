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
        Schema::table('sosmed_keeps', function (Blueprint $table) {
            if (! Schema::hasColumn('sosmed_keeps', 'fu_h1_sent_at')) {
                $table->dateTime('fu_h1_sent_at')->nullable()->index()->after('nomor_fu');
            }
            if (! Schema::hasColumn('sosmed_keeps', 'fu_h1_status')) {
                $table->string('fu_h1_status', 30)->nullable()->default('PENDING')->index()->after('fu_h1_sent_at');
            }
            if (! Schema::hasColumn('sosmed_keeps', 'fu_h1_note')) {
                $table->text('fu_h1_note')->nullable()->after('fu_h1_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sosmed_keeps', function (Blueprint $table) {
            $table->dropColumn(['fu_h1_sent_at', 'fu_h1_status', 'fu_h1_note']);
        });
    }
};
