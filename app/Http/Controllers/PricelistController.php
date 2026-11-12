<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PricelistController extends Controller
{
    /**
     * Storage path for pricelist JSON data
     */
    private string $dataPath;
    private string $rawExcelPath;
    private string $dropboxUrl = 'https://www.dropbox.com/scl/fi/9k0o82jum0yd1j0qm55pz/Pricelist-Update-New-Update.xlsx?rlkey=j5qbrdfvn8kust83b18rq6f68&st=8qc2m1u8&dl=1';

    public function __construct()
    {
        $this->dataPath = storage_path('app/pricelist_data.json');
        $this->rawExcelPath = storage_path('app/pricelist_sample.xlsx');
    }

    /**
     * Render Pricelist Master View (Superadmin Only, View-Only)
     */
    public function index(Request $request): View
    {
        abort_unless($request->user() && $request->user()->canAccessFeature('pricelist'), 403, 'Akses terbatas: Fitur Pricelist Gadget tidak diaktifkan untuk akun Anda.');

        $data = $this->loadPricelistData();

        if ($request->user()?->isGuest()) {
            $data = $this->maskPricelistDataForGuest($data);
        }

        return view('pricelist.index', [
            'data' => $data,
            'updatedAt' => $data['updated_at'] ?? 'Baru saja',
            'fileSource' => $data['file_source'] ?? 'Pricelist-Update-New-Update.xlsx',
            'sheets' => $data['sheets'] ?? [],
        ]);
    }

    /**
     * Sensor seluruh data harga, modal, margin, cashback, subsidi, dan nominal uang untuk akun Tamu (Guest).
     */
    private function maskPricelistDataForGuest(array $data): array
    {
        if (! isset($data['sheets']) || ! is_array($data['sheets'])) {
            return $data;
        }

        $priceKeywords = [
            'modal', 'srp', 'harga', 'margin', 'cashback', 'subsidi', 'mpl', 'pwp',
            'biaya', 'promo', 'net', 'cuan', 'up', 'down', 'ongkir', 'total',
        ];

        foreach ($data['sheets'] as $sheetKey => &$sheet) {
            if (! isset($sheet['data']) || ! is_array($sheet['data'])) {
                continue;
            }

            foreach ($sheet['data'] as &$row) {
                if (! is_array($row)) {
                    continue;
                }
                foreach ($row as $col => $val) {
                    $lowerCol = strtolower((string) $col);
                    $isPriceCol = false;
                    foreach ($priceKeywords as $kw) {
                        if (str_contains($lowerCol, $kw)) {
                            $isPriceCol = true;
                            break;
                        }
                    }

                    if ($isPriceCol || (is_numeric($val) && (float) $val > 100)) {
                        $row[$col] = 0;
                    }
                }
            }
        }
        unset($sheet, $row);

        return $data;
    }

    /**
     * Sync directly from Dropbox shared link
     */
    public function syncDropbox(Request $request): JsonResponse
    {
        abort_unless($request->user() && $request->user()->canAccessFeature('pricelist'), 403, 'Akses terbatas.');

        try {
            ini_set('memory_limit', '1024M');
            set_time_limit(300);

            $ch = curl_init($this->dropboxUrl);
            $fp = fopen($this->rawExcelPath, 'w+');
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if ($code !== 200 || ! file_exists($this->rawExcelPath) || filesize($this->rawExcelPath) < 1000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengunduh file terbaru dari Dropbox (HTTP: '.$code.').',
                ], 500);
            }

            $parsed = $this->parseExcelToJson();

            return response()->json([
                'success' => true,
                'message' => 'Pricelist berhasil disinkronkan langsung dari Dropbox!',
                'updated_at' => $parsed['updated_at'] ?? now()->timezone('Asia/Makassar')->format('d M Y, H:i').' WITA',
                'sheets' => $parsed['sheets'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi Dropbox: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if Dropbox file has changed using ETag/Head
     */
    public function checkDropboxStatus(Request $request): JsonResponse
    {
        abort_unless($request->user() && $request->user()->canAccessFeature('pricelist'), 403, 'Akses terbatas.');

        $data = $this->loadPricelistData();

        return response()->json([
            'success' => true,
            'updated_at' => $data['updated_at'] ?? 'Baru saja',
            'file_size' => file_exists($this->rawExcelPath) ? round(filesize($this->rawExcelPath) / (1024 * 1024), 2).' MB' : '0 MB',
        ]);
    }

    /**
     * Upload & Refresh Pricelist Excel (Optional Superadmin feature)
     */
    public function updateFromExcel(Request $request): JsonResponse
    {
        abort_unless($request->user() && $request->user()->canAccessFeature('pricelist'), 403, 'Akses terbatas.');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:51200'], // max 50MB
        ]);

        $uploadedFile = $request->file('file');
        $uploadedFile->move(storage_path('app'), 'pricelist_sample.xlsx');

        $this->parseExcelToJson();

        return response()->json([
            'success' => true,
            'message' => 'Pricelist berhasil diperbarui dari file Excel!',
        ]);
    }

    /**
     * Load JSON or fallback to parse Excel
     */
    private function loadPricelistData(): array
    {
        if (file_exists($this->dataPath)) {
            $json = @file_get_contents($this->dataPath);
            if ($json) {
                $decoded = json_decode($json, true);
                if (is_array($decoded) && isset($decoded['sheets'])) {
                    return $decoded;
                }
            }
        }

        return $this->parseExcelToJson();
    }

    /**
     * Parse the 4 requested sheets into high-performance JSON
     */
    private function parseExcelToJson(): array
    {
        if (! file_exists($this->rawExcelPath)) {
            return [
                'updated_at' => now()->timezone('Asia/Makassar')->format('d M Y, H:i').' WITA',
                'file_source' => 'Belum ada file',
                'sheets' => [
                    'harga_fix' => ['title' => 'HARGA FIX', 'total' => 0, 'data' => []],
                    'update_harga_sm' => ['title' => 'Update Harga - SM', 'total' => 0, 'data' => []],
                    'xiaomi' => ['title' => 'HP Xiaomi Store', 'total' => 0, 'data' => []],
                    'modal_imei' => ['title' => 'Modal By IMEI', 'total' => 0, 'data' => []],
                ],
            ];
        }

        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $reader = IOFactory::createReaderForFile($this->rawExcelPath);
        $reader->setReadDataOnly(false);
        $reader->setReadEmptyCells(false);
        $reader->setLoadSheetsOnly(['HARGA FIX', 'Update Harga - SM', 'HP XIAOMI STORE (OKT)', 'MODAL BY IMEI']);
        $ss = $reader->load($this->rawExcelPath);

        $getVal = function ($sheet, $col, $row) {
            $cell = $sheet->getCell($col.$row);
            $val = $cell->getOldCalculatedValue();
            if ($val === null || $val === '') {
                $raw = $cell->getValue();
                if (! is_string($raw) || ! str_starts_with($raw, '=')) {
                    $val = $raw;
                }
            }

            return $val;
        };

        // 1. HARGA FIX
        $sheetHF = $ss->getSheetByName('HARGA FIX');
        $rowsHF = [];
        if ($sheetHF) {
            $highestRowHF = $sheetHF->getHighestRow();
            for ($r = 3; $r <= $highestRowHF; $r++) {
                $type = trim((string) $getVal($sheetHF, 'A', $r));
                if ($type === '' || str_starts_with($type, '=')) {
                    continue;
                }

                $modalSesudah = (float) $getVal($sheetHF, 'B', $r);
                $srpPromo = (float) $getVal($sheetHF, 'C', $r);
                $cbPwp = (float) $getVal($sheetHF, 'D', $r);
                $cbTradeIn = (float) $getVal($sheetHF, 'E', $r);
                $ketProgram = trim((string) $getVal($sheetHF, 'F', $r));
                $modalAkhir = (float) $getVal($sheetHF, 'G', $r);
                $margin = (float) $getVal($sheetHF, 'H', $r);
                $marginModalAkhir = (float) $getVal($sheetHF, 'I', $r);
                $hargaMpl = (float) $getVal($sheetHF, 'J', $r);
                $adminMpl = (float) $getVal($sheetHF, 'K', $r);
                $marginMpl = (float) $getVal($sheetHF, 'L', $r);

                $brand = 'OTHER';
                if (stripos($type, 'APPLE') !== false || stripos($type, 'IPHONE') !== false || stripos($type, 'IPAD') !== false || stripos($type, 'MACBOOK') !== false) {
                    $brand = 'APPLE';
                } elseif (stripos($type, 'SAMSUNG') !== false) {
                    $brand = 'SAMSUNG';
                } elseif (stripos($type, 'OPPO') !== false) {
                    $brand = 'OPPO';
                } elseif (stripos($type, 'VIVO') !== false) {
                    $brand = 'VIVO';
                } elseif (stripos($type, 'XIAOMI') !== false || stripos($type, 'REDMI') !== false || stripos($type, 'POCO') !== false) {
                    $brand = 'XIAOMI';
                } elseif (stripos($type, 'REALME') !== false) {
                    $brand = 'REALME';
                } elseif (stripos($type, 'INFINIX') !== false) {
                    $brand = 'INFINIX';
                } elseif (stripos($type, 'TECNO') !== false) {
                    $brand = 'TECNO';
                }

                $rowsHF[] = [
                    'type' => $type,
                    'brand' => $brand,
                    'modal_sesudah_cb' => $modalSesudah,
                    'srp_promo' => $srpPromo,
                    'cb_pwp' => $cbPwp,
                    'cb_trade_in' => $cbTradeIn,
                    'ket_program' => $ketProgram,
                    'modal_akhir' => $modalAkhir,
                    'margin' => $margin,
                    'margin_modal_akhir' => $marginModalAkhir,
                    'harga_mpl' => $hargaMpl,
                    'admin_mpl' => $adminMpl,
                    'margin_mpl' => $marginMpl,
                ];
            }
        }

        // 2. Update Harga - SM
        $sheetSM = $ss->getSheetByName('Update Harga - SM');
        $rowsSM = [];
        if ($sheetSM) {
            $highestRowSM = $sheetSM->getHighestRow();
            for ($r = 2; $r <= $highestRowSM; $r++) {
                $brand = trim((string) $getVal($sheetSM, 'A', $r));
                $itemName = trim((string) $getVal($sheetSM, 'B', $r));
                if ($itemName === '' || str_starts_with($itemName, '=')) {
                    continue;
                }

                $modalSebelum = (float) $getVal($sheetSM, 'C', $r);
                $srp = (float) $getVal($sheetSM, 'D', $r);
                $cbRegular = (float) $getVal($sheetSM, 'H', $r);
                $cbPwp = (float) $getVal($sheetSM, 'I', $r);
                $cbPartner = (float) $getVal($sheetSM, 'J', $r);
                $cbTradeIn = (float) $getVal($sheetSM, 'L', $r);
                $ketProgram = trim((string) $getVal($sheetSM, 'N', $r));
                $subsidiLeasing = (float) $getVal($sheetSM, 'O', $r);

                $rowsSM[] = [
                    'brand' => $brand ?: 'LAINNYA',
                    'item_name' => $itemName,
                    'modal_sebelum_cb' => $modalSebelum,
                    'srp' => $srp,
                    'cb_regular' => $cbRegular,
                    'cb_pwp' => $cbPwp,
                    'cb_partner' => $cbPartner,
                    'cb_trade_in' => $cbTradeIn,
                    'ket_program' => $ketProgram,
                    'subsidi_leasing' => $subsidiLeasing,
                ];
            }
        }

        // 3. HP Xiaomi Store
        $sheetXM = $ss->getSheetByName('HP XIAOMI STORE (OKT)');
        $rowsXM = [];
        if ($sheetXM) {
            $highestRowXM = $sheetXM->getHighestRow();
            for ($r = 2; $r <= $highestRowXM; $r++) {
                $productId = trim((string) $getVal($sheetXM, 'A', $r));
                $variant = trim((string) $getVal($sheetXM, 'B', $r));
                $name = trim((string) $getVal($sheetXM, 'G', $r));
                if ($name === '' && $productId === '') {
                    continue;
                }

                $srp = (float) $getVal($sheetXM, 'C', $r);
                $dtpWoTax = (float) $getVal($sheetXM, 'D', $r);
                $sipWithTax = (float) $getVal($sheetXM, 'J', $r);
                $marginRate = (float) $getVal($sheetXM, 'K', $r);
                $margin = (float) $getVal($sheetXM, 'L', $r);
                $marginPlusTax = (float) $getVal($sheetXM, 'M', $r);

                $rowsXM[] = [
                    'product_id' => $productId,
                    'name' => $name,
                    'variant' => $variant,
                    'srp' => $srp,
                    'dtp_wo_tax' => $dtpWoTax,
                    'sip_with_tax' => $sipWithTax,
                    'margin_rate' => $marginRate,
                    'margin' => $margin,
                    'margin_plus_tax' => $marginPlusTax,
                ];
            }
        }

        // 4. Modal By IMEI
        $sheetIMEI = $ss->getSheetByName('MODAL BY IMEI');
        $rowsIMEI = [];
        if ($sheetIMEI) {
            $highestRowIMEI = $sheetIMEI->getHighestRow();
            for ($r = 2; $r <= $highestRowIMEI; $r++) {
                $imei = trim((string) $getVal($sheetIMEI, 'A', $r));
                $tipe = trim((string) $getVal($sheetIMEI, 'B', $r));
                if ($imei === '' && $tipe === '') {
                    continue;
                }

                $modal = (float) $getVal($sheetIMEI, 'C', $r);

                $rowsIMEI[] = [
                    'imei' => $imei,
                    'tipe' => $tipe,
                    'modal' => $modal,
                ];
            }
        }

        $result = [
            'updated_at' => now()->timezone('Asia/Makassar')->format('d M Y, H:i').' WITA',
            'file_source' => 'Pricelist-Update-New-Update.xlsx',
            'sheets' => [
                'harga_fix' => [
                    'title' => 'HARGA FIX',
                    'total' => count($rowsHF),
                    'data' => $rowsHF,
                ],
                'update_harga_sm' => [
                    'title' => 'Update Harga - SM',
                    'total' => count($rowsSM),
                    'data' => $rowsSM,
                ],
                'xiaomi' => [
                    'title' => 'HP Xiaomi Store',
                    'total' => count($rowsXM),
                    'data' => $rowsXM,
                ],
                'modal_imei' => [
                    'title' => 'Modal By IMEI',
                    'total' => count($rowsIMEI),
                    'data' => $rowsIMEI,
                ],
            ],
        ];

        @file_put_contents($this->dataPath, json_encode($result, JSON_UNESCAPED_SLASHES));

        return $result;
    }
}
