<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAttendance
 */
class Attendance extends Model
{
    use HasFactory;

    // recorded_by lo asigna el servidor, nunca el formulario.
    protected $fillable = ['child_id', 'date', 'status'];

    /**
     * Igual que DailyReport: no tiene school_id, pertenece al colegio de su estudiante.
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
            'date' => 'date',
            'status' => AttendanceStatus::class,
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}