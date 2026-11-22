<?php

namespace App\View\Components;

use App\Enums\FamilyRequestStatus;
use App\Enums\FamilyRequestType;
use App\Enums\Role;
use App\Models\FamilyRequest;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Bloque "Solicitudes de familias" del director de grupo.
 * Uso: <x-family-requests /> (en la Bitácora y en Grupos).
 * Muestra las pendientes de hoy en adelante; las recomendaciones, sin importar la fecha.
 */
class FamilyRequests extends Component
{
    public Collection $requests;

    public function __construct()
    {
        $user = auth()->user();

        $this->requests = FamilyRequest::query()
            ->where('status', FamilyRequestStatus::Sent)
            ->where(fn ($q) => $q->where('type', FamilyRequestType::Recommendation)
                ->orWhereDate('date', '>=', today()))
            ->when($user->role === Role::Educator, fn ($q) => $q->whereHas(
                'child', fn ($c) => $c->whereIn('classroom_id', $user->ledClassrooms()->pluck('classrooms.id'))
            ))
            ->with('child')
            ->orderBy('date')
            ->get();
    }

    public function shouldRender(): bool
    {
        return $this->requests->isNotEmpty();
    }

    public function render(): View
    {
        return view('components.family-requests');
    }
}
