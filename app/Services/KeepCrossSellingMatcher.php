<?php

namespace App\Services;

use App\Models\CrossSelling;
use App\Models\CrossSellingImport;
use App\Models\SosmedKeep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class KeepCrossSellingMatcher
{
    /**
     * Kata-kata umum atau atribut yang diabaikan saat pencocokan model produk
     */
    private const IGNORED_WORDS = [
        'WARNA', 'COLOR', 'COLOUR', 'BLACK', 'HITAM', 'WHITE', 'PUTIH', 'BLUE', 'BIRU',
        'GREEN', 'HIJAU', 'RED', 'MERAH', 'GOLD', 'EMAS', 'SILVER', 'PERAK', 'GREY', 'GRAY',
        'ABU', 'YELLOW', 'KUNING', 'PURPLE', 'UNGU', 'PINK', 'ROSE', 'TITANIUM', 'NATURAL',
        'ORIGINAL', 'RESMI', 'GARANSI', 'TAM', 'NEW', 'BARU', 'UNIT', 'PROMO', 'BUNDLE',
        'SPECIAL', 'EDITION', 'BEST', 'PRICE', 'SALE', 'DEAL', 'BOX', 'SEALED',
    ];

    /**
     * Kata kunci aksesoris untuk mendeteksi barang aksesoris
     */
    private const ACCESSORY_KEYWORDS = [
        'TEMPERED GLASS', 'TEMPERED', 'HYDROGEL', 'ANTI GORES', 'ANTIGORES', 'SCREEN PROTECTOR',
        'CASE', 'CASING', 'COVER', 'SILICONE', 'FLIP COVER', 'BACKDOOR',
        'CHARGER', 'ADAPTOR', 'ADAPTER', 'TRAVEL ADAPTER', 'POWER ADAPTER', 'FAST CHARGING',
        'KABEL', 'CABLE', 'DATA CABLE',
        'HEADSET', 'EARPHONE', 'HANDSFREE', 'TWS', 'EARBUDS', 'AIRPODS',
        'POWERBANK', 'POWER BANK', 'BATTERY', 'BATERAI', 'STRAP', 'PELINDUNG', 'TAS GADGETMART',
    ];

    /**
     * Kategori spesifik aksesoris untuk mencegah salah pasang antar-jenis aksesoris
     */
    private const ACCESSORY_CATEGORIES = [
        'SCREEN_PROTECTOR' => ['TEMPERED GLASS', 'TEMPERED', 'HYDROGEL', 'ANTI GORES', 'ANTIGORES', 'SCREEN PROTECTOR'],
        'CASE' => ['CASE', 'CASING', 'COVER', 'SILICONE', 'FLIP COVER', 'BACKDOOR'],
        'CHARGER' => ['CHARGER', 'ADAPTOR', 'ADAPTER', 'TRAVEL ADAPTER', 'POWER ADAPTER', 'FAST CHARGING'],
        'CABLE' => ['KABEL', 'CABLE', 'DATA CABLE'],
        'AUDIO' => ['HEADSET', 'EARPHONE', 'HANDSFREE', 'TWS', 'EARBUDS', 'AIRPODS'],
        'POWERBANK' => ['POWERBANK', 'POWER BANK', 'BATTERY PACK', 'BATTERY', 'BATERAI'],
    ];

    /**
     * Kata kunci elektronik non-gadget / peralatan rumah tangga (Home Appliances)
     */
    private const HOME_APPLIANCE_KEYWORDS = [
        'KULKAS', 'REFRIGERATOR', 'FREEZER', 'CHILLER', 'SHOWCASE',
        'TELEVISI', 'SMART TV', 'LED TV', 'ANDROID TV', 'GOOGLE TV',
        'AIR CONDITIONER', 'AC SPLIT', 'AC INVERTER', 'AC STANDING',
        'MESIN CUCI', 'WASHING MACHINE', 'DRYER', 'PENGERING',
        'DISPENSER', 'WATER DISPENSER', 'MICROWAVE', 'OVEN',
        'BLENDER', 'JUICER', 'RICE COOKER', 'MAGIC COM', 'MAGIC JAR',
        'KIPAS ANGIN', 'STAND FAN', 'WALL FAN', 'DESK FAN', 'EXHAUST',
        'SETRIKA', 'IRON', 'GARMENT STEAMER', 'VACUUM CLEANER', 'ROBOT VACUUM',
        'WATER HEATER', 'KOMPOR', 'GAS STOVE', 'INDUCTION COOKER',
        'AIR FRYER', 'AIR PURIFIER', 'DEHUMIDIFIER', 'HUMIDIFIER',
    ];

    /**
     * Merek-merek utama untuk verifikasi konsistensi brand
     */
    private const MAJOR_BRANDS = [
        'IPHONE' => ['IPHONE', 'APPLE', 'IPAD', 'MACBOOK'],
        'SAMSUNG' => ['SAMSUNG', 'GALAXY'],
        'XIAOMI' => ['XIAOMI', 'REDMI', 'MI'],
        'POCO' => ['POCO'],
        'OPPO' => ['OPPO', 'RENO', 'FIND'],
        'VIVO' => ['VIVO', 'IQOO'],
        'REALME' => ['REALME'],
        'INFINIX' => ['INFINIX', 'NOTE', 'HOT', 'SMART', 'ZERO'],
        'TECNO' => ['TECNO', 'SPARK', 'POVA', 'CAMON'],
        'ITEL' => ['ITEL'],
        'HUAWEI' => ['HUAWEI'],
        'ASUS' => ['ASUS', 'ROG', 'ZENFONE'],
    ];

    /**
     * Eksekusi pencocokan untuk seluruh data cross-selling dari satu batch import tertentu
     *
     * @return array{total_transactions: int, total_candidates: int, matched_count: int, need_to_check_count: int, updated_keeps: array}
     */
    public function matchImport(CrossSellingImport $import): array
    {
        $transactions = CrossSelling::where('cross_selling_import_id', $import->id)
            ->whereNotNull('no_hp_customer')
            ->where('no_hp_customer', '!=', '')
            ->get();

        return $this->matchTransactions($transactions);
    }

    /**
     * Eksekusi pencocokan untuk seluruh koleksi transaksi cross-selling terhadap data Format Keep.
     * Mengelompokkan transaksi per faktur AR (document_number) sehingga seluruh item dalam 1 AR
     * dapat dievaluasi bersama (termasuk unit utama dan aksesoris).
     *
     * Aturan:
     * 1. Jika No HP sama & barang cocok (termasuk aksesoris sama atau handphone sama) -> WIN!
     * 2. Jika No HP sama tetapi barang berbeda (beda tipe HP, beda jenis kulkas/tv/ac, atau cuma aksesoris)
     *    -> Status 'NEED TO CHECK' agar admin mudah melakukan crosscheck.
     *
     * @param Collection<int, CrossSelling> $transactions
     * @return array{total_transactions: int, total_candidates: int, matched_count: int, need_to_check_count: int, updated_keeps: array}
     */
    public function matchTransactions(Collection $transactions): array
    {
        if ($transactions->isEmpty()) {
            return [
                'total_transactions' => 0,
                'total_candidates' => 0,
                'matched_count' => 0,
                'need_to_check_count' => 0,
                'updated_keeps' => [],
            ];
        }

        // Ambil data format keep yang memiliki nomor WA
        $activeKeeps = SosmedKeep::whereNotNull('nomor_wa')
            ->where('nomor_wa', '!=', '')
            ->get();

        if ($activeKeeps->isEmpty()) {
            return [
                'total_transactions' => $transactions->count(),
                'total_candidates' => 0,
                'matched_count' => 0,
                'need_to_check_count' => 0,
                'updated_keeps' => [],
            ];
        }

        // Petakan Keep berdasarkan 9 digit terakhir nomor HP untuk pencocokan cepat
        $keepsByPhone9 = [];
        foreach ($activeKeeps as $keep) {
            $cleanPhone = preg_replace('/\D/', '', (string) $keep->nomor_wa);
            if (strlen($cleanPhone) >= 8) {
                $last9 = substr($cleanPhone, -9);
                $keepsByPhone9[$last9][] = $keep;
            }
        }

        // Kelompokkan data transaksi berdasarkan nomor faktur AR (document_number)
        $groupedByDoc = $transactions->groupBy(function ($trx) {
            $doc = trim((string) $trx->document_number);
            return $doc !== '' ? $doc : ('TRX_' . $trx->id);
        });

        $matchedCount = 0;
        $needToCheckCount = 0;
        $updatedKeeps = [];

        foreach ($groupedByDoc as $docNum => $docItems) {
            // Kumpulkan nomor HP unik dalam 1 faktur AR ini
            $phonesInDoc = [];
            foreach ($docItems as $trx) {
                $rawPhone = $trx->no_hp_customer;
                $cleanPhone = preg_replace('/\D/', '', (string) $rawPhone);
                if (strlen($cleanPhone) >= 8) {
                    $phonesInDoc[substr($cleanPhone, -9)] = true;
                }
            }

            if (empty($phonesInDoc)) {
                continue;
            }

            // Cari semua kandidat keep yang nomor HP-nya cocok dengan faktur AR ini
            /** @var array<int, SosmedKeep> $candidateKeeps */
            $candidateKeeps = [];
            foreach (array_keys($phonesInDoc) as $last9) {
                if (isset($keepsByPhone9[$last9])) {
                    foreach ($keepsByPhone9[$last9] as $k) {
                        $candidateKeeps[$k->id] = $k;
                    }
                }
            }

            if (empty($candidateKeeps)) {
                continue;
            }

            // Periksa setiap keep terhadap SELURUH item dalam 1 faktur AR ini
            foreach ($candidateKeeps as $keep) {
                $barangKeep = (string) $keep->barang_keep;
                $matchedItem = null;

                foreach ($docItems as $item) {
                    $trxDesc = (string) ($item->dscription ?: $item->item_code);
                    $trxBrand = (string) ($item->brand ?: '');

                    if ($this->isItemMatch($barangKeep, $trxDesc, $trxBrand)) {
                        $matchedItem = $item;
                        break;
                    }
                }

                $currentStatus = strtoupper(trim((string) $keep->status));

                // 1. KASUS A: Barang SAMA / Cocok (termasuk aksesoris sama) -> Closing WIN!
                if ($matchedItem !== null) {
                    $matchedDesc = (string) ($matchedItem->dscription ?: $matchedItem->item_code);
                    $matchedDoc = (string) $matchedItem->document_number;
                    $matchedDate = $matchedItem->posting_date ? $matchedItem->posting_date->format('Y-m-d') : null;

                    $wasUpdated = false;

                    // Perbarui status menjadi WIN jika belum
                    if ($currentStatus !== 'WIN') {
                        $keep->status = 'WIN';
                        $wasUpdated = true;
                    }

                    // Isi AR dari VLOOKUP jika masih kosong atau berbeda
                    if (empty($keep->ar_vlookup) || $keep->ar_vlookup !== $matchedDoc) {
                        $keep->ar_vlookup = $matchedDoc;
                        $keep->tanggal_vlookup = $matchedDate;
                        $wasUpdated = true;
                    }

                    // Isi REPORT AR jika belum ada
                    if (empty($keep->report_ar)) {
                        $keep->report_ar = $matchedDoc;
                        $keep->tanggal_input_ar = $matchedDate ? now() : null;
                        $wasUpdated = true;
                    }

                    if ($wasUpdated) {
                        $keep->save();
                        $matchedCount++;
                        $updatedKeeps[] = [
                            'keep_id' => $keep->id,
                            'customer_name' => $keep->nama,
                            'phone' => $keep->nomor_wa,
                            'status' => 'WIN',
                            'barang_keep' => $barangKeep,
                            'transaksi_item' => $matchedDesc,
                            'document_number' => $matchedDoc,
                            'posting_date' => $matchedDate,
                        ];
                    }
                }
                // 2. KASUS B: No HP Cocok, tetapi Barang Berbeda -> Masukkan ke NEED TO CHECK
                // (Jangan pernah downgrade jika status keep sudah WIN)
                elseif ($currentStatus !== 'WIN') {
                    $primaryItem = $this->getPrimaryItem($docItems);
                    if ($primaryItem !== null) {
                        $primaryDesc = (string) ($primaryItem->dscription ?: $primaryItem->item_code);
                        $primaryDoc = (string) $primaryItem->document_number;
                        $primaryDate = $primaryItem->posting_date ? $primaryItem->posting_date->format('Y-m-d') : null;

                        $wasUpdated = false;

                        if ($currentStatus !== 'NEED TO CHECK') {
                            $keep->status = 'NEED TO CHECK';
                            $wasUpdated = true;
                        }

                        if (empty($keep->ar_vlookup) || $keep->ar_vlookup !== $primaryDoc) {
                            $keep->ar_vlookup = $primaryDoc;
                            $keep->tanggal_vlookup = $primaryDate;
                            $wasUpdated = true;
                        }

                        if (empty($keep->report_ar)) {
                            $keep->report_ar = $primaryDoc;
                            $keep->tanggal_input_ar = $primaryDate ? now() : null;
                            $wasUpdated = true;
                        }

                        $mismatchReason = $this->detectMismatchReason($barangKeep, $primaryDesc);

                        if ($wasUpdated) {
                            $keep->save();
                            $needToCheckCount++;
                            $updatedKeeps[] = [
                                'keep_id' => $keep->id,
                                'customer_name' => $keep->nama,
                                'phone' => $keep->nomor_wa,
                                'status' => 'NEED TO CHECK',
                                'mismatch_reason' => $mismatchReason,
                                'barang_keep' => $barangKeep,
                                'transaksi_item' => $primaryDesc,
                                'document_number' => $primaryDoc,
                                'posting_date' => $primaryDate,
                            ];
                        }
                    }
                }
            }
        }

        return [
            'total_transactions' => $transactions->count(),
            'total_candidates' => count($keepsByPhone9),
            'matched_count' => $matchedCount,
            'need_to_check_count' => $needToCheckCount,
            'updated_keeps' => $updatedKeeps,
        ];
    }

    /**
     * Pilih item utama dalam 1 faktur AR (prioritaskan unit handphone/gadget jika ada)
     *
     * @param Collection<int, CrossSelling> $docItems
     */
    private function getPrimaryItem(Collection $docItems): ?CrossSelling
    {
        // 1. Cari apakah ada unit handphone/gadget
        foreach ($docItems as $item) {
            $desc = (string) ($item->dscription ?: $item->item_code);
            if ($this->isHandphoneOrGadget($desc)) {
                return $item;
            }
        }

        // 2. Jika tidak ada gadget, ambil item pertama
        return $docItems->first();
    }

    /**
     * Identifikasi kategori perbedaan barang untuk memudahkan admin saat kroscek
     */
    public function detectMismatchReason(string $barangKeep, string $trxDesc): string
    {
        $keepIsHp = $this->isHandphoneOrGadget($barangKeep);
        $trxIsHp = $this->isHandphoneOrGadget($trxDesc);

        if ($this->isHomeAppliance($trxDesc)) {
            return "Beda Jenis (Kulkas/TV/AC/Elektronik)";
        }

        if ($keepIsHp && $trxIsHp) {
            return "Tipe HP Berbeda (Sama-sama Handphone)";
        }

        if ($keepIsHp && $this->isAccessory($trxDesc)) {
            return "Hanya Beli Aksesoris (Keep Unit HP)";
        }

        if ($this->isAccessory($barangKeep) && $this->isAccessory($trxDesc)) {
            return "Beda Jenis Aksesoris";
        }

        if ($this->isAccessory($barangKeep) && $trxIsHp) {
            return "Keep Aksesoris tapi Beli Unit HP";
        }

        return "Barang Berbeda";
    }

    /**
     * Evaluasi kecocokan antara barang keep dengan nama barang di transaksi penjualan
     */
    public function isItemMatch(string $keepBarang, string $crossDesc, ?string $crossBrand = null): bool
    {
        $kb = strtoupper(trim($keepBarang));
        $cd = strtoupper(trim($crossDesc));
        $cb = strtoupper(trim((string) $crossBrand));

        if ($kb === '' || $cd === '') {
            return false;
        }

        $keepIsAccessory = $this->isAccessory($kb);
        $crossIsAccessory = $this->isAccessory($cd);

        // 1. Proteksi Aksesoris: Jika keep adalah unit HP tapi transaksi HANYA aksesoris -> FALSE
        if (! $keepIsAccessory && $crossIsAccessory) {
            return false;
        }

        // 2. Jika keep dan transaksi SAMA-SAMA aksesoris:
        // Selagi item nya sama dengan di format keep, dan dalam 1 AR yang sama -> dianggap closing
        if ($keepIsAccessory && $crossIsAccessory) {
            if (! $this->isAccessoryCategoryMatch($kb, $cd)) {
                return false;
            }
        }

        // 3. Jika barang adalah elektronik rumah tangga non-gadget (Kulkas/TV/AC) -> FALSE
        if ($this->isHomeAppliance($cd) && ! $this->isHomeAppliance($kb)) {
            return false;
        }

        // 4. Jika teks deskripsi identik
        if ($kb === $cd) {
            return true;
        }

        // 5. Ekstraksi Merek / Brand
        $keepBrand = $this->detectBrand($kb);
        $crossDetectedBrand = $this->detectBrand($cd) ?: $this->detectBrand($cb);

        if ($keepBrand && $crossDetectedBrand && $keepBrand !== $crossDetectedBrand) {
            // Merek jelas berbeda (misal Keep IPHONE tapi transaksi SAMSUNG) -> FALSE
            return false;
        }

        // 6. Ekstraksi Model & Tipe Spesifik Produk
        $keepModelTokens = $this->extractProductModelTokens($kb);
        $crossModelTokens = $this->extractProductModelTokens($cd);

        if (empty($keepModelTokens)) {
            // Jika tidak ada token model spesifik, gunakan kecocokan kata kunci dasar atau brand
            if ($keepBrand && $crossDetectedBrand && $keepBrand === $crossDetectedBrand) {
                return true;
            }
            return str_contains($cd, $kb) || str_contains($kb, $cd);
        }

        // Periksa apakah token model keep ditemukan di deskripsi transaksi
        $matchModelCount = 0;
        foreach ($keepModelTokens as $token) {
            if (preg_match('/\b' . preg_quote($token, '/') . '\b/i', $cd)) {
                $matchModelCount++;
            } elseif (str_contains($cd, $token)) {
                $matchModelCount++;
            }
        }

        $totalTokens = count($keepModelTokens);
        $tokenRatio = $totalTokens > 0 ? ($matchModelCount / $totalTokens) : 0;

        if ($tokenRatio >= 0.70 || ($matchModelCount >= 2 && $tokenRatio >= 0.50) || ($totalTokens === 1 && $matchModelCount === 1)) {
            return true;
        }

        return false;
    }

    /**
     * Deteksi apakah teks deskripsi barang merupakan aksesoris
     */
    public function isAccessory(string $text): bool
    {
        $upper = strtoupper($text);
        foreach (self::ACCESSORY_KEYWORDS as $acc) {
            if (str_contains($upper, $acc)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Deteksi apakah teks deskripsi merupakan produk elektronik non-gadget (Kulkas/TV/AC/Mesin Cuci dll)
     */
    public function isHomeAppliance(string $text): bool
    {
        $upper = strtoupper($text);
        foreach (self::HOME_APPLIANCE_KEYWORDS as $keyword) {
            if (str_contains($upper, $keyword)) {
                return true;
            }
        }

        // Standalone TV atau AC (menggunakan word boundary agar tidak salah deteksi kata seperti ACTION atau MACBOOK)
        if (preg_match('/\b(TV|AC)\b/i', $upper)) {
            return true;
        }

        return false;
    }

    /**
     * Deteksi apakah teks deskripsi merupakan smartphone atau tablet gadget
     */
    public function isHandphoneOrGadget(string $text): bool
    {
        if ($this->isAccessory($text) || $this->isHomeAppliance($text)) {
            return false;
        }

        $upper = strtoupper($text);

        // Keyword eksplisit gadget
        if (preg_match('/\b(HP|SMARTPHONE|HANDPHONE|TABLET|IPAD|TAB)\b/i', $upper)) {
            return true;
        }

        // Cek apakah mengandung brand gadget utama
        return $this->detectBrand($upper) !== null;
    }

    /**
     * Cek kesesuaian kategori aksesoris (misal: sama-sama pelindung layar, casing, atau charger)
     */
    public function isAccessoryCategoryMatch(string $keepBarang, string $crossDesc): bool
    {
        $kb = strtoupper($keepBarang);
        $cd = strtoupper($crossDesc);

        $keepCats = [];
        $crossCats = [];

        foreach (self::ACCESSORY_CATEGORIES as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($kb, $kw)) {
                    $keepCats[] = $catName;
                    break;
                }
            }
            foreach ($keywords as $kw) {
                if (str_contains($cd, $kw)) {
                    $crossCats[] = $catName;
                    break;
                }
            }
        }

        // Jika salah satu tidak terpetakan dalam kategori spesifik, loloskan untuk cek token model
        if (empty($keepCats) || empty($crossCats)) {
            return true;
        }

        return ! empty(array_intersect($keepCats, $crossCats));
    }

    /**
     * Deteksi nama brand dari string produk
     */
    public function detectBrand(string $text): ?string
    {
        $upper = strtoupper($text);
        foreach (self::MAJOR_BRANDS as $brandKey => $aliases) {
            foreach ($aliases as $alias) {
                if (preg_match('/\b' . preg_quote($alias, '/') . '\b/i', $upper)) {
                    return $brandKey;
                }
            }
        }
        return null;
    }

    /**
     * Ekstraksi token nomor model/tipe utama (misal: "15", "PRO", "A55", "S24", "NOTE", "13")
     *
     * @return array<string>
     */
    private function extractProductModelTokens(string $text): array
    {
        $upper = strtoupper($text);
        // Hapus tanda kurung dan karakter non-alfanumerik
        $cleaned = preg_replace('/[^\w\d\s]/', ' ', $upper);
        $words = preg_split('/\s+/', (string) $cleaned, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) < 2) {
                // Kecuali digit angka model seperti 8, 9
                if (is_numeric($word)) {
                    $tokens[] = $word;
                }
                continue;
            }

            // Abaikan kata-kata umum warna/garansi/spesifikasi RAM
            if (in_array($word, self::IGNORED_WORDS, true)) {
                continue;
            }

            // Abaikan satuan storage / memori jika berdiri sendiri
            if (preg_match('/^\d+(GB|TB|MB|RAM)$/i', $word) || preg_match('/^\d+\/\d+$/', $word)) {
                continue;
            }

            // Abaikan nama brand murni
            $isBrandWord = false;
            foreach (self::MAJOR_BRANDS as $aliases) {
                if (in_array($word, $aliases, true)) {
                    $isBrandWord = true;
                    break;
                }
            }
            if ($isBrandWord) {
                continue;
            }

            $tokens[] = $word;
        }

        return array_values(array_unique($tokens));
    }
}
