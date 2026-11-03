<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {

    // Punto de entrada único: manda a cada rol a su zona.
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->role->homeRoute());
    })->name('dashboard');

    // Zona de coordinación
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::view('/', 'admin.dashboard')->name('dashboard');
        });

    // Zona de educadoras (la coordinación también puede entrar)
    Route::middleware('role:educator,admin')
        ->prefix('educadora')
        ->name('educator.')
        ->group(function () {
            Route::view('/', 'educator.dashboard')->name('dashboard');
        });

    // Zona de familias
    Route::middleware('role:guardian')
        ->prefix('familia')
        ->name('guardian.')
        ->group(function () {
            Route::view('/', 'guardian.dashboard')->name('dashboard');
        });

    // Perfil: lo comparten todos los roles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';