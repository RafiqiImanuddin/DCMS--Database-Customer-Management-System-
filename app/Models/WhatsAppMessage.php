<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'whatsapp_chat_id',
    'message_id',
    'sender_type',
    'user_id',
    'agent_name',
    'message',
    'media_url',
    'media_type',
    'reply_to',
    'raw_payload',
    'status',
])]
class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected function casts(): array
    {
        return [
            'reply_to' => 'array',
            'raw_payload' => 'array',
        ];
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(WhatsAppChat::class, 'whatsapp_chat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
