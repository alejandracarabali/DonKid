<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Privacidad por diseño: solo los hijos vinculados a esta familia.
        $children = $request->user()->children()
            ->with('classroom')
            ->orderBy('first_name')
            ->get();

        return view('guardian.dashboard', compact('children'));
    }
}