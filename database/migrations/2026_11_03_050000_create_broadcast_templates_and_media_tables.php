<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('broadcast_templates')) {
            Schema::create('broadcast_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->text('message');
                $table->text('image_url')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('broadcast_media')) {
            Schema::create('broadcast_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('filename');
                $table->string('mime_type', 100);
                $table->unsignedBigInteger('file_size')->default(0);
                $table->longText('data');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('broadcast_reports') && ! Schema::hasColumn('broadcast_reports', 'image_url')) {
            Schema::table('broadcast_reports', function (Blueprint $table) {
                $table->text('image_url')->nullable()->after('message');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('broadcast_reports') && Schema::hasColumn('broadcast_reports', 'image_url')) {
            Schema::table('broadcast_reports', function (Blueprint $table) {
                $table->dropColumn('image_url');
            });
        }

        Schema::dropIfExists('broadcast_media');
        Schema::dropIfExists('broadcast_templates');
    }
};

