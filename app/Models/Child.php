<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id', 'first_name', 'last_name', 'birth_date', 'allergies',
    ];

    // Laravel convierte el texto de la BD en un objeto fecha (Carbon).
    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    // Un niño pertenece a un grupo.
    // Uso: $child->classroom->name
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
        // Un niño tiene varios acudientes.
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'child_guardian')
            ->withPivot('relationship')
            ->withTimestamps();
    }
        public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }
}