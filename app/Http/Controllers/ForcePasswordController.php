<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ForcePasswordController extends Controller
{
    public function edit()
    {
        return view('auth.force-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            // current_password: comprueba que escribió la clave temporal real.
            'current_password' => ['required', 'current_password'],
            // different: no puede "cambiar" la clave por la misma.
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ], [
            'current_password.required' => 'Escribe la contraseña que te dieron.',
            'current_password.current_password' => 'Esa no es la contraseña que te dieron.',
            'password.required' => 'Elige una contraseña nueva.',
            'password.confirmed' => 'Las dos contraseñas no coinciden.',
            'password.different' => 'Elige una contraseña distinta a la temporal.',
        ]);

        // forceFill: must_change_password no está en $fillable, y es intencional.
        $request->user()->forceFill([
            'password' => $request->password,
            'must_change_password' => false,
        ])->save();

        return redirect()->route('dashboard');
    }
}