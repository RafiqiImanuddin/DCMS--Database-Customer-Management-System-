<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        // 1. Update kpi_criteria to match the daily operational criteria:
        // - Cara Balas Chat Konsumen (25%)
        // - Respon Time (25%)
        // - Etika & Keramahan (25%)
        // - Kesesuaian Penawaran Promo (25%)
        DB::table('kpi_criteria')->update(['is_active' => false]);

        $dailyCriteria = [
            [
                'code' => 'chat_reply',
                'name' => 'Cara Balas Chat Konsumen',
                'description' => 'Kejelasan solusi, kecocokan jawaban dengan kebutuhan konsumen, gaya komunikasi persuasif dan tidak kaku.',
                'weight' => 25.00,
                'target_benchmark' => 'Jawaban solutif, komunikatif & mudah dipahami',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'resp_time',
                'name' => 'Respon Time (Kecepatan Balas)',
                'description' => 'Kecepatan membalas pesan masuk calon pembeli dan ketepatan waktu follow-up prospek saat jam kerja.',
                'weight' => 25.00,
                'target_benchmark' => 'Respon cepat < 3-5 menit, standby aktif',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ethics',
                'name' => 'Etika & Keramahan',
                'description' => 'Sopan santun bahasa, greeting pembuka & penutup yang hangat, kesabaran melayani, dan nada bicara profesional.',
                'weight' => 25.00,
                'target_benchmark' => 'Greeting lengkap, ramah, sopan & solutif',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'promo_check',
                'name' => 'Kesesuaian Penawaran Promo',
                'description' => 'Kepatuhan menawarkan program promo yang sedang berlangsung (bundling gadget, voucher, diskon aksesoris, cicilan) dan keakuratan syarat.',
                'weight' => 25.00,
                'target_benchmark' => 'Aktif tawarkan promo yang berlaku & info akurat',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($dailyCriteria as $c) {
            $existing = DB::table('kpi_criteria')->where('code', $c['code'])->first();
            if ($existing) {
                DB::table('kpi_criteria')->where('code', $c['code'])->update([
                    'name' => $c['name'],
                    'description' => $c['description'],
                    'weight' => $c['weight'],
                    'target_benchmark' => $c['target_benchmark'],
                    'is_active' => true,
                    'sort_order' => $c['sort_order'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('kpi_criteria')->insert($c);
            }
        }

        // 2. Seed daily assessments for today (2026-11-05) and yesterday (2026-11-04)
        $employees = DB::table('employees')->where('status', 'active')->orderBy('id')->get();
        if ($employees->isNotEmpty()) {
            $today = '2026-11-05';
            $yesterday = '2026-11-04';

            $mockDailyScores = [
                1 => [ // Siti Rahmawati
                    'today' => ['chat_reply' => 95, 'resp_time' => 98, 'ethics' => 96, 'promo_check' => 92],
                    'yesterday' => ['chat_reply' => 92, 'resp_time' => 95, 'ethics' => 94, 'promo_check' => 90],
                    'notes' => 'Cara balas chat sangat solutif dan greeting selalu ramah. Promo bundling headphone sukses ditawarkan ke 4 customer.',
                    'recommendations' => 'Pertahankan performa prima dan konsistensi respon cepat saat jam puncak.',
                ],
                2 => [ // Muhammad Rizky Pratama
                    'today' => ['chat_reply' => 90, 'resp_time' => 92, 'ethics' => 88, 'promo_check' => 90],
                    'yesterday' => ['chat_reply' => 88, 'resp_time' => 90, 'ethics' => 86, 'promo_check' => 88],
                    'notes' => 'Respon time sangat tanggap, penawaran promo cicilan 0% sudah sesuai arahan tim promosi.',
                    'recommendations' => 'Tingkatkan kehangatan salam penutup agar interaksi terasa lebih personal.',
                ],
                3 => [ // Nadia Safitri
                    'today' => ['chat_reply' => 92, 'resp_time' => 90, 'ethics' => 96, 'promo_check' => 94],
                    'yesterday' => ['chat_reply' => 90, 'resp_time' => 88, 'ethics' => 95, 'promo_check' => 92],
                    'notes' => 'Etika dan keramahan luar biasa. Penjelasan benefit promo tukar tambah sangat detail dan meyakinkan konsumen.',
                    'recommendations' => 'Pertahankan antusiasme dan cara komunikasi yang menyenangkan.',
                ],
                4 => [ // Ahmad Fauzi
                    'today' => ['chat_reply' => 76, 'resp_time' => 72, 'ethics' => 82, 'promo_check' => 74],
                    'yesterday' => ['chat_reply' => 78, 'resp_time' => 74, 'ethics' => 80, 'promo_check' => 72],
                    'notes' => 'Respon time sempat lambat di jam 13.00-14.30. Promo diskon aksesoris lupa disampaikan pada 2 obrolan leads.',
                    'recommendations' => 'Wajib buka contekan promo aktif di awal shift dan pasang notifikasi audio pesan masuk.',
                ],
                5 => [ // Dewi Anggraini
                    'today' => ['chat_reply' => 88, 'resp_time' => 86, 'ethics' => 90, 'promo_check' => 86],
                    'yesterday' => ['chat_reply' => 85, 'resp_time' => 84, 'ethics' => 88, 'promo_check' => 85],
                    'notes' => 'Komunikasi stabil, etika sopan dan penawaran promo cashback voucher sudah tepat sasaran.',
                    'recommendations' => 'Dapat ditingkatkan kecepatan respon untuk chat follow-up sore hari.',
                ],
                6 => [ // Budi Santoso
                    'today' => ['chat_reply' => 84, 'resp_time' => 82, 'ethics' => 85, 'promo_check' => 82],
                    'yesterday' => ['chat_reply' => 82, 'resp_time' => 80, 'ethics' => 84, 'promo_check' => 80],
                    'notes' => 'Penyampaian promo cukup baik. Cara balas chat informatif meski masih agak kaku/template.',
                    'recommendations' => 'Gunakan variasi kalimat yang lebih fleksibel dan luwes saat menjawab pertanyaan spesifikasi.',
                ],
            ];

            foreach ($employees as $emp) {
                $scoresData = $mockDailyScores[$emp->id] ?? [
                    'today' => ['chat_reply' => 85, 'resp_time' => 85, 'ethics' => 85, 'promo_check' => 85],
                    'yesterday' => ['chat_reply' => 82, 'resp_time' => 82, 'ethics' => 82, 'promo_check' => 82],
                    'notes' => 'Kinerja harian standar baik, komunikasi tertib.',
                    'recommendations' => 'Tingkatkan kecepatan follow-up prospek.',
                ];

                // Seed Today
                $todayScore = (
                    ($scoresData['today']['chat_reply'] * 0.25) +
                    ($scoresData['today']['resp_time'] * 0.25) +
                    ($scoresData['today']['ethics'] * 0.25) +
                    ($scoresData['today']['promo_check'] * 0.25)
                );
                $todayGrade = $todayScore >= 90 ? 'A' : ($todayScore >= 80 ? 'B' : ($todayScore >= 70 ? 'C' : 'D'));

                DB::table('sales_assessments')->updateOrInsert(
                    ['employee_id' => $emp->id, 'period' => $today],
                    [
                        'final_score' => $todayScore,
                        'grade' => $todayGrade,
                        'scores' => json_encode($scoresData['today']),
                        'evaluator_name' => 'Manager Sales',
                        'notes' => $scoresData['notes'],
                        'recommendations' => $scoresData['recommendations'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                // Seed Yesterday
                $yesterdayScore = (
                    ($scoresData['yesterday']['chat_reply'] * 0.25) +
                    ($scoresData['yesterday']['resp_time'] * 0.25) +
                    ($scoresData['yesterday']['ethics'] * 0.25) +
                    ($scoresData['yesterday']['promo_check'] * 0.25)
                );
                $yesterdayGrade = $yesterdayScore >= 90 ? 'A' : ($yesterdayScore >= 80 ? 'B' : ($yesterdayScore >= 70 ? 'C' : 'D'));

                DB::table('sales_assessments')->updateOrInsert(
                    ['employee_id' => $emp->id, 'period' => $yesterday],
                    [
                        'final_score' => $yesterdayScore,
                        'grade' => $yesterdayGrade,
                        'scores' => json_encode($scoresData['yesterday']),
                        'evaluator_name' => 'Manager Sales',
                        'notes' => 'Evaluasi harian kemarin: ' . $scoresData['notes'],
                        'recommendations' => $scoresData['recommendations'],
                        'created_at' => $now->copy()->subDay(),
                        'updated_at' => $now->copy()->subDay(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old criteria if needed
    }
};

