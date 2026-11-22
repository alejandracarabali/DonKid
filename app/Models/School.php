<?php

namespace App\Models;

use App\Enums\SchoolType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperSchool
 */
class School extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type'];

    protected function casts(): array
    {
        return ['type' => SchoolType::class];
    }

    // RF-54: un colegio solo de primaria no tiene bitácora en ningún grupo.
    public function offersDailyLog(): bool
    {
        return $this->type !== SchoolType::Primary;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }
}