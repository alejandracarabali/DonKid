@props(['activity'])

{{-- Enlace a la planeación en PDF. Si la actividad no tiene PDF, no muestra nada. --}}
@if ($activity->attachment_path)
    <a href="{{ route('activities.attachment', $activity) }}"
       class="mt-1 inline-flex min-h-[44px] items-center gap-1 text-sm font-bold text-action underline">
        📄 Descargar planeación (PDF)
        <span class="sr-only">de {{ $activity->title }}</span>
    </a>
@endif