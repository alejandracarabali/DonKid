<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RF-07: actividad evaluada (quiz, taller, exposición...) de una materia en un grupo y periodo.
 */
class Assessment extends Model
{
    protected $fillable = ['classroom_id', 'subject_id', 'academic_period_id', 'title', 'created_by'];

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }
}
