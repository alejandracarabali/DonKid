<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * RF-32: la docente marca una actividad como hecha, con una nota y hasta 10 fotos.
 */
class ActivityRecordController extends Controller
{
    public const MAX_PHOTOS = 10;

    public function edit(Activity $activity): View
    {
        Gate::authorize('update', $activity);

        return view('educator.activities.record', [
            'activity' => $activity->load('photos', 'classroom'),
            // Para que la docente sepa a quién NO fotografiar.
            'withoutConsent' => $activity->classroom->children()->whereNull('image_consent_at')->orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        Gate::authorize('update', $activity);

        $room = self::MAX_PHOTOS - $activity->photos()->count();

        $data = $request->validate([
            'done_note' => ['nullable', 'string', 'max:1000'],
            'photos' => ['nullable', 'array', "max:{$room}"],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5 MB cada una
        ], [
            'photos.max' => $room > 0 ? "Puedes subir {$room} fotos más (máximo ".self::MAX_PHOTOS.' por actividad).' : 'Esta actividad ya tiene '.self::MAX_PHOTOS.' fotos. Quita alguna para subir otras.',
            'photos.*.image' => 'Cada archivo debe ser una foto (JPG, PNG o WebP).',
            'photos.*.mimes' => 'Cada archivo debe ser una foto (JPG, PNG o WebP).',
            'photos.*.max' => 'Una de las fotos pesa más de 5 MB.',
            'photos.*.uploaded' => 'Una foto no se pudo subir. Puede que pese demasiado.',
            'done_note.max' => 'La nota es muy larga. Máximo 1000 letras.',
        ]);

        $stored = [];
        try {
            DB::transaction(function () use ($request, $activity, $data, &$stored) {
                $activity->forceFill([
                    'done_at' => $activity->done_at ?? now(), // la primera vez que se registra
                    'done_by' => $activity->done_by ?? $request->user()->id,
                    'done_note' => $data['done_note'] ?? null,
                ])->save();

                foreach ($request->file('photos', []) as $file) {
                    // Nombre aleatorio en el disco privado, una carpeta por actividad.
                    $path = $file->storeAs("actividades/fotos/{$activity->id}", Str::uuid().'.'.$file->extension(), 'local');
                    $stored[] = $path;
                    $activity->photos()->create(['path' => $path, 'uploaded_by' => $request->user()->id]);
                }
            });
        } catch (\Throwable $e) {
            // La base de datos se deshace sola; los archivos ya escritos hay que borrarlos a mano.
            Storage::disk('local')->delete($stored);
            throw $e;
        }

        return redirect()->route('educator.activities.record', $activity)
            ->with('status', 'Listo, el registro de la actividad quedó guardado.');
    }

    public function destroyPhoto(Activity $activity, ActivityPhoto $photo): RedirectResponse
    {
        Gate::authorize('update', $activity);
        abort_unless($photo->activity_id === $activity->id, 404);

        $photo->delete();
        Storage::disk('local')->delete($photo->path);

        return back()->with('status', 'Se quitó la foto.');
    }
}