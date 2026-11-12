<?php

use App\Services\FonnteService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $map = FonnteService::GROUP_CABANG_MAP;

        foreach ($map as $groupId => $info) {
            $cabang = addslashes($info['cabang']);
            $grup = addslashes($info['grup']);

            DB::table('sosmed_keeps')
                ->where('grup_id', $groupId)
                ->where(function ($q) {
                    $q->whereNull('cabang')
                        ->orWhere('cabang', '')
                        ->orWhere('grup', 'GRUP')
                        ->orWhereNull('grup')
                        ->orWhere('grup', '');
                })
                ->update([
                    'cabang' => DB::raw("CASE WHEN cabang IS NULL OR cabang = '' THEN '{$cabang}' ELSE cabang END"),
                    'grup' => DB::raw("CASE WHEN grup IS NULL OR grup = '' OR grup = 'GRUP' THEN '{$grup}' ELSE grup END"),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data backfill
    }
};
