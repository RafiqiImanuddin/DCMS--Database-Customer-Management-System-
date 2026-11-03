<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'whatsapp_chat_id',
    'sender_type',
    'user_id',
    'agent_name',
    'message',
    'media_url',
    'media_type',
    'status',
])]
class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    public function chat(): BelongsTo
    {
        return $this->belongsTo(WhatsAppChat::class, 'whatsapp_chat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

