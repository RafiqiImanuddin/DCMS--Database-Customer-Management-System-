<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'period',
    'final_score',
    'grade',
    'scores',
    'evaluator_name',
    'notes',
    'recommendations',
])]
class SalesAssessment extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scores' => 'array',
            'final_score' => 'decimal:2',
        ];
    }

    /**
     * Get the employee that owns this assessment.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Determine letter grade based on final score.
     */
    public static function calculateGrade(float $score): string
    {
        if ($score >= 90.0) {
            return 'A';
        }
        if ($score >= 80.0) {
            return 'B';
        }
        if ($score >= 70.0) {
            return 'C';
        }

        return 'D';
    }

    /**
     * Get human label for grade.
     */
    public static function getGradeLabel(string $grade): string
    {
        return match (strtoupper($grade)) {
            'A' => 'Sangat Baik (Top Performer)',
            'B' => 'Baik (Memenuhi Target)',
            'C' => 'Cukup (Perlu Evaluasi)',
            'D' => 'Kurang (Perlu Pembinaan Intensif)',
            default => 'Belum Dinilai',
        };
    }

    /**
     * Get Tailwind badge classes for grade.
     */
    public static function getGradeBadgeClasses(string $grade): string
    {
        return match (strtoupper($grade)) {
            'A' => 'bg-emerald-500/15 text-emerald-700 border-emerald-300 ring-emerald-500/20',
            'B' => 'bg-blue-500/15 text-blue-700 border-blue-300 ring-blue-500/20',
            'C' => 'bg-amber-500/15 text-amber-700 border-amber-300 ring-amber-500/20',
            'D' => 'bg-red-500/15 text-red-700 border-red-300 ring-red-500/20',
            default => 'bg-slate-100 text-slate-600 border-slate-200 ring-slate-400/20',
        };
    }

    /**
     * Generate smart diagnostic analysis, strengths, weaknesses, and ready-to-share WhatsApp message.
     *
     * @param array<string, mixed> $scores
     * @return array{
     *     strengths: array<int, string>,
     *     improvements: array<int, string>,
     *     recommendation: string,
     *     whatsapp_text: string
     * }
     */
    public static function generateSmartDiagnostic(
        Employee $employee,
        string $period,
        float $finalScore,
        string $grade,
        array $scores,
        ?string $notes = null,
        ?string $recommendations = null
    ): array {
        $strengths = [];
        $improvements = [];

        // 1. Cara Balas Chat
        $chat = (float) ($scores['chat_reply'] ?? $scores['csat_quality'] ?? 80);
        if ($chat >= 90) {
            $strengths[] = 'Cara balas chat sangat solutif, komunikatif, dan persuasif meyakinkan konsumen.';
        } elseif ($chat < 75) {
            $improvements[] = 'Penyampaian pesan masih agak kaku/template dan kurang detail menjawab kebutuhan konsumen.';
        }

        // 2. Respon Time
        $resp = (float) ($scores['resp_time'] ?? 80);
        if ($resp >= 90) {
            $strengths[] = 'Respon time sangat tanggap dan standby disiplin (< 3 menit per chat).';
        } elseif ($resp < 75) {
            $improvements[] = 'Kecepatan respon perlu ditingkatkan, terdeteksi chat tertunda lama terutama di jam operasional sibuk.';
        }

        // 3. Etika & Keramahan
        $ethics = (float) ($scores['ethics'] ?? 80);
        if ($ethics >= 90) {
            $strengths[] = 'Etika komunikasi sangat prima, sopan santun, dan selalu menyertakan greeting ramah.';
        } elseif ($ethics < 75) {
            $improvements[] = 'Perlu pembiasaan greeting pembuka/penutup yang lebih hangat dan ramah kepada pelanggan.';
        }

        // 4. Kesesuaian Promo
        $promo = (float) ($scores['promo_check'] ?? 80);
        if ($promo >= 90) {
            $strengths[] = 'Sangat disiplin dan aktif menawarkan program promo berjalan dengan informasi syarat yang akurat.';
        } elseif ($promo < 75) {
            $improvements[] = 'Peluang promo sering terlewatkan atau belum aktif ditawarkan ke calon pembeli potensial.';
        }

        if (empty($strengths)) {
            $strengths[] = 'Secara umum tugas pelayanan dasar telah dijalankan sesuai standar operasional harian.';
        }
        if (empty($improvements)) {
            $improvements[] = 'Seluruh aspek kerja harian telah memenuhi standar target dengan sangat baik.';
        }

        $recText = $recommendations ?: ($grade === 'A' 
            ? 'Pertahankan konsistensi performa terbaik ini dan jadilah teladan bagi rekan admin sales lainnya.' 
            : ($grade === 'B' 
                ? 'Tingkatkan penawaran bundling promo aktif dan jaga kecepatan respon chat saat jam ramai.' 
                : 'Fokuskan perbaikan pada aspek respon time dan hafalkan daftar promo aktif hari ini sebelum mulai shift.'));

        // Format Date for WA
        $isDaily = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $period);
        $dateLabel = $isDaily 
            ? \Carbon\Carbon::parse($period)->translatedFormat('l, d F Y')
            : \Carbon\Carbon::parse($period . '-01')->translatedFormat('F Y');

        // Build WhatsApp Ready-to-Send Text
        $wa = "📊 *RAPOR EVALUASI HARIAN ADMIN SALES*\n";
        $wa .= "🏢 *Gadgetmart DCMS*\n";
        $wa .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $wa .= "👤 *Nama*: {$employee->name}\n";
        $wa .= "🆔 *NIK*: {$employee->nik} | *Cabang*: {$employee->cabang}\n";
        $wa .= "📅 *Tanggal*: {$dateLabel}\n";
        $wa .= "⭐ *Skor Akhir*: *{$finalScore} / 100* (*Grade {$grade}* - " . self::getGradeLabel($grade) . ")\n\n";

        $wa .= "📌 *Rincian Nilai Aspek*:\n";
        if (isset($scores['chat_reply'])) {
            $wa .= "• Cara Balas Chat: {$scores['chat_reply']}/100 (Bobot 25%)\n";
        }
        if (isset($scores['resp_time'])) {
            $wa .= "• Respon Time: {$scores['resp_time']}/100 (Bobot 25%)\n";
        }
        if (isset($scores['ethics'])) {
            $wa .= "• Etika & Keramahan: {$scores['ethics']}/100 (Bobot 25%)\n";
        }
        if (isset($scores['promo_check'])) {
            $wa .= "• Kesesuaian Promo: {$scores['promo_check']}/100 (Bobot 25%)\n";
        }

        $wa .= "\n🌟 *Kekuatan Utama*:\n";
        foreach ($strengths as $s) {
            $wa .= "✓ {$s}\n";
        }

        $wa .= "\n⚠️ *Poin Perhatian/Evaluasi*:\n";
        foreach ($improvements as $imp) {
            $wa .= "• {$imp}\n";
        }

        if ($notes) {
            $wa .= "\n💬 *Catatan Manager*:\n\"{$notes}\"\n";
        }

        $wa .= "\n🎯 *Rekomendasi Tindak Lanjut*:\n{$recText}\n";
        $wa .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $wa .= "_Evaluasi ini dicatat resmi di DCMS Gadgetmart demi peningkatan kualitas layanan bersama._";

        return [
            'strengths' => $strengths,
            'improvements' => $improvements,
            'recommendation' => $recText,
            'whatsapp_text' => $wa,
        ];
    }
}

