<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class FileManagerController extends Controller
{
    /**
     * Tampilkan File Manager Berkas Klaim AR per Cabang & Folder
     */
    public function index(Request $request)
    {
        $claimsData = $this->loadClaimsData();
        $selectedCabang = $request->query('cabang', 'ALL');
        $searchQuery = trim((string) $request->query('q', ''));

        // Kelompokkan berkas klaim per cabang toko
        $folders = [];
        $files = [];
        $totalItems = 0;
        $totalOmzet = 0;
        $totalPenalty = 0;

        foreach ($claimsData as $item) {
            $cab = !empty($item['cabang']) ? trim((string) $item['cabang']) : 'LAINNYA';
            if (!isset($folders[$cab])) {
                $folders[$cab] = [
                    'cabang' => $cab,
                    'count' => 0,
                    'omzet' => 0,
                    'penalty' => 0,
                    'pics' => [],
                    'statuses' => [],
                    'items' => [],
                ];
            }

            $folders[$cab]['count']++;
            $folders[$cab]['omzet'] += (float) ($item['hargaJual'] ?? 0);
            $folders[$cab]['penalty'] += (float) ($item['penaltyAmount'] ?? 0);
            if (!empty($item['picSosmed'])) {
                $folders[$cab]['pics'][$item['picSosmed']] = true;
            }
            if (!empty($item['status'])) {
                $folders[$cab]['statuses'][$item['status']] = true;
            }
            $folders[$cab]['items'][] = $item;

            $totalItems++;
            $totalOmzet += (float) ($item['hargaJual'] ?? 0);
            $totalPenalty += (float) ($item['penaltyAmount'] ?? 0);
        }

        ksort($folders);

        // Ambil data karyawan PIC untuk pencocokan nomor WhatsApp
        $employees = Employee::query()
            ->where('status', 'active')
            ->select(['id', 'name', 'role', 'cabang', 'phone'])
            ->get();

        $employeePhoneMap = [];
        foreach ($employees as $emp) {
            $nameKey = strtolower(trim($emp->name));
            $cleanPhone = preg_replace('/\D/', '', (string) $emp->phone);
            if (!empty($cleanPhone)) {
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
                $employeePhoneMap[$nameKey] = $cleanPhone;
            }
        }

        // Susun daftar berkas per cabang
        $todayDate = date('Y-m-d');
        foreach ($folders as $cab => $info) {
            if ($selectedCabang !== 'ALL' && $cab !== $selectedCabang) {
                continue;
            }

            $picsList = array_keys($info['pics']);
            $primaryPic = !empty($picsList) ? $picsList[0] : '-';
            $detectedPhone = '';
            foreach ($picsList as $picName) {
                $pKey = strtolower(trim($picName));
                if (isset($employeePhoneMap[$pKey])) {
                    $detectedPhone = $employeePhoneMap[$pKey];
                    break;
                }
            }

            $fileName = "Klaim_AR_{$cab}_{$todayDate}.xlsx";
            if ($searchQuery !== '') {
                $haystack = strtolower("{$fileName} {$cab} " . implode(' ', $picsList));
                if (!str_contains($haystack, strtolower($searchQuery))) {
                    continue;
                }
            }

            $files[] = [
                'id' => "file_{$cab}",
                'name' => $fileName,
                'extension' => 'xlsx',
                'cabang' => $cab,
                'date' => $todayDate,
                'items_count' => $info['count'],
                'total_omzet' => $info['omzet'],
                'total_penalty' => $info['penalty'],
                'pics' => $picsList,
                'primary_pic' => $primaryPic,
                'pic_phone' => $detectedPhone,
                'status' => in_array('SHARED', array_keys($info['statuses']), true) ? 'Sudah Di Share' : 'Siap Dikirim',
                'items' => $info['items'],
            ];
        }

        $stats = [
            'total_folders' => count($folders),
            'total_files' => count($files),
            'total_items' => $totalItems,
            'total_omzet' => $totalOmzet,
            'total_penalty' => $totalPenalty,
        ];

        return view('file-manager.index', compact(
            'folders',
            'files',
            'selectedCabang',
            'searchQuery',
            'stats',
            'employees'
        ));
    }

    /**
     * Unduh file Excel klaim AR untuk cabang tertentu
     */
    public function downloadBranchExcel(string $cabang)
    {
        $claimsData = $this->loadClaimsData();
        $branchItems = array_values(array_filter($claimsData, function ($item) use ($cabang) {
            $cab = !empty($item['cabang']) ? trim((string) $item['cabang']) : 'LAINNYA';
            return strcasecmp($cab, $cabang) === 0;
        }));

        if (empty($branchItems)) {
            return redirect()->back()->with('error', "Tidak ada data klaim AR untuk cabang {$cabang}.");
        }

        $spreadsheet = $this->generateSpreadsheetForBranch($cabang, $branchItems);
        $fileName = "Klaim_AR_{$cabang}_" . date('Y-m-d') . ".xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Unduh paket berkas cabang terpilih dalam SATU file ZIP
     */
    public function downloadBatchZip(Request $request)
    {
        $selectedCabangList = (array) $request->input('cabang_list', []);
        if (empty($selectedCabangList)) {
            return response()->json(['success' => false, 'message' => 'Pilih setidaknya satu cabang berkas untuk diunduh.'], 422);
        }

        $claimsData = $this->loadClaimsData();
        $zipFileName = 'Paket_Klaim_AR_' . count($selectedCabangList) . '_Cabang_' . date('Ymd_His') . '.zip';
        $tempZipPath = tempnam(sys_get_temp_dir(), 'claim_zip_');

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['success' => false, 'message' => 'Gagal membuat arsip ZIP di server.'], 500);
        }

        $addedCount = 0;
        foreach ($selectedCabangList as $cabang) {
            $cabang = trim((string) $cabang);
            $branchItems = array_values(array_filter($claimsData, function ($item) use ($cabang) {
                $cab = !empty($item['cabang']) ? trim((string) $item['cabang']) : 'LAINNYA';
                return strcasecmp($cab, $cabang) === 0;
            }));

            if (empty($branchItems)) {
                continue;
            }

            $spreadsheet = $this->generateSpreadsheetForBranch($cabang, $branchItems);
            $tempExcelPath = tempnam(sys_get_temp_dir(), 'claim_xlsx_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempExcelPath);

            $zip->addFile($tempExcelPath, "Klaim_AR_{$cabang}_" . date('Y-m-d') . ".xlsx");
            $addedCount++;
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($tempZipPath);
            return response()->json(['success' => false, 'message' => 'Tidak ada data klaim valid dari cabang yang dipilih.'], 404);
        }

        return response()->download($tempZipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Buat format pesan ringkasan dan tautan WhatsApp untuk dikirim ke PIC
     */
    public function sendToPic(Request $request)
    {
        $cabangList = (array) $request->input('cabang_list', []);
        $targetPhone = preg_replace('/\D/', '', (string) $request->input('phone', ''));
        $targetPicName = trim((string) $request->input('pic_name', ''));

        if (empty($cabangList)) {
            return response()->json(['success' => false, 'message' => 'Pilih cabang terlebih dahulu.'], 422);
        }

        $claimsData = $this->loadClaimsData();
        $todayStr = date('d/m/Y');

        $messageLines = [];
        $messageLines[] = "📋 *DISTRIBUSI BERKAS KLAIM AR - GADGETMART*";
        $messageLines[] = "Tanggal Siklus: {$todayStr}";
        if ($targetPicName) {
            $messageLines[] = "Kepada PIC: *{$targetPicName}*";
        }
        $messageLines[] = "------------------------------------------";

        $grandItems = 0;
        $grandPenalty = 0;

        foreach ($cabangList as $cabang) {
            $branchItems = array_values(array_filter($claimsData, function ($item) use ($cabang) {
                $cab = !empty($item['cabang']) ? trim((string) $item['cabang']) : 'LAINNYA';
                return strcasecmp($cab, $cabang) === 0;
            }));

            $itemCount = count($branchItems);
            $penaltySum = array_sum(array_column($branchItems, 'penaltyAmount'));
            $grandItems += $itemCount;
            $grandPenalty += $penaltySum;

            $messageLines[] = "🏬 *Cabang {$cabang}*";
            $messageLines[] = "• Jumlah AR: {$itemCount} Transaksi";
            $messageLines[] = "• Potensi Denda: Rp " . number_format($penaltySum, 0, ',', '.');
            
            // Sertakan 3 sample invoice pertama
            $sampleDocs = array_slice(array_column($branchItems, 'docNumber'), 0, 3);
            if (!empty($sampleDocs)) {
                $messageLines[] = "• Contoh No. AR: " . implode(', ', $sampleDocs) . ($itemCount > 3 ? " (+ " . ($itemCount - 3) . " lainnya)" : "");
            }
            $messageLines[] = "";
        }

        $messageLines[] = "------------------------------------------";
        $messageLines[] = "📊 *TOTAL KESELURUHAN:*";
        $messageLines[] = "• Total AR Belum Sosmed: *{$grandItems} Faktur*";
        $messageLines[] = "• Total Potensi Denda: *Rp " . number_format($grandPenalty, 0, ',', '.') . "*";
        $messageLines[] = "";
        $messageLines[] = "⚠️ *PENTING - BATAS WAKTU REVISI (H+1):*";
        $messageLines[] = "Mohon PIC Cabang segera merevisi transaksi SAP menjadi *BJM.SOS* sebelum masa tenggang H+1 berakhir agar tidak terkena denda kasir Rp 50.000 per unit HP.";
        $messageLines[] = "";
        $messageLines[] = "_Pesan otomatis terpusat dari DCMS Dashboard Manager._";

        $fullText = implode("\n", $messageLines);
        $encodedText = rawurlencode($fullText);

        $whatsappUrl = '';
        if ($targetPhone) {
            if (str_starts_with($targetPhone, '0')) {
                $targetPhone = '62' . substr($targetPhone, 1);
            }
            $whatsappUrl = "https://wa.me/{$targetPhone}?text={$encodedText}";
        } else {
            $whatsappUrl = "https://api.whatsapp.com/send?text={$encodedText}";
        }

        return response()->json([
            'success' => true,
            'whatsapp_url' => $whatsappUrl,
            'phone' => $targetPhone,
            'pic_name' => $targetPicName,
            'message_preview' => $fullText,
            'total_items' => $grandItems,
            'total_penalty' => $grandPenalty,
        ]);
    }

    /**
     * Muat data klaim AR dari storage JSON
     */
    private function loadClaimsData(): array
    {
        $filePath = storage_path('app/ar_claims.json');
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Buat instance PhpSpreadsheet rapi untuk satu cabang
     */
    private function generateSpreadsheetForBranch(string $cabang, array $items): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Klaim {$cabang}", 0, 31));

        // 1. Banner Judul
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', "DAFTAR REKAPITULASI KLAIM FAKTUR AR BELUM SOSMED - CABANG {$cabang}");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A2', "Dicetak dari DCMS File Manager pada " . date('d F Y H:i') . " | Siklus Pengawasan Denda & Validasi Toko");
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        // 2. Header Kolom
        $headers = [
            'NO',
            'TANGGAL FAKTUR',
            'PERIODE',
            'NO. DOKUMEN AR',
            'CUSTOMER',
            'NO. WHATSAPP',
            'KODE SOSMED',
            'PIC SOSMED',
            'CABANG',
            'NAMA BARANG',
            'HARGA JUAL',
            'POTENSI DENDA',
            'FORMAT KEEP WA',
            'SALES AR SAP'
        ];

        $headerRow = 4;
        $colIdx = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIdx, $headerRow, $h);
            $colIdx++;
        }

        $headerRange = 'A4:N4';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        // 3. Isi Data
        $row = 5;
        foreach ($items as $idx => $it) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $it['postingDate'] ?? '-');
            $sheet->setCellValue('C' . $row, $it['periode'] ?? '-');
            $sheet->setCellValue('D' . $row, $it['docNumber'] ?? '-');
            $sheet->setCellValue('E' . $row, $it['customerName'] ?? '-');
            $sheet->setCellValue('F' . $row, $it['customerPhone'] ?? '-');
            $sheet->setCellValue('G' . $row, $it['targetSosmedCode'] ?? '-');
            $sheet->setCellValue('H' . $row, $it['picSosmed'] ?? '-');
            $sheet->setCellValue('I' . $row, $it['cabang'] ?? '-');
            $sheet->setCellValue('J' . $row, $it['productName'] ?? '-');
            $sheet->setCellValue('K' . $row, (float) ($it['hargaJual'] ?? 0));
            $sheet->setCellValue('L' . $row, (!empty($it['isHandphone']) ? 'DENDA Rp 50.000' : 'PASS Rp 0'));
            $sheet->setCellValue('M' . $row, $it['keepText'] ?? '-');
            $sheet->setCellValue('N' . $row, $it['currentSalesCode'] ?? '-');

            // Format uang
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            // Highlighting denda
            if (!empty($it['isHandphone'])) {
                $sheet->getStyle("L{$row}")->getFont()->setBold(true)->getColor()->setRGB('BE123C');
            } else {
                $sheet->getStyle("L{$row}")->getColor()->setRGB('64748B');
            }

            $row++;
        }

        // Border keliling
        $lastRow = $row - 1;
        if ($lastRow >= 5) {
            $sheet->getStyle("A4:N{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
        }

        // Auto width
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
