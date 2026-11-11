<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    // Bitácora de UN hijo: solo reportes publicados, los más recientes primero.
    public function index(Request $request, Child $child): View
    {
        // Evita IDOR: si no es su hijo, 403.
        Gate::authorize('view', $child);

        $reports = $child->dailyReports()
            ->whereNotNull('published_at')
            ->with('educator')
            ->latest('report_date')
            ->paginate(10);

        return view('guardian.reports.index', compact('child', 'reports'));
    }
}