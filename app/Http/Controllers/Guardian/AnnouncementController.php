<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    // Bandeja: avisos publicados de los grupos de sus hijos (y generales).
    public function index(Request $request): View
    {
        $user = $request->user();
        $classroomIds = $user->children()->pluck('children.classroom_id')->filter();

        $announcements = Announcement::query()
            ->whereNotNull('published_at')
            ->where(fn ($q) => $q->whereNull('classroom_id')->orWhereIn('classroom_id', $classroomIds))
            ->with(['author', 'classroom'])
            ->withExists(['readers as read_by_me' => fn ($q) => $q
                ->where('users.id', $user->id)
                ->whereNotNull('announcement_user.read_at')])
            ->latest('published_at')
            ->paginate(10);

        return view('guardian.announcements.index', compact('announcements'));
    }

    // Confirmación de lectura (idempotente: no pisa la primera fecha).
    public function read(Request $request, Announcement $announcement): RedirectResponse
    {
        Gate::authorize('view', $announcement);

        $user = $request->user();

        if (! $announcement->readers()->where('users.id', $user->id)->exists()) {
            $announcement->readers()->attach($user->id, ['read_at' => now()]);
        }

        return back()->with('status', 'Listo, quedó marcado como leído.');
    }
}