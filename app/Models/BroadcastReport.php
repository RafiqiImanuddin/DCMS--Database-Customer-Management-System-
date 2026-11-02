<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'repeat_order_prediction_id', 'customer_vendor_code', 'phone_number', 'channel', 'message', 'status', 'provider_response', 'sent_at'])]
class BroadcastReport extends Model
{
    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(RepeatOrderPrediction::class, 'repeat_order_prediction_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
