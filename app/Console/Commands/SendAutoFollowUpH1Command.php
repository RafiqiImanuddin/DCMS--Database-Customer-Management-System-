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
    protected $signature = 'keep:send-auto-fu-h1 {--date= : Tanggal keep (default: kemarin / H-1)} {--force : Paksa kirim ulang meskipun sudah terkirim}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim WhatsApp Rekap Auto Follow-Up H+1 ke PIC Sales via Baileys Bot Gateway';

    /**
     * Execute the console command.
     */
    public function handle(KeepAutoFollowUpService $service): int
    {
        $date = $this->option('date');
        $force = (bool) $this->option('force');

        $resolvedDate = $service->resolveTargetDate($date);
        $this->info("Menjalankan Auto Follow-Up H+1 untuk transaksi Keep tanggal: {$resolvedDate}...");

        $results = $service->sendAllPendingDigests($resolvedDate, $force, function ($type, $meta) {
            if ($type === 'REST_CYCLE') {
                $this->warn("   ☕ [Rest Cycle] Tercapai batch {$meta['sent_count']} pesan. Istirahat sejenak {$meta['cooldown_seconds']} detik untuk proteksi anti-suspend nomor WhatsApp...");
            } elseif ($type === 'DELAY') {
                $this->line("   ⏱️ Jeda acak {$meta['delay_seconds']} detik sebelum pesan berikutnya...");
            }
        });

        $this->info("Proses selesai!");
        $this->line("Total PIC Terdeteksi: {$results['total_pics']}");
        $this->line("Berhasil Terkirim: {$results['sent_count']}");
        $this->line("Gagal: {$results['failed_count']}");
        $this->line("Dilewati (Sudah kirim / Semua WIN): {$results['skipped_count']}");

        if (! empty($results['details'])) {
            $tableData = array_map(function ($d) {
                return [
                    $d['pic_name'],
                    $d['pic_phone'] ?: '-',
                    $d['status'],
                    $d['message'],
                ];
            }, $results['details']);

            $this->table(['Nama PIC', 'No. WhatsApp', 'Status', 'Keterangan'], $tableData);
        }

        return Command::SUCCESS;
    }
}
