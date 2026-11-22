<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FamilyRequestStatus;
use App\Enums\FamilyRequestType;
use App\Http\Controllers\Controller;
use App\Models\FamilyRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Coordinación ve todas las solicitudes del colegio, con filtros.
 */
class FamilyRequestController extends Controller
{
    public function index(Request $request): View
    {
        $type = FamilyRequestType::tryFrom((string) $request->query('tipo'));
        $status = FamilyRequestStatus::tryFrom((string) $request->query('estado'));

        $requests = FamilyRequest::with(['child.classroom', 'guardian'])
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.requests.index', compact('requests', 'type', 'status'));
    }
}
