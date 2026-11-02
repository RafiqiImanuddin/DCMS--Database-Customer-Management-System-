<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'original_name', 'stored_path', 'batch_label', 'period_start', 'period_end', 'status', 'row_count', 'success_count', 'failed_count', 'error_log'])]
class CrossSellingImport extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
