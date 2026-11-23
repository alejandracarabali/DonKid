<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityPhoto;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Muestra una foto de actividad. Quién puede verla lo decide ActivityPolicy::viewPhotos().
 */
class ActivityPhotoController extends Controller
{
    public function __invoke(Activity $activity, ActivityPhoto $photo): StreamedResponse
    {
        Gate::authorize('viewPhotos', $activity);
        abort_unless($photo->activity_id === $activity->id && Storage::disk('local')->exists($photo->path), 404);

        // response() (no download()): se muestra en la página en vez de descargarse.
        return Storage::disk('local')->response($photo->path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}