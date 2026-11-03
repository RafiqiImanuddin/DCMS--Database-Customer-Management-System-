<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastMedia extends Model
{
    use HasFactory;

    protected $table = 'broadcast_media';

    protected $fillable = [
        'user_id',
        'filename',
        'mime_type',
        'file_size',
        'data',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return route('broadcasts.media.show', ['media' => $this->id], true);
    }
}

