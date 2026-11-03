<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'repeat_order_prediction_id', 'customer_vendor_code', 'customer_vendor_name', 'no_hp_customer', 'classification', 'item_code', 'dscription', 'status', 'assigned_at'])]
class LeadCustomer extends Model
{
    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(RepeatOrderPrediction::class, 'repeat_order_prediction_id');
    }

    public function getCategoryAttribute(): string
    {
        return \App\Support\BrandCategory::resolve($this->item_code);
    }
}
