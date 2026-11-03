<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_vendor_code',
    'customer_name',
    'phone_number',
    'cabang',
    'folder',
    'assigned_user_id',
    'format_type',
    'barang_keep',
    'tanggal_pengambilan',
    'notes',
    'tags_json',
    'last_message',
    'last_message_at',
    'unread_count',
])]
class WhatsAppChat extends Model
{
    protected $table = 'whatsapp_chats';

    protected function casts(): array
    {
        return [
            'tanggal_pengambilan' => 'date',
            'last_message_at' => 'datetime',
            'unread_count' => 'integer',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'whatsapp_chat_id')->orderBy('created_at', 'asc');
    }

    public function getTagsAttribute(): array
    {
        return json_decode($this->tags_json ?? '[]', true) ?: [];
    }

    public function setTagsAttribute(array $tags): void
    {
        $this->attributes['tags_json'] = json_encode(array_values(array_unique($tags)));
    }
}
