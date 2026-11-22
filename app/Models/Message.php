<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    // sender_id, visible_at y read_at los pone el servidor.
    protected $fillable = ['body'];

    protected function casts(): array
    {
        return [
            'body' => 'encrypted',
            'visible_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Lo que el colegio ya puede ver: sus mensajes y los de la familia que ya están en horario.
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('visible_at', '<=', now());
    }

    public function isPending(): bool
    {
        return $this->visible_at->isFuture();
    }
}
