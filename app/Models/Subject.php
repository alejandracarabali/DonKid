<?php

namespace App\Models;

use App\Enums\SubjectKind;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * RF-04: materia (primaria) o dimensión del desarrollo (preescolar).
 */
class Subject extends Model
{
    use BelongsToSchool;

    protected $fillable = ['kind', 'name', 'position', 'active'];

    protected function casts(): array
    {
        return ['kind' => SubjectKind::class, 'active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
