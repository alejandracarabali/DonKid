<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RF-27: conversación entre el acudiente de un niño y su director de grupo.
 */
class Conversation extends Model
{
    use BelongsToSchool;

    protected $fillable = ['child_id', 'guardian_id'];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class)->withoutGlobalScope('active');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    protected function inheritedSchoolId(): ?int
    {
        return Child::withoutGlobalScopes()->whereKey($this->child_id)->value('school_id');
    }
}
