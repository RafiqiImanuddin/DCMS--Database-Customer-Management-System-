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
        if (! Schema::hasTable('sosmed_keeps')) {
            return;
        }

        Schema::table('sosmed_keeps', function (Blueprint $table) {
            // Index untuk nama PIC Sales Follow Up (Tab 10 Leaderboard PIC)
            if (Schema::hasColumn('sosmed_keeps', 'nama_tim_follow_up')) {
                $table->index('nama_tim_follow_up', 'idx_sk_nama_tim_fu');
            }

            // Compound index untuk filter Cabang + Status (Tab 10 Donut Chart & Keep List)
            $table->index(['cabang', 'status'], 'idx_sk_cabang_status');

            // Compound index untuk tracking history customer
            $table->index(['nomor_wa', 'keep_timestamp'], 'idx_sk_nomor_wa_timestamp');

            // Compound index untuk filter antrean Auto FU H+1 harian
            if (Schema::hasColumn('sosmed_keeps', 'fu_h1_status')) {
                $table->index(['fu_h1_status', 'keep_timestamp'], 'idx_sk_fu_h1_status_ts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sosmed_keeps')) {
            return;
        }

        Schema::table('sosmed_keeps', function (Blueprint $table) {
            $table->dropIndex('idx_sk_nama_tim_fu');
            $table->dropIndex('idx_sk_cabang_status');
            $table->dropIndex('idx_sk_nomor_wa_timestamp');
            $table->dropIndex('idx_sk_fu_h1_status_ts');
        });
    }
};
