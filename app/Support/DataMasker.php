<?php

namespace App\Support;

class DataMasker
{
    /**
     * Cek apakah user yang sedang terautentikasi adalah akun Tamu (Guest).
     */
    public static function isGuest(): bool
    {
        return auth()->check() && (auth()->user()->role ?? '') === 'guest';
    }

    /**
     * Sensor nomor telepon / WhatsApp konsumen.
     * Contoh: 081234567890 -> 0812****890
     */
    public static function maskPhone(?string $phone): string
    {
        if (! static::isGuest() || $phone === null || trim($phone) === '') {
            return $phone ?? '-';
        }

        $raw = trim($phone);
        $digits = preg_replace('/\D/', '', $raw);

        if ($digits === '') {
            return '-';
        }

        $len = strlen($digits);
        if ($len >= 9) {
            $prefix = substr($digits, 0, 4);
            $suffix = substr($digits, -3);
            return $prefix . '****' . $suffix;
        }

        if ($len >= 6) {
            return substr($digits, 0, 2) . '****' . substr($digits, -2);
        }

        return '****';
    }

    /**
     * Sensor nilai mata uang rupiah (Omzet, GP, Harga, Modal, Target, Nilai Transaksi).
     * Contoh: Rp 15.000.000 -> Rp ***.***
     */
    public static function maskCurrency(int|float|string|null $amount, string $prefix = 'Rp '): string
    {
        if (! static::isGuest()) {
            if ($amount === null || $amount === '' || $amount == 0) {
                return $prefix . '-';
            }
            return $prefix . number_format((float) $amount, 0, ',', '.');
        }

        return $prefix . '***.***';
    }

    /**
     * Sensor angka atau nilai numerik murni tanpa prefix.
     */
    public static function maskNumber(int|float|string|null $number): string
    {
        if (! static::isGuest()) {
            if ($number === null || $number === '') {
                return '-';
            }
            return number_format((float) $number, 0, ',', '.');
        }

        return '***.***';
    }

    /**
     * Sensor persentase pencapaian (Run rate, GP %, Margin %, dll).
     * Contoh: 85.5% -> ***%
     */
    public static function maskPercentage(int|float|string|null $pct): string
    {
        if (! static::isGuest()) {
            if ($pct === null || $pct === '') {
                return '0%';
            }
            return round((float) $pct, 1) . '%';
        }

        return '***%';
    }

    /**
     * Sensor teks atau nilai umum.
     */
    public static function maskValue(mixed $value, string $mask = '***'): mixed
    {
        if (! static::isGuest()) {
            return $value;
        }

        return $mask;
    }

    /**
     * Link WhatsApp yang aman untuk mode Tamu (tidak membuka nomor asli).
     */
    public static function maskWaLink(?string $phone): string
    {
        if (static::isGuest() || ! $phone) {
            return 'javascript:void(0)';
        }

        $digits = preg_replace('/\D/', '', $phone);
        return 'https://wa.me/' . $digits;
    }
}
