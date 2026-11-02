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
        Schema::create('cross_sellings', function (Blueprint $table) {
            $table->id();
            $table->string('cabang', 100)->nullable();
            $table->text('info')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('group')->nullable();
            $table->date('posting_date')->nullable()->index();
            $table->string('waktu', 20)->nullable();
            $table->string('document_number', 100)->nullable()->index();
            $table->string('customer_vendor_code', 100)->nullable()->index();
            $table->string('customer_vendor_name')->nullable();
            $table->string('item_code', 100)->nullable()->index();
            $table->text('dscription')->nullable();
            $table->string('itms_grp_nam')->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('whs_code', 100)->nullable();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('harga_jual', 18, 2)->default(0);
            $table->decimal('harga_sebelum_potongan', 18, 2)->default(0);
            $table->decimal('selisih_total_potongan', 18, 2)->default(0);
            $table->decimal('p_bonus', 18, 2)->default(0);
            $table->decimal('p_voucher', 18, 2)->default(0);
            $table->decimal('p_nego', 18, 2)->default(0);
            $table->decimal('p_leasing', 18, 2)->default(0);
            $table->decimal('p_program', 18, 2)->default(0);
            $table->decimal('p_edc', 18, 2)->default(0);
            $table->decimal('p_otorisasi_posvit', 18, 2)->default(0);
            $table->string('price_list')->nullable();
            $table->text('remarks')->nullable();
            $table->string('sc_hl_code')->nullable();
            $table->string('slp_name')->nullable();
            $table->text('promo')->nullable();
            $table->string('user_input')->nullable();
            $table->string('no_hp_customer', 50)->nullable();
            $table->decimal('harga_modal', 18, 2)->default(0);
            $table->decimal('gross_profit', 18, 2)->default(0);
            $table->string('reseller')->nullable();
            $table->decimal('beban_subsidi_leasing', 18, 2)->default(0);
            $table->string('transaksi_customer')->nullable();
            $table->string('name')->nullable();
            $table->string('no_reff', 100)->nullable();
            $table->string('no_po', 100)->nullable();
            $table->string('source_file')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cross_sellings');
    }
};
