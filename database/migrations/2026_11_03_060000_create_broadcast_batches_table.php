<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('broadcast_batches')) {
            Schema::create('broadcast_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title', 255)->default('Broadcast Campaign');
                $table->text('message');
                $table->string('image_url', 1000)->nullable();
                $table->unsignedInteger('total_recipients')->default(0);
                $table->unsignedInteger('total_sent')->default(0);
                $table->unsignedInteger('total_failed')->default(0);
                $table->decimal('delivery_rate', 5, 2)->default(0.00);
                $table->string('status', 30)->default('completed')->index();
                $table->timestamp('sent_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('broadcast_reports') && ! Schema::hasColumn('broadcast_reports', 'broadcast_batch_id')) {
            Schema::table('broadcast_reports', function (Blueprint $table) {
                $table->foreignId('broadcast_batch_id')->nullable()->after('repeat_order_prediction_id')->constrained('broadcast_batches')->nullOnDelete();
            });
        }

        // Backfill existing reports into a default batch if any exist
        if (Schema::hasTable('broadcast_reports') && Schema::hasTable('broadcast_batches')) {
            $existingReports = DB::table('broadcast_reports')->whereNull('broadcast_batch_id')->get();
            if ($existingReports->isNotEmpty()) {
                $first = $existingReports->first();
                $total = $existingReports->count();
                $sent = $existingReports->where('status', 'sent')->count();
                $failed = $existingReports->where('status', 'failed')->count();
                $rate = $total > 0 ? round(($sent / $total) * 100, 1) : 0;

                $batchId = DB::table('broadcast_batches')->insertGetId([
                    'user_id' => $first->user_id,
                    'title' => 'Broadcast Riwayat Awal',
                    'message' => $first->message ?? 'Broadcast Campaign',
                    'image_url' => $first->image_url ?? null,
                    'total_recipients' => $total,
                    'total_sent' => $sent,
                    'total_failed' => $failed,
                    'delivery_rate' => $rate,
                    'status' => 'completed',
                    'sent_at' => $first->sent_at ?? now(),
                    'created_at' => $first->created_at ?? now(),
                    'updated_at' => now(),
                ]);

                DB::table('broadcast_reports')->whereNull('broadcast_batch_id')->update([
                    'broadcast_batch_id' => $batchId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('broadcast_reports') && Schema::hasColumn('broadcast_reports', 'broadcast_batch_id')) {
            Schema::table('broadcast_reports', function (Blueprint $table) {
                $table->dropForeign(['broadcast_batch_id']);
                $table->dropColumn('broadcast_batch_id');
            });
        }

        Schema::dropIfExists('broadcast_batches');
    }
};
