<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'nik',
    'name',
    'role',
    'level',
    'target',
    'cabang',
    'phone',
    'email',
    'status',
    'joined_date',
    'avatar_color',
    'notes',
])]
class Employee extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_date' => 'date',
            'target' => 'float',
        ];
    }

    /**
     * Get all assessments for this employee.
     *
     * @return HasMany<SalesAssessment, $this>
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(SalesAssessment::class);
    }

    /**
     * Get assessment for a specific period or date.
     *
     * @param string|null $period YYYY-MM-DD or YYYY-MM
     * @return HasOne<SalesAssessment, $this>
     */
    public function assessmentForPeriod(?string $period = null): HasOne
    {
        $relation = $this->hasOne(SalesAssessment::class);

        if ($period !== null) {
            $relation->where('period', $period);
        }

        return $relation;
    }

    /**
     * Get recent daily assessments for history / trend tracking.
     *
     * @return HasMany<SalesAssessment, $this>
     */
    public function recentAssessments(): HasMany
    {
        return $this->hasMany(SalesAssessment::class)
            ->where('period', 'like', '____-__-__')
            ->orderBy('period', 'desc')
            ->limit(10);
    }

    /**
     * Get initials of employee name (e.g. Siti Rahmawati -> SR).
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->name));
        if (empty($words)) {
            return 'EM';
        }

        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 2));
        }

        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }

    /**
     * Check if employee is currently active.
     */
    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }
}
