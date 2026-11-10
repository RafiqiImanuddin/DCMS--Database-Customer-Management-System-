<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityNotificationController extends Controller
{
    /**
     * Get recent team activity notifications.
     */
    public function index(Request $request): JsonResponse
    {
        if (ActivityNotification::count() === 0) {
            $this->backfillFromImports();
        }

        $notifications = ActivityNotification::query()
            ->latest()
            ->limit(25)
            ->get()
            ->map(function (ActivityNotification $item): array {
                /** @var Carbon $createdAt */
                $createdAt = $item->created_at;

                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user_name' => $item->user_name ?: 'Sistem',
                    'action' => $item->action,
                    'title' => $item->title,
                    'message' => $item->message,
                    'type' => $item->type,
                    'icon' => $item->icon ?: 'bell',
                    'time_ago' => $createdAt->diffForHumans(),
                    'time_formatted' => $createdAt->translatedFormat('d M Y, H:i') . ' WITA',
                    'timestamp' => $createdAt->getTimestampMs(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'latest_timestamp' => $notifications->first()['timestamp'] ?? 0,
        ]);
    }

    /**
     * Backfill activity notifications from existing imports if empty.
     */
    protected function backfillFromImports(): void
    {
        try {
            $crossImports = \App\Models\CrossSellingImport::with('user')->latest()->limit(10)->get();
            foreach ($crossImports as $imp) {
                $name = $imp->user?->name ?: 'Superadmin';
                $count = number_format($imp->row_count ?: 0, 0, ',', '.');
                ActivityNotification::create([
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

            $turnoverImports = \App\Models\TurnoverImport::with('user')->latest()->limit(10)->get();
            foreach ($turnoverImports as $imp) {
                $name = $imp->user?->name ?: 'Superadmin';
                $count = number_format($imp->row_count ?: 0, 0, ',', '.');
                ActivityNotification::create([
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
     * Clear all activity notifications.
     */
    public function clear(Request $request): JsonResponse
    {
        ActivityNotification::truncate();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat aktivitas berhasil dibersihkan.',
        ]);
    }
}
