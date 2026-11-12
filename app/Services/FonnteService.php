<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Daftarkan URL Webhook ke server Fonnte secara otomatis via API update-device.
     *
     * @return array{success: bool, message: string, raw: array}
     */
    public function setWebhook(string $webhookUrl, ?string $overrideToken = null): array
    {
        $token = $overrideToken ?: $this->getToken();
        if ($token === '') {
            return [
                'success' => false,
                'message' => 'Token Fonnte belum diisi.',
                'raw' => [],
            ];
        }

        try {
            $device = $this->getDeviceStatus($token);
            $deviceName = ($device['name'] ?? '') !== '-' ? $device['name'] : 'Broadcast';

            $res = Http::timeout(10)
                ->withHeaders(['Authorization' => $token])
                ->withoutVerifying()
                ->asForm()
                ->post('https://api.fonnte.com/update-device', [
                    'name' => $deviceName,
                    'webhook' => $webhookUrl,
                    'autoread' => 'true',
                ]);

            $json = $res->json() ?? [];
            $ok = (bool) ($json['status'] ?? false);

            return [
                'success' => $ok,
                'message' => (string) ($json['detail'] ?? ($ok ? 'Webhook berhasil didaftarkan ke Fonnte.' : 'Gagal mendaftarkan webhook.')),
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Koneksi ke server Fonnte gagal: ' . $e->getMessage(),
                'raw' => [],
            ];
        }
    }

    /**
     * Dapatkan Token Fonnte dari pengaturan sistem.
     */
    public function getToken(): string
    {
        return trim((string) Setting::get('fonnte_token', config('services.fonnte.token', 'NMGid9rgBKYAAyZ7wmSn')));
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
    public function getDeviceStatus(?string $overrideToken = null, bool $forceRefresh = false): array
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
                'quota' => '-',
                'message' => 'Token Fonnte belum diisi.',
                'raw' => [],
            ];
        }

        $cacheKey = 'fonnte_device_status_' . md5($token);
        if (! $forceRefresh && cache()->has($cacheKey)) {
            $cached = cache()->get($cacheKey);
            if (is_array($cached) && ! empty($cached['success'])) {
                return $cached;
            }
        }

        try {
            $res = Http::timeout(15)
                ->connectTimeout(10)
                ->retry(2, 300, throw: false)
                ->withHeaders(['Authorization' => $token])
                ->withoutVerifying()
                ->post('https://api.fonnte.com/device');

            if ($res->ok()) {
                $json = $res->json() ?? [];
                $deviceStatus = (string) ($json['device_status'] ?? $json['status'] ?? 'unknown');
                $isOnline = in_array(strtolower($deviceStatus), ['connect', 'connected', 'ready'], true);

                $quota = (string) ($json['quota'] ?? '-');
                $package = (string) ($json['package'] ?? 'Free');
                if ($quota !== '-' && $quota !== '') {
                    $packageText = "{$package} ({$quota} Kuota)";
                } else {
                    $packageText = $package;
                }

                $result = [
                    'success' => true,
                    'status' => $isOnline ? 'Connected' : ucfirst($deviceStatus),
                    'device' => (string) ($json['device'] ?? '-'),
                    'name' => (string) ($json['name'] ?? '-'),
                    'package' => $packageText,
                    'expired' => (string) ($json['expired'] ?? '-'),
                    'quota' => $quota,
                    'message' => $isOnline ? 'Device Fonnte Terhubung dan Siap Digunakan' : "Device Fonnte: {$deviceStatus}",
                    'raw' => $json,
                ];

                if ($isOnline) {
                    cache()->put($cacheKey, $result, now()->addMinutes(3));
                }

                return $result;
            }

            return [
                'success' => false,
                'status' => 'Error '.$res->status(),
                'device' => '-',
                'name' => '-',
                'package' => '-',
                'expired' => '-',
                'quota' => '-',
                'message' => 'Gagal membaca status device: HTTP '.$res->status(),
                'raw' => $res->json() ?? [],
            ];
        } catch (\Throwable $e) {
            if (cache()->has($cacheKey)) {
                return cache()->get($cacheKey);
            }

            return [
                'success' => false,
                'status' => 'Offline',
                'device' => '-',
                'name' => '-',
                'package' => '-',
                'expired' => '-',
                'quota' => '-',
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

            // Hilangkan markdown WA (*, _, ~) untuk membaca key
            $plainLine = trim(preg_replace('/[*_~]/u', '', $rawLine));

            // Abaikan header format / footer disclaimer
            if (preg_match('/^(wajib mencantumkan|format keep|format kredit|format preorder|format po|format\s+transaksi)/i', $plainLine)) {
                if (preg_match('/^wajib mencantumkan/i', $plainLine)) {
                    $currentField = '';
                }
                continue;
            }

            // Abaikan jika baris tanggal keep agar tidak menimpa remaks
            if (preg_match('/^(tanggal\s*keep|tgl\s*keep)/i', $plainLine)) {
                $currentField = '';
                continue;
            }

            // Deteksi pemisah key-value: titik dua (:) atau sama dengan (=)
            $colonPos = strpos($plainLine, ':');
            $equalPos = strpos($plainLine, '=');

            $sep = null;
            $sepIdx = false;
            if ($colonPos !== false && ($equalPos === false || $colonPos < $equalPos)) {
                $sep = ':';
                $sepIdx = $colonPos;
            } elseif ($equalPos !== false) {
                $sep = '=';
                $sepIdx = $equalPos;
            }

            if ($sep !== null && $sepIdx !== false) {
                $rawKey = trim(substr($plainLine, 0, $sepIdx));
                // Normalisasi key: lowercase, hilangkan tanda baca seperti titik atau strip
                $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9\s]/u', '', $rawKey));
                $cleanKey = trim(preg_replace('/\s+/u', ' ', $cleanKey));

                // Ekstraksi nilai value tanpa markdown WA
                $rawSepPos = strpos($rawLine, $sep);
                $rawVal = $rawSepPos !== false ? substr($rawLine, $rawSepPos + 1) : substr($plainLine, $sepIdx + 1);
                $val = trim($rawVal);
                $val = trim(preg_replace('/^[\s*_~]+|[\s*_~]+$/u', '', $val));
                $val = trim($val);

                if (in_array($cleanKey, ['nama', 'nama customer', 'nama konsumen', 'nama cust', 'customer', 'konsumen', 'cust', 'nama pembeli', 'pembeli'], true)) {
                    $currentField = 'nama';
                    $result['nama'] = $val;
                } elseif (in_array($cleanKey, [
                    'nomor wa', 'no wa', 'nowa', 'nomorwa', 'nomor', 'no hp', 'nohp', 'nomor hp',
                    'telepon', 'hp', 'no telp', 'notelp', 'nomor telepon', 'no telepon',
                    'no whatsapp', 'nomor whatsapp', 'kontak', 'no kontak', 'whatsapp', 'wa'
                ], true)) {
                    $currentField = 'nomor';
                    $result['nomor'] = $val;
                } elseif (in_array($cleanKey, ['barang', 'barang keep', 'barang po', 'barang kredit', 'barang preorder', 'unit', 'tipe', 'item', 'produk', 'nama barang'], true)) {
                    $currentField = 'barang';
                    $result['barang'] = $val;
                } elseif (in_array($cleanKey, [
                    'tanggal', 'tanggal pengambilan', 'tanggal ambil', 'tgl pengambilan', 'tgl ambil',
                    'tgl', 'jadwal ambil', 'jadwal', 'hari tgl', 'haritanggal', 'rencana ambil', 'rencana'
                ], true)) {
                    $currentField = 'tanggal';
                    $result['tanggal'] = $val;
                } elseif (in_array($cleanKey, ['catatan', 'keterangan', 'note', 'notes'], true)) {
                    $currentField = 'catatan';
                    $result['catatan'] = $val;
                } elseif (
                    in_array($cleanKey, [
                        'remaks', 'remarks', 'remak', 'remark', 'remaks sosmed', 'remarks sosmed',
                        'kode sosmed', 'admin sosmed', 'cs sosmed', 'sosmed', 'kode'
                    ], true)
                    || str_starts_with($cleanKey, 'remak')
                    || str_starts_with($cleanKey, 'remark')
                    || str_starts_with($cleanKey, 'sosmed')
                ) {
                    $currentField = 'remaks';
                    $result['remaks'] = $val;
                } elseif ($currentField === 'catatan' || in_array($cleanKey, ['umur', 'pembiayaan', 'dp', 'pekerjaan', 'leasing', 'tenor', 'angsuran'], true)) {
                    $currentField = 'catatan';
                    $cleanSub = trim(preg_replace('/^[\s*_~]+|[\s*_~]+$/u', '', $rawLine));
                    $result['catatan'] = $result['catatan'] !== '' ? $result['catatan'] . "\n" . $cleanSub : $cleanSub;
                } else {
                    $currentField = '';
                }
            } elseif ($currentField !== '') {
                $cleanSub = trim(preg_replace('/^[\s*_~]+|[\s*_~]+$/u', '', $rawLine));
                if (!preg_match('/^wajib mencantumkan/i', $cleanSub)) {
                    $result[$currentField] .= ($result[$currentField] !== '' ? "\n" : "") . $cleanSub;
                }
            }
        }

        // 1. Ekstraksi Khusus Field Remaks: Username - Kode SOSMED
        if ($result['remaks'] !== '') {
            $cleanRemaks = preg_replace('/[*_~]/u', '', $result['remaks']);
            $cleanRemaks = preg_replace('/@\S+/u', '', $cleanRemaks);
            $cleanRemaks = trim((string) preg_replace('/\s+/u', ' ', $cleanRemaks));

            // Jika ada tanda pemisah "-", cari bagian yang memuat SOSMED
            if (str_contains($cleanRemaks, '-')) {
                $parts = array_map('trim', explode('-', $cleanRemaks));
                foreach ($parts as $p) {
                    if (preg_match('/sosmed\s*\d+/i', $p)) {
                        $cleanRemaks = $p;
                        break;
                    }
                }
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
     * Deteksi kode cabang dari remarks (Kode SOSMED) atau Nama Grup WhatsApp.
     */
    public function detectCabangFromRemarks(?string $remarks, ?string $groupName = null): string
    {
        $text = strtoupper(trim(($remarks ?? '') . ' ' . ($groupName ?? '')));
        if ($text === '') {
            return '';
        }

        $branchMap = [
            'BANJARMASIN' => 'Banjarmasin',
            'BJM' => 'Banjarmasin',
            'BJMC' => 'Banjarmasin',
            'BANJARBARU' => 'Banjarbaru',
            'BJB' => 'Banjarbaru',
            'KOTABARU' => 'Kotabaru',
            'KTB' => 'Kotabaru',
            'PELAIHARI' => 'Pelaihari',
            'PLH' => 'Pelaihari',
            'BATULICIN' => 'Batulicin',
            'BTC' => 'Batulicin',
            'TANJUNG' => 'Tanjung',
            'TJG' => 'Tanjung',
            'PONTIANAK' => 'Pontianak',
            'PTK' => 'Pontianak',
            'BALIKPAPAN' => 'Balikpapan',
            'BPP' => 'Balikpapan',
            'SAMARINDA' => 'Samarinda',
            'SMD' => 'Samarinda',
            'KAPUAS' => 'Kapuas',
            'KPS' => 'Kapuas',
            'PALANGKARAYA' => 'Palangkaraya',
            'PKY' => 'Palangkaraya',
            'SINTANG' => 'Sintang',
            'STG' => 'Sintang',
            'SINGKAWANG' => 'Singkawang',
            'SKW' => 'Singkawang',
            'AMUNTAI' => 'Amuntai',
            'AMT' => 'Amuntai',
            'SAMPIT' => 'Sampit',
            'SPT' => 'Sampit',
            'SUNGAI DANAU' => 'Sungai Danau',
            'SDN' => 'Sungai Danau',
            'BARABAI' => 'Barabai',
            'BRB' => 'Barabai',
            'RANTAU' => 'Rantau',
            'RNT' => 'Rantau',
            'KANDANGAN' => 'Kandangan',
            'KDG' => 'Kandangan',
            'PARINGIN' => 'Paringin',
            'PRG' => 'Paringin',
        ];

        // 1. Cek dari remarks terlebih dahulu
        if ($remarks) {
            $rem = strtoupper(trim($remarks));
            foreach ($branchMap as $code => $branchName) {
                if (preg_match('/\b' . preg_quote($code, '/') . '\b/i', $rem) || str_contains($rem, $code)) {
                    return $branchName;
                }
            }
        }

        // 2. Fallback cek dari nama grup WhatsApp
        if ($groupName) {
            $grp = strtoupper(trim($groupName));
            foreach ($branchMap as $code => $branchName) {
                if (preg_match('/\b' . preg_quote($code, '/') . '\b/i', $grp) || str_contains($grp, $code)) {
                    return $branchName;
                }
            }
        }

        return '';
    }
}
