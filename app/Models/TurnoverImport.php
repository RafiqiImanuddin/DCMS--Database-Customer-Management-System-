<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'original_name',
    'stored_path',
    'batch_label',
    'period_start',
    'period_end',
    'status',
    'row_count',
    'success_count',
    'failed_count',
    'error_log',
])]
class TurnoverImport extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turnovers(): HasMany
    {
        return $this->hasMany(Turnover::class);
    }
}

