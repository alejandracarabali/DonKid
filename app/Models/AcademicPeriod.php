<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RF-02: periodo académico (1, 2 o 3) de un año escolar.
 */
class AcademicPeriod extends Model
{
    protected $fillable = ['number', 'starts_on', 'ends_on', 'grades_due_on'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'grades_due_on' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    // "Primer periodo", "Segundo periodo"...
    public function name(): string
    {
        $ordinals = [1 => 'Primer', 2 => 'Segundo', 3 => 'Tercer', 4 => 'Cuarto'];

        return ($ordinals[$this->number] ?? $this->number.'.°').' periodo';
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }
}
