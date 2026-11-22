<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * RF-05: rangos de nota de cada desempeño, según el SIEE del colegio.
 */
class PerformanceLevelController extends Controller
{
    public function edit(): View
    {
        return view('admin.performance.edit', ['levels' => PerformanceLevel::orderBy('position')->get()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $levels = PerformanceLevel::orderBy('position')->get();

        $rules = [];
        foreach ($levels as $level) {
            $rules["levels.{$level->id}.min"] = ['required', 'numeric', 'between:1,5', 'decimal:0,1'];
            $rules["levels.{$level->id}.max"] = ['required', 'numeric', 'between:1,5', 'decimal:0,1'];
        }
        $data = $request->validate($rules, [
            'levels.*.*.required' => 'Completa el rango.',
            'levels.*.*.between' => 'Las notas van de 1.0 a 5.0.',
            'levels.*.*.decimal' => 'Usa máximo un decimal (ej. 3.5).',
        ])['levels'];

        $this->ensureContinuous($levels, $data);

        DB::transaction(function () use ($levels, $data) {
            foreach ($levels as $level) {
                $level->update($data[$level->id]);
            }
        });

        return back()->with('status', 'Listo, la escala quedó guardada.');
    }

    // Los rangos deben cubrir de 1.0 a 5.0 sin huecos ni cruces: Bajo 1.0–2.9, Básico 3.0–3.9...
    private function ensureContinuous($levels, array $data): void
    {
        $expectedMin = 10; // en décimas: 1.0
        foreach ($levels as $level) {
            $min = (int) round($data[$level->id]['min'] * 10);
            $max = (int) round($data[$level->id]['max'] * 10);

            if ($max < $min) {
                throw ValidationException::withMessages(["levels.{$level->id}.max" => "{$level->name}: el máximo no puede ser menor que el mínimo."]);
            }
            if ($min !== $expectedMin) {
                throw ValidationException::withMessages(["levels.{$level->id}.min" => sprintf(
                    '%s debe empezar en %.1f, justo después del desempeño anterior.', $level->name, $expectedMin / 10
                )]);
            }
            $expectedMin = $max + 1;
        }

        if ($expectedMin !== 51) {
            throw ValidationException::withMessages(["levels.{$levels->last()->id}.max" => 'El último desempeño debe terminar en 5.0.']);
        }
    }
}
