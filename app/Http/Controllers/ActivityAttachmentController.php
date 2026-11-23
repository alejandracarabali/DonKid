<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Descarga de la planeación en PDF de una actividad.
 * La comparten los tres roles; quién puede verla lo decide ActivityPolicy::view().
 */
class ActivityAttachmentController extends Controller
{
    public function __invoke(Activity $activity): StreamedResponse
    {
        Gate::authorize('view', $activity);

        abort_unless($activity->attachment_path && Storage::disk('local')->exists($activity->attachment_path), 404);

        return Storage::disk('local')->download($activity->attachment_path, $activity->attachment_name, [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}