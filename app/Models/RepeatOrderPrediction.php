<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cross_selling_import_id', 'customer_vendor_code', 'customer_vendor_name', 'no_hp_customer', 'cabang', 'item_code', 'dscription', 'first_order_date', 'last_order_date', 'transaction_count', 'total_quantity', 'total_value', 'average_gap_days', 'predicted_repeat_date', 'confidence', 'classification', 'period_label'])]
class RepeatOrderPrediction extends Model
{
    protected function casts(): array
    {
        return [
            'first_order_date' => 'date',
            'last_order_date' => 'date',
            'predicted_repeat_date' => 'date',
            'total_quantity' => 'decimal:4',
            'total_value' => 'decimal:2',
            'average_gap_days' => 'decimal:2',
        ];
    }

    public function crossSellingImport(): BelongsTo
    {
        return $this->belongsTo(CrossSellingImport::class);
    }
}
