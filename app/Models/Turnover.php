<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'turnover_import_id',
    'cabang',
    'posting_date',
    'document_number',
    'slp_code',
    'slp_name',
    'remarks',
    'sales_cabang',
    'dpp',
    'gross_profit',
    'total',
    'period_label',
    'cleaning_hash',
])]
class Turnover extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'posting_date' => 'date',
            'dpp' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(TurnoverImport::class, 'turnover_import_id');
    }
}

