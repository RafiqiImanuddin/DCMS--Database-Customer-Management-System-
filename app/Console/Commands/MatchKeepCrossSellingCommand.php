<?php

namespace App\Console\Commands;

use App\Models\CrossSelling;
use App\Services\KeepCrossSellingMatcher;
use Illuminate\Console\Command;

class MatchKeepCrossSellingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keep:match-cross-selling {--limit=10000 : Jumlah transaksi cross-selling terakhir yang dicek}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mencocokkan nomor HP & produk transaksi Cross-Selling SAP dengan data Format Keep (Closing WIN Detector)';

    /**
     * Execute the console command.
     */
    public function handle(KeepCrossSellingMatcher $matcher): int
    {
        $limit = (int) $this->option('limit');
        $this->info("🔍 Memulai proses matching transaksi Cross-Selling dengan Format Keep (limit {$limit} data)...");

        $transactions = CrossSelling::whereNotNull('no_hp_customer')
            ->where('no_hp_customer', '!=', '')
            ->latest('id')
            ->limit($limit)
            ->get();

        if ($transactions->isEmpty()) {
            $this->warn('Tidak ada data transaksi cross-selling dengan nomor HP.');
            return Command::SUCCESS;
        }

        $result = $matcher->matchTransactions($transactions);

        $this->table(['Metrik', 'Nilai'], [
            ['Total Transaksi Diperiksa', number_format($result['total_transactions'])],
            ['Total Kandidat Nomor HP Keep', number_format($result['total_candidates'])],
            ['Transaksi Cocok (Closing WIN)', number_format($result['matched_count'])],
        ]);

        if (! empty($result['updated_keeps'])) {
            $this->info("✅ Berhasil mengupdate {$result['matched_count']} data Format Keep menjadi WIN:");
            $sample = array_slice($result['updated_keeps'], 0, 10);
            $this->table(
                ['ID Keep', 'Nama Customer', 'No WA', 'Barang Keep', 'Item Cross-Selling', 'Faktur AR'],
                array_map(fn ($k) => [
                    $k['keep_id'],
                    $k['customer_name'],
                    $k['phone'],
                    $k['barang_keep'],
                    $k['transaksi_item'],
                    $k['document_number'],
                ], $sample)
            );
        } else {
            $this->line('Semua data keep yang cocok sudah berstatus WIN atau tidak ada nomor & item yang sama.');
        }

        return Command::SUCCESS;
    }
}
