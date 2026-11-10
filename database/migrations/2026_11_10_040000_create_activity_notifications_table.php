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
        Schema::create('activity_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 100)->default('Sistem');
            $table->string('action', 50)->default('general');
            $table->string('title', 150);
            $table->text('message');
            $table->string('type', 20)->default('success'); // success, info, warning, danger
            $table->string('icon', 50)->nullable()->default('bell');
            $table->timestamps();

            $table->index('created_at');
        });

        // Backfill data awal dari riwayat upload Cross-Selling yang sudah ada
        try {
            $crossImports = DB::table('cross_selling_imports')
                ->leftJoin('users', 'cross_selling_imports.user_id', '=', 'users.id')
                ->select(
                    'cross_selling_imports.id',
                    'cross_selling_imports.user_id',
                    'cross_selling_imports.original_name',
                    'cross_selling_imports.row_count',
                    'cross_selling_imports.created_at',
                    'users.name as user_name'
                )
                ->orderByDesc('cross_selling_imports.created_at')
                ->limit(10)
                ->get();

            foreach ($crossImports as $imp) {
                $name = $imp->user_name ?: 'Superadmin';
                $count = number_format($imp->row_count ?: 0, 0, ',', '.');
                DB::table('activity_notifications')->insert([
                    'user_id' => $imp->user_id,
                    'user_name' => $name,
                    'action' => 'upload_cross_selling',
                    'title' => 'Upload Cross-Selling SAP',
                    'message' => "{$name} mengunggah {$count} data Cross-Selling ({$imp->original_name})",
                    'type' => 'success',
                    'icon' => 'upload',
                    'created_at' => $imp->created_at ?: now(),
                    'updated_at' => $imp->created_at ?: now(),
                ]);
            }
        } catch (\Throwable $e) {}

        // Backfill data awal dari riwayat upload Turnover yang sudah ada
        try {
            $turnoverImports = DB::table('turnover_imports')
                ->leftJoin('users', 'turnover_imports.user_id', '=', 'users.id')
                ->select(
                    'turnover_imports.id',
                    'turnover_imports.user_id',
                    'turnover_imports.original_name',
                    'turnover_imports.row_count',
                    'turnover_imports.created_at',
                    'users.name as user_name'
                )
                ->orderByDesc('turnover_imports.created_at')
                ->limit(10)
                ->get();

            foreach ($turnoverImports as $imp) {
                $name = $imp->user_name ?: 'Superadmin';
                $count = number_format($imp->row_count ?: 0, 0, ',', '.');
                DB::table('activity_notifications')->insert([
                    'user_id' => $imp->user_id,
                    'user_name' => $name,
                    'action' => 'upload_turnover',
                    'title' => 'Upload Turnover SAP',
                    'message' => "{$name} mengunggah {$count} data Turnover SAP ({$imp->original_name})",
                    'type' => 'success',
                    'icon' => 'upload',
                    'created_at' => $imp->created_at ?: now(),
                    'updated_at' => $imp->created_at ?: now(),
                ]);
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_notifications');
    }
};
