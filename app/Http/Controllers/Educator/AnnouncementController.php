<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function create(Request $request): View
    {
        $user = $request->user();

        // Admin elige cualquier grupo; la educadora solo los suyos.
        $classrooms = $user->role->value === 'admin'
            ? Classroom::orderBy('name')->get()
            : $user->classrooms()->orderBy('name')->get();

        return view('educator.announcements.create', compact('classrooms'));
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $classroom = $request->filled('classroom_id')
            ? Classroom::findOrFail($request->integer('classroom_id'))
            : null;

        Gate::authorize('create', [Announcement::class, $classroom]);

        $announcement = new Announcement($request->safe()->only(['title', 'body']));
        $announcement->classroom_id = $classroom?->id;
        $announcement->author_id = $request->user()->id;
        $announcement->published_at = now();
        $announcement->save();

        return redirect()->route('educator.dashboard')
            ->with('status', 'Aviso enviado a las familias.');
    }
}