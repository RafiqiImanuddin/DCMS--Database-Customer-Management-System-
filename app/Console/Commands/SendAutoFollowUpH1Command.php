<?php

namespace App\Console\Commands;

use App\Services\KeepAutoFollowUpService;
use Illuminate\Console\Command;

class SendAutoFollowUpH1Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keep:send-auto-fu-h1 {--date= : Tanggal keep tertentu untuk mode rekap harian} {--force : Paksa kirim ulang} {--limit= : Batas grup PIC per eksekusi batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim WhatsApp Auto Follow-Up H+1 (Strict 24 Jam Presisi atau Rekap Harian) ke PIC Sales via Baileys Bot';

    /**
     * Execute the console command.
     */
    public function handle(KeepAutoFollowUpService $service): int
    {
        $date = $this->option('date');
        $force = (bool) $this->option('force');

        $progressCallback = function ($type, $meta) {
            if ($type === 'REST_CYCLE') {
                $this->warn("   ☕ [Rest Cycle] Tercapai batch {$meta['sent_count']} pesan. Istirahat sejenak {$meta['cooldown_seconds']} detik untuk proteksi anti-suspend WhatsApp...");
            } elseif ($type === 'DELAY') {
                $this->line("   ⏱️ Jeda acak {$meta['delay_seconds']} detik sebelum pesan berikutnya...");
            }
        };

        if (! empty($date)) {
            $resolvedDate = $service->resolveTargetDate($date);
            $this->info("Menjalankan Manual Rekap Follow-Up H+1 untuk transaksi Keep tanggal: {$resolvedDate}...");
            $results = $service->sendAllPendingDigests($resolvedDate, $force, $progressCallback);
        } else {
            $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;
            $this->info("Menjalankan Auto Follow-Up H+1 Strict 1x24 Jam Presisi (Jam Kerja 08:00 - 22:00 WITA)...");
            $results = $service->sendStrict24hAlerts($force, $progressCallback, $limit);
        }

        $this->info("Proses selesai: " . ($results['message'] ?? 'OK'));
        if (isset($results['total_eligible'])) {
            $this->line("Total Keep Jatuh Tempo (>= 24 Jam): {$results['total_eligible']}");
        }
        $this->line("Total PIC Terlibat: " . ($results['total_pics'] ?? 0));
        $this->line("Berhasil Terkirim: " . ($results['sent_count'] ?? 0));
        $this->line("Gagal: " . ($results['failed_count'] ?? 0));
        $this->line("Dilewati: " . ($results['skipped_count'] ?? 0));

        if (! empty($results['details'])) {
            $tableData = array_map(function ($d) {
                return [
                    $d['pic_name'],
                    $d['pic_phone'] ?: '-',
                    $d['mode'] ?? 'DIGEST',
                    $d['status'],
                    $d['message'],
                ];
            }, $results['details']);

            $this->table(['Nama PIC', 'No. WhatsApp', 'Mode', 'Status', 'Keterangan'], $tableData);
        }

        return Command::SUCCESS;
    }
}
