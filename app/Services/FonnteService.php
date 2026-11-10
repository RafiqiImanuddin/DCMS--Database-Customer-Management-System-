<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Dapatkan Token Fonnte dari pengaturan sistem.
     */
    public function getToken(): string
    {
        return trim((string) Setting::get('fonnte_token', config('services.fonnte.token', 'UjuTUowTBsUR1mzWhKFj')));
    }

    /**
     * Cek apakah token Fonnte sudah terkonfigurasi.
     */
    public function isConfigured(): bool
    {
        return $this->getToken() !== '';
    }

    /**
     * Ambil status device dan nomor WhatsApp dari Fonnte API.
     *
     * @return array{success: bool, status: string, device: string, name: string, package: string, expired: string, raw: array}
     */
    public function getDeviceStatus(?string $overrideToken = null): array
    {
        $token = $overrideToken ?: $this->getToken();
        if ($token === '') {
            return [
                'success' => false,
                'status' => 'Belum Dikonfigurasi',
                'device' => '-',
                'name' => '-',
                'package' => '-',
                'expired' => '-',
                'message' => 'Token Fonnte belum diisi.',
                'raw' => [],
            ];
        }

        try {
            $res = Http::timeout(6)
                ->withHeaders(['Authorization' => $token])
                ->withoutVerifying()
                ->post('https://api.fonnte.com/device');

            if ($res->ok()) {
                $json = $res->json() ?? [];
                $deviceStatus = (string) ($json['device_status'] ?? $json['status'] ?? 'unknown');
                $isOnline = in_array(strtolower($deviceStatus), ['connect', 'connected', 'ready'], true);

                return [
                    'success' => true,
                    'status' => $isOnline ? 'Connected' : ucfirst($deviceStatus),
                    'device' => (string) ($json['device'] ?? '-'),
                    'name' => (string) ($json['name'] ?? '-'),
                    'package' => (string) ($json['package'] ?? 'Fonnte Service'),
                    'expired' => (string) ($json['expired'] ?? '-'),
                    'message' => $isOnline ? 'Device Fonnte Terhubung dan Siap Digunakan' : "Device Fonnte: {$deviceStatus}",
                    'raw' => $json,
                ];
            }

            return [
                'success' => false,
                'status' => 'Error '.$res->status(),
                'device' => '-',
                'name' => '-',
                'package' => '-',
                'expired' => '-',
                'message' => 'Gagal membaca status device: HTTP '.$res->status(),
                'raw' => $res->json() ?? [],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 'Offline',
                'device' => '-',
                'name' => '-',
                'package' => '-',
                'expired' => '-',
                'message' => 'Koneksi ke server Fonnte gagal: '.$e->getMessage(),
                'raw' => [],
            ];
        }
    }

    /**
     * Kirim pesan WhatsApp melalui Fonnte API.
     *
     * @return array{success: bool, message: string, raw: array}
     */
    public function sendMessage(string $target, string $message, bool $isGroup = false, ?string $overrideToken = null): array
    {
        $token = $overrideToken ?: $this->getToken();
        if ($token === '' || trim($target) === '' || trim($message) === '') {
            return [
                'success' => false,
                'message' => 'Token, nomor tujuan, atau pesan tidak boleh kosong.',
                'raw' => [],
            ];
        }

        try {
            $cleanTarget = preg_replace('/\D/', '', $target);
            if (str_starts_with($cleanTarget, '08')) {
                $cleanTarget = '62' . substr($cleanTarget, 1);
            } elseif (str_starts_with($cleanTarget, '8')) {
                $cleanTarget = '62' . $cleanTarget;
            }

            $payload = [
                'target' => $isGroup ? $target : $cleanTarget,
                'message' => $message,
            ];
            if ($isGroup) {
                $payload['group'] = 'true';
            }

            $res = Http::timeout(10)
                ->withHeaders(['Authorization' => $token])
                ->withoutVerifying()
                ->post('https://api.fonnte.com/send', $payload);

            $json = $res->json() ?? [];
            $status = (bool) ($json['status'] ?? false);

            return [
                'success' => $status,
                'message' => (string) ($json['detail'] ?? ($status ? 'Pesan berhasil dikirim via Fonnte.' : 'Gagal mengirim pesan via Fonnte.')),
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('Fonnte send error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan jaringan saat mengirim via Fonnte: ' . $e->getMessage(),
                'raw' => [],
            ];
        }
    }

    /**
     * Parser format pesan KEEP sesuai spesifikasi Google Apps Script Fonnte.
     *
     * @return array{nama: string, nomor: string, barang: string, tanggal: string, catatan: string, remaks: string}
     */
    public function parseKeep(string $text): array
    {
        $result = [
            'nama' => '',
            'nomor' => '',
            'barang' => '',
            'tanggal' => '',
            'catatan' => '',
            'remaks' => '',
        ];

        if (trim($text) === '') {
            return $result;
        }

        // Normalisasi spasi Unicode non-breaking (\u00A0) dan baris baru
        $cleanText = str_replace(["\xC2\xA0", "\u{00A0}"], ' ', $text);
        $cleanText = str_replace(["\r\n", "\r"], "\n", $cleanText);

        $lines = explode("\n", $cleanText);
        $currentField = '';

        foreach ($lines as $rawLine) {
            $rawLine = trim($rawLine);
            if ($rawLine === '') {
                continue;
            }

            // Bersihkan format WA (*, _, ~) khusus untuk pengecekan label
            $plainLine = trim(preg_replace('/[*_~]/', '', $rawLine));

            // Abaikan jika baris tanggal keep agar tidak menimpa remaks
            if (preg_match('/^(tanggal\s*keep|tgl\s*keep)/i', $plainLine)) {
                $currentField = '';
                continue;
            }

            $colonIdx = strpos($plainLine, ':');
            if ($colonIdx !== false) {
                $key = strtolower(trim(substr($plainLine, 0, $colonIdx)));
                $rawColonIdx = strpos($rawLine, ':');
                $val = trim(preg_replace('/^[*_~]+|[*_~]+$/', '', substr($rawLine, $rawColonIdx + 1)));

                if ($key === 'nama') {
                    $currentField = 'nama';
                    $result['nama'] = $val;
                } elseif (in_array($key, ['nomor wa', 'no wa', 'nomor', 'no hp', 'telepon', 'hp'], true)) {
                    $currentField = 'nomor';
                    $result['nomor'] = $val;
                } elseif (in_array($key, ['barang', 'barang keep'], true)) {
                    $currentField = 'barang';
                    $result['barang'] = $val;
                } elseif (in_array($key, ['tanggal', 'tanggal pengambilan'], true)) {
                    $currentField = 'tanggal';
                    $result['tanggal'] = $val;
                } elseif ($key === 'catatan') {
                    $currentField = 'catatan';
                    $result['catatan'] = $val;
                } elseif (in_array($key, ['remaks', 'remarks'], true)) {
                    $currentField = 'remaks';
                    $result['remaks'] = $val;
                } else {
                    $currentField = '';
                }
            } elseif ($currentField !== '') {
                $result[$currentField] .= "\n" . trim(preg_replace('/^[*_~]+|[*_~]+$/', '', $rawLine));
            }
        }

        // 1. Ekstraksi Khusus Field Remaks: Username - Kode SOSMED
        if ($result['remaks'] !== '') {
            $cleanRemaks = preg_replace('/[*_~]/', '', $result['remaks']);
            $cleanRemaks = preg_replace('/@\S+/', '', $cleanRemaks);
            $cleanRemaks = trim((string) preg_replace('/\s+/', ' ', $cleanRemaks));

            // Jika ada tanda pemisah "-", ambil bagian setelah tanda "-" terakhir (Kode SOSMED)
            if (str_contains($cleanRemaks, '-')) {
                $parts = explode('-', $cleanRemaks);
                $cleanRemaks = trim((string) end($parts));
            }

            $result['remaks'] = $cleanRemaks;
        }

        // 2. Pembersihan Khusus Nomor WA (+62 / 08 / format spasi & strip)
        if ($result['nomor'] !== '') {
            $cleanNum = preg_replace('/\D/', '', (string) $result['nomor']);
            if (str_starts_with($cleanNum, '0')) {
                $cleanNum = '62' . substr($cleanNum, 1);
            }
            $result['nomor'] = $cleanNum;
        }

        return $result;
    }

    /**
     * Deteksi kode cabang dari remarks (Kode SOSMED).
     */
    public function detectCabangFromRemarks(?string $remarks): string
    {
        if (! $remarks) {
            return '';
        }

        $rem = strtoupper(trim($remarks));

        $branchMap = [
            'BJM' => 'Banjarmasin',
            'BJB' => 'Banjarbaru',
            'KTB' => 'Kotabaru',
            'PLH' => 'Pelaihari',
            'BTC' => 'Batulicin',
            'TJG' => 'Tanjung',
            'PTK' => 'Pontianak',
            'BPP' => 'Balikpapan',
            'SMD' => 'Samarinda',
            'KPS' => 'Kapuas',
            'PKY' => 'Palangkaraya',
            'STG' => 'Sintang',
            'SKW' => 'Singkawang',
        ];

        foreach ($branchMap as $code => $branchName) {
            if (preg_match('/\b' . preg_quote($code, '/') . '\b/i', $rem) || str_contains($rem, $code)) {
                return $branchName;
            }
        }

        return '';
    }
}
