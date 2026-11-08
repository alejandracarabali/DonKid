<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

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
}