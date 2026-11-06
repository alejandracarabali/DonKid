<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\NewAnnouncement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyGuardiansOfAnnouncement implements ShouldQueue
{
    use Queueable;

    public function __construct(public Announcement $announcement)
    {
    }

    public function handle(): void
    {
        // Acudientes con al menos un hijo en el grupo (o todos si es general).
        User::query()
            ->where('role', 'guardian')
            ->when($this->announcement->classroom_id, fn ($q, $id) => $q->whereHas(
                'children', fn ($c) => $c->where('children.classroom_id', $id)
            ))
            ->each(fn (User $guardian) => $guardian->notify(new NewAnnouncement($this->announcement)));
    }
}