<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_chats', function (Blueprint $table) {
            $table->id();
            $table->string('customer_vendor_code', 100)->nullable()->index();
            $table->string('customer_name', 150);
            $table->string('phone_number', 50)->index();
            $table->string('cabang', 100)->nullable()->index();
            $table->string('folder', 30)->default('all')->index();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('format_type', 50)->default('Keep');
            $table->string('barang_keep')->nullable();
            $table->date('tanggal_pengambilan')->nullable();
            $table->text('notes')->nullable();
            $table->text('tags_json')->nullable();
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->unsignedInteger('unread_count')->default(0);
            $table->timestamps();
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_chat_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type', 20)->default('customer');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('agent_name')->nullable();
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->string('status', 30)->default('sent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_chats');
    }
};

