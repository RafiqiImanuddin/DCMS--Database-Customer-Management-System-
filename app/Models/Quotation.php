<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quotation_number',
        'customer_name',
        'customer_destination',
        'letter_date',
        'letter_city',
        'greeting_text',
        'items',
        'subtotal',
        'discount',
        'total_amount',
        'notes',
        'closing_text',
        'signer_name',
        'signer_title',
        'include_signature',
        'signature_image',
        'signature_scale',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
        'letter_date' => 'date:Y-m-d',
        'include_signature' => 'boolean',
        'signature_scale' => 'integer',
        'subtotal' => 'integer',
        'discount' => 'integer',
        'total_amount' => 'integer',
    ];

    /**
     * Relasi ke user pembuat surat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dapatkan teks tanggal terformat Bahasa Indonesia (misal: "10 November 2026")
     */
    public function getFormattedDateAttribute(): string
    {
        if (! $this->letter_date) {
            return '';
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $carbon = Carbon::parse($this->letter_date);
        $day = $carbon->format('d');
        $month = $months[(int) $carbon->format('m')] ?? $carbon->format('F');
        $year = $carbon->format('Y');

        return "{$day} {$month} {$year}";
    }

    /**
     * Dapatkan URL tanda tangan (hanya jika ada file kustom yang diunggah, jika tidak ada maka polosan / null).
     */
    public function getSignatureUrlAttribute(): ?string
    {
        if ($this->signature_image && file_exists(public_path($this->signature_image))) {
            return asset($this->signature_image);
        }

        return null;
    }

    /**
     * Generate nomor surat penawaran unik berikutnya
     */
    public static function generateNextNumber(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        $prefix = 'SPH/GM/' . $date->format('Ym') . '/';

        $lastQuotation = static::where('quotation_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($lastQuotation && preg_match('/(\d+)$/', $lastQuotation->quotation_number, $matches)) {
            $seq = (int) $matches[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
