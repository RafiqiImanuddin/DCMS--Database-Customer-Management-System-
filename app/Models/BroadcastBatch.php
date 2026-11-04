<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'title',
    'message',
    'image_url',
    'total_recipients',
    'total_sent',
    'total_failed',
    'delivery_rate',
    'status',
    'sent_at',
])]
class BroadcastBatch extends Model
{
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'delivery_rate' => 'float',
            'total_recipients' => 'integer',
            'total_sent' => 'integer',
            'total_failed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(BroadcastReport::class, 'broadcast_batch_id');
    }
}
