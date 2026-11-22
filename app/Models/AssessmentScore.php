<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    protected $fillable = ['assessment_id', 'child_id', 'score'];

    protected function casts(): array
    {
        return ['score' => 'float'];
    }
}
