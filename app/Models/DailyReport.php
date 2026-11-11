<?php

namespace App\Models;

use App\Enums\MealStatus;
use App\Enums\Mood;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    // Solo lo que viene del formulario. educator_id y published_at
    // los asigna el servidor, nunca el usuario.
    protected $fillable = [
        'child_id',
        'report_date',
        'mood',
        'meal_status',
        'nap_minutes',
        'note',
    ];

    /**
     * Un reporte no tiene school_id: pertenece al colegio de su niño.
     * whereHas('child') aplica el filtro de colegio que ya tiene Child.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('school', function (Builder $query) {
            if (auth()->guard()->hasUser()) {
                $query->whereHas('child');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'mood' => Mood::class,
            'meal_status' => MealStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function educator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'educator_id');
    }
}