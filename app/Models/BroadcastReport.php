<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'repeat_order_prediction_id', 'broadcast_batch_id', 'customer_vendor_code', 'phone_number', 'channel', 'message', 'image_url', 'status', 'provider_response', 'sent_at'])]
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BroadcastBatch::class, 'broadcast_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
