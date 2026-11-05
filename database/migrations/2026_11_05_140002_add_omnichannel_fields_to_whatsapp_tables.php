<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_chats', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_chats', 'is_resolved')) {
                $table->boolean('is_resolved')->default(false)->index();
            }
            if (! Schema::hasColumn('whatsapp_chats', 'resolved_by')) {
                $table->string('resolved_by', 100)->nullable();
            }
            if (! Schema::hasColumn('whatsapp_chats', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable();
            }
        });

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_messages', 'message_id')) {
                $table->string('message_id', 100)->nullable()->index();
            }
            if (! Schema::hasColumn('whatsapp_messages', 'reply_to')) {
                $table->json('reply_to')->nullable();
            }
            if (! Schema::hasColumn('whatsapp_messages', 'raw_payload')) {
                $table->json('raw_payload')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_chats', function (Blueprint $table) {
            $table->dropColumn(['is_resolved', 'resolved_by', 'resolved_at']);
        });

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropColumn(['message_id', 'reply_to', 'raw_payload']);
        });
    }
};
