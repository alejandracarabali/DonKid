<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * RF-08: valoración escrita de una dimensión del desarrollo (preescolar) por niño y periodo.
 */
class DimensionReport extends Model
{
    protected $fillable = ['child_id', 'subject_id', 'academic_period_id', 'body', 'author_id'];

    protected function casts(): array
    {
        return ['body' => 'encrypted'];
    }
}
