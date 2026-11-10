<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityNotification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'title',
        'message',
        'type',
        'icon',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to log an activity notification for the entire team.
     *
     * @param string $title
     * @param string $message
     * @param User|null $user
     * @param string $type success|info|warning|danger
     * @param string $action
     * @param string $icon
     * @return static
     */
    public static function log(
        string $title,
        string $message,
        ?User $user = null,
        string $type = 'success',
        string $action = 'general',
        string $icon = 'bell'
    ): static {
        $actor = $user ?? auth()->user();
        $userName = $actor ? $actor->name : 'Sistem';

        $notif = static::create([
            'user_id' => $actor?->id,
            'user_name' => $userName,
            'action' => $action,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'icon' => $icon,
        ]);

        // Jaga agar tabel tetap ramping (maksimal 200 data aktivitas terakhir)
        try {
            $excessCount = static::count() - 200;
            if ($excessCount > 0) {
                static::orderBy('id', 'asc')->limit($excessCount)->delete();
            }
        } catch (\Throwable $e) {}

        return $notif;
    }
}
