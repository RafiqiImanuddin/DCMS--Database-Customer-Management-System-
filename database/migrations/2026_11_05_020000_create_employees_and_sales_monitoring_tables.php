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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 50)->unique();
            $table->string('name');
            $table->string('role')->default('Admin Sales');
            $table->string('cabang', 50)->index();
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive
            $table->date('joined_date')->nullable();
            $table->string('avatar_color', 20)->default('#2563eb');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 2)->default(20.00); // persentase bobot
            $table->string('target_benchmark')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sales_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('period', 10)->index(); // YYYY-MM
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->string('grade', 2)->default('C'); // A, B, C, D
            $table->json('scores')->nullable(); // detail nilai per kriteria code
            $table->string('evaluator_name')->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period']);
        });

        // Seed default KPI criteria
        $now = now();
        DB::table('kpi_criteria')->insert([
            [
                'code' => 'resp_time',
                'name' => 'Kecepatan Respon & Follow-Up',
                'description' => 'Kecepatan membalas chat calon pembeli dan menindaklanjuti pesan broadcast/leads.',
                'weight' => 25.00,
                'target_benchmark' => 'Rata-rata respon < 5 menit',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'broadcast_vol',
                'name' => 'Pencapaian Broadcast & Outreach',
                'description' => 'Kepatuhan kuota harian broadcast WhatsApp dan volume pelanggan yang dijangkau.',
                'weight' => 20.00,
                'target_benchmark' => 'Min. 500 kontak terjangkau/bulan',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'closing_rate',
                'name' => 'Tingkat Konversi & Closing Leads',
                'description' => 'Persentase customer dari repeat order prediction / leads yang sukses belanja ulang.',
                'weight' => 25.00,
                'target_benchmark' => 'Tingkat konversi leads > 15%',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'crm_discipline',
                'name' => 'Ketertiban CRM & Format Keep',
                'description' => 'Kedisiplinan input format keep, update status klaim/tracking AR, dan validitas nomor.',
                'weight' => 15.00,
                'target_benchmark' => '100% data terisi rapi & tepat waktu',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'csat_quality',
                'name' => 'Kualitas Layanan & Etika Chat',
                'description' => 'Keramahan, kesopanan bahasa, ketepatan solusi promo, dan kepuasan pelanggan.',
                'weight' => 15.00,
                'target_benchmark' => 'Rating kepuasan pelanggan > 4.5/5',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Seed default Gadgetmart sales employees
        $defaultEmployees = [
            [
                'nik' => 'SLS-HO-01',
                'name' => 'Siti Rahmawati',
                'role' => 'Admin Sales',
                'cabang' => 'Head Office',
                'phone' => '081250112233',
                'email' => 'siti.ho@gadgetmart.id',
                'status' => 'active',
                'joined_date' => '2024-01-15',
                'avatar_color' => '#2563eb',
                'notes' => 'Fokus penanganan broadcast & follow-up pelanggan Head Office.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nik' => 'SLS-HO-02',
                'name' => 'Muhammad Rizky Pratama',
                'role' => 'Admin Sales',
                'cabang' => 'Head Office',
                'phone' => '085248991122',
                'email' => 'rizky.ho@gadgetmart.id',
                'status' => 'active',
                'joined_date' => '2024-03-01',
                'avatar_color' => '#059669',
                'notes' => 'Spesialis follow-up broadcast customer repeat order area Banjarmasin & sekitar.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nik' => 'SLS-HO-03',
                'name' => 'Nadia Safitri',
                'role' => 'Admin Sales',
                'cabang' => 'Head Office',
                'phone' => '082155447788',
                'email' => 'nadia.ho@gadgetmart.id',
                'status' => 'active',
                'joined_date' => '2023-11-10',
                'avatar_color' => '#7c3aed',
                'notes' => 'Tingkat closing aksesoris dan add-on warranty tinggi.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nik' => 'SLS-HO-04',
                'name' => 'Ahmad Fauzi',
                'role' => 'Admin Sales',
                'cabang' => 'Head Office',
                'phone' => '081349887766',
                'email' => 'fauzi.ho@gadgetmart.id',
                'status' => 'active',
                'joined_date' => '2024-05-20',
                'avatar_color' => '#d97706',
                'notes' => 'Aktif koordinasi broadcast promo cicilan 0% dan tukar tambah.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nik' => 'SLS-HO-05',
                'name' => 'Dewi Anggraini',
                'role' => 'Admin Sales',
                'cabang' => 'Head Office',
                'phone' => '087811224455',
                'email' => 'dewi.ho@gadgetmart.id',
                'status' => 'active',
                'joined_date' => '2024-02-18',
                'avatar_color' => '#db2777',
                'notes' => 'Responsif terhadap pertanyaan customer WhatsApp dan keep order.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nik' => 'SLS-HO-06',
                'name' => 'Budi Santoso',
                'role' => 'Admin Sales',
                'cabang' => 'Head Office',
                'phone' => '081955331199',
                'email' => 'budi.ho@gadgetmart.id',
                'status' => 'active',
                'joined_date' => '2024-06-01',
                'avatar_color' => '#0891b2',
                'notes' => 'Mengelola database customer baru dan follow-up omnichannel.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('employees')->insert($defaultEmployees);

        // Seed initial assessment sample for current period (2026-11)
        $employeeIds = DB::table('employees')->pluck('id', 'nik');
        $currentPeriod = '2026-11';

        $assessments = [
            [
                'employee_id' => $employeeIds['SLS-AMTC-01'] ?? 1,
                'period' => $currentPeriod,
                'final_score' => 93.50,
                'grade' => 'A',
                'scores' => json_encode([
                    'resp_time' => 95,
                    'broadcast_vol' => 90,
                    'closing_rate' => 96,
                    'crm_discipline' => 92,
                    'csat_quality' => 94,
                ]),
                'evaluator_name' => 'Manager Sales',
                'notes' => 'Performa sangat luar biasa! Respon cepat dan tingkat closing leads tertinggi bulan ini.',
                'recommendations' => 'Pertahankan konsistensi dan bagikan tips closing ke rekan admin lainnya.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeIds['SLS-BJMA-02'] ?? 2,
                'period' => $currentPeriod,
                'final_score' => 88.20,
                'grade' => 'B',
                'scores' => json_encode([
                    'resp_time' => 88,
                    'broadcast_vol' => 95,
                    'closing_rate' => 84,
                    'crm_discipline' => 90,
                    'csat_quality' => 86,
                ]),
                'evaluator_name' => 'Manager Sales',
                'notes' => 'Volume broadcast sangat tinggi dan disiplin. Perlu sedikit peningkatan pada follow-up leads hangat.',
                'recommendations' => 'Tingkatkan penawaran bundling aksesoris saat customer merespon promo.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeIds['SLS-BJMC-03'] ?? 3,
                'period' => $currentPeriod,
                'final_score' => 91.00,
                'grade' => 'A',
                'scores' => json_encode([
                    'resp_time' => 92,
                    'broadcast_vol' => 88,
                    'closing_rate' => 94,
                    'crm_discipline' => 88,
                    'csat_quality' => 95,
                ]),
                'evaluator_name' => 'Manager Sales',
                'notes' => 'Komunikasi sangat ramah dan pelanggan memberikan ulasan positif. Penjualan stabil.',
                'recommendations' => 'Targetkan peningkatan kuota broadcast di minggu ke-3.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeIds['SLS-BDM3-04'] ?? 4,
                'period' => $currentPeriod,
                'final_score' => 76.80,
                'grade' => 'C',
                'scores' => json_encode([
                    'resp_time' => 72,
                    'broadcast_vol' => 80,
                    'closing_rate' => 75,
                    'crm_discipline' => 82,
                    'csat_quality' => 78,
                ]),
                'evaluator_name' => 'Manager Sales',
                'notes' => 'Kecepatan respon perlu ditingkatkan saat jam sibuk siang hari. Ada beberapa chat yang tertunda > 15 menit.',
                'recommendations' => 'Gunakan quick-reply dan notifikasi browser agar chat masuk langsung tertangani.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeIds['SLS-BJB2-05'] ?? 5,
                'period' => $currentPeriod,
                'final_score' => 86.50,
                'grade' => 'B',
                'scores' => json_encode([
                    'resp_time' => 86,
                    'broadcast_vol' => 85,
                    'closing_rate' => 88,
                    'crm_discipline' => 85,
                    'csat_quality' => 90,
                ]),
                'evaluator_name' => 'Manager Sales',
                'notes' => 'Performa solid dan stabil. Pelanggan merasa puas dengan penjelasan spesifikasi produk.',
                'recommendations' => 'Dapat diberikan kesempatan menangani database customer VIP / corporate.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'employee_id' => $employeeIds['SLS-TJGC-06'] ?? 6,
                'period' => $currentPeriod,
                'final_score' => 82.40,
                'grade' => 'B',
                'scores' => json_encode([
                    'resp_time' => 80,
                    'broadcast_vol' => 82,
                    'closing_rate' => 85,
                    'crm_discipline' => 84,
                    'csat_quality' => 82,
                ]),
                'evaluator_name' => 'Manager Sales',
                'notes' => 'Pencapaian bagus untuk cabang baru. Terus tingkatkan database customer lokal.',
                'recommendations' => 'Perluas broadcast ke komunitas dan instansi setempat di Tabalong.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('sales_assessments')->insert($assessments);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_assessments');
        Schema::dropIfExists('kpi_criteria');
        Schema::dropIfExists('employees');
    }
};

