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
        Schema::create('sosmed_keeps', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('message_id', 100)->nullable()->index();
            $table->dateTime('keep_timestamp')->nullable()->index();
            $table->string('nomor_sender', 35)->nullable();
            $table->string('grup_id', 120)->nullable();
            $table->string('sender', 120)->nullable();
            $table->string('grup', 150)->nullable();
            $table->string('jenis_chat', 20)->default('GRUP'); // GRUP / PRIBADI
            $table->string('nama', 150)->nullable();
            $table->string('nomor_wa', 35)->nullable()->default('')->index();
            $table->text('barang_keep')->nullable();
            $table->string('tanggal_ambil', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->string('remarks', 120)->nullable()->index();
            $table->string('kode_sosmed', 60)->nullable()->index();
            $table->string('report_ar', 50)->nullable()->index();
            $table->dateTime('tanggal_input_ar')->nullable();
            $table->string('ar_vlookup', 50)->nullable();
            $table->string('tanggal_vlookup', 50)->nullable();
            $table->string('cabang', 50)->nullable()->index();
            $table->text('evidence')->nullable();
            $table->string('nomor_follow_up', 35)->nullable();
            $table->string('nama_tim_follow_up', 120)->nullable();
            $table->string('status', 50)->nullable()->index();
            $table->text('feedback_tim_fu')->nullable();
            $table->string('nama_toko_kompetitor', 150)->nullable();
            $table->string('user_kredit', 50)->nullable();
            $table->string('periode', 35)->nullable()->index();
            $table->string('item', 150)->nullable();
            $table->string('nomor_fu', 50)->nullable()->index(); // Kolom AC (PIC FU dari Bot VPS)
            $table->timestamps();

            // Compound index for quick lookups
            $table->index(['cabang', 'periode']);
            $table->index(['status', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sosmed_keeps');
    }
};
