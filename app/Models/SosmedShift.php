<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'employee_id',
    'admin_name',
    'shift_date',
    'shift_type',
    'start_time',
    'end_time',
    'notes',
    'created_by',
])]
class SosmedShift extends Model
{
    use HasFactory;

    /**
     * Definisi 4 Shift Kerja Admin Sosmed
     */
    public const SHIFTS = [
        'pagi' => [
            'name' => 'Pagi',
            'start' => '09:00',
            'end' => '16:30',
            'time_label' => '09:00 s/d 16:30',
            'badge_bg' => 'bg-sky-50',
            'badge_text' => 'text-sky-700',
            'badge_border' => 'border-sky-200',
            'badge_dot' => 'bg-sky-500',
            'icon' => 'sun',
            'card_accent' => 'border-sky-300 bg-sky-50/40 text-sky-900',
        ],
        'siang' => [
            'name' => 'Siang',
            'start' => '14:30',
            'end' => '22:00',
            'time_label' => '14:30 s/d 22:00',
            'badge_bg' => 'bg-indigo-50',
            'badge_text' => 'text-indigo-700',
            'badge_border' => 'border-indigo-200',
            'badge_dot' => 'bg-indigo-500',
            'icon' => 'cloud-sun',
            'card_accent' => 'border-indigo-300 bg-indigo-50/40 text-indigo-900',
        ],
        'lembur' => [
            'name' => 'Lembur',
            'start' => '09:00',
            'end' => '22:00',
            'time_label' => '09:00 s/d 22:00',
            'badge_bg' => 'bg-amber-50',
            'badge_text' => 'text-amber-800',
            'badge_border' => 'border-amber-200',
            'badge_dot' => 'bg-amber-500',
            'icon' => 'bolt',
            'card_accent' => 'border-amber-300 bg-amber-50/40 text-amber-900',
        ],
        'off' => [
            'name' => 'Off',
            'start' => null,
            'end' => null,
            'time_label' => 'Libur / Off',
            'badge_bg' => 'bg-slate-100',
            'badge_text' => 'text-slate-600',
            'badge_border' => 'border-slate-200',
            'badge_dot' => 'bg-slate-400',
            'icon' => 'moon',
            'card_accent' => 'border-slate-300 bg-slate-50 text-slate-800',
        ],
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Ambil metadata shift berdasarkan shift_type
     */
    public function getShiftMetaAttribute(): array
    {
        return self::SHIFTS[$this->shift_type] ?? [
            'name' => ucfirst($this->shift_type),
            'start' => $this->start_time,
            'end' => $this->end_time,
            'time_label' => $this->start_time && $this->end_time ? "{$this->start_time} s/d {$this->end_time}" : ($this->shift_type === 'off' ? 'Libur / Off' : '-'),
            'badge_bg' => 'bg-slate-100',
            'badge_text' => 'text-slate-600',
            'badge_border' => 'border-slate-200',
            'badge_dot' => 'bg-slate-400',
            'icon' => 'clock',
            'card_accent' => 'border-slate-200 bg-slate-50 text-slate-700',
        ];
    }

    /**
     * Label rentang jam kerja format ramah tampilan
     */
    public function getTimeRangeLabelAttribute(): string
    {
        if ($this->shift_type === 'off') {
            return 'Libur / Off';
        }
        if ($this->start_time && $this->end_time) {
            $start = substr((string) $this->start_time, 0, 5);
            $end = substr((string) $this->end_time, 0, 5);
            return "{$start} s/d {$end}";
        }
        return self::SHIFTS[$this->shift_type]['time_label'] ?? '-';
    }

    /**
     * Inisial nama admin
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->admin_name));
        if (empty($words)) {
            return 'AD';
        }
        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 2));
        }
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
}
