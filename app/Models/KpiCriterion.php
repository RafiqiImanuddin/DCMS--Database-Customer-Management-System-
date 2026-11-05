<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'name',
    'description',
    'weight',
    'target_benchmark',
    'is_active',
    'sort_order',
])]
class KpiCriterion extends Model
{
    use HasFactory;

    protected $table = 'kpi_criteria';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope a query to only include active criteria.
     *
     * @param Builder<KpiCriterion> $query
     * @return Builder<KpiCriterion>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the sum of all active criteria weights.
     */
    public static function totalActiveWeight(): float
    {
        return (float) static::where('is_active', true)->sum('weight');
    }
}

