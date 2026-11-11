<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperClassroom
 */
class Classroom extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = ['name', 'age_range'];

    // Un grupo tiene muchos niños.
    // Uso: $classroom->children
    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    // Un grupo tiene varias educadoras.
    public function educators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_user')->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    // Pertenece a un colegio. Uso: $modelo->school->name
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}