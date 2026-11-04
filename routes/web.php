<?php

use App\Http\Controllers\Admin\EducatorController;
use App\Http\Controllers\ForcePasswordController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\ChildController as AdminChildController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Guardian\ChildController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Estas rutas quedan FUERA de 'password.changed'; si no, habría un bucle infinito.
Route::middleware('auth')->group(function () {
    Route::get('/cambiar-clave', [ForcePasswordController::class, 'edit'])->name('password.force');
    Route::put('/cambiar-clave', [ForcePasswordController::class, 'update'])->name('password.force.update');
});

Route::middleware(['auth', 'password.changed'])->group(function () {

    // Punto de entrada único: manda a cada rol a su zona.
    Route::get('/dashboard', function () {
        return redirect()->route(request()->user()->role->homeRoute());
    })->name('dashboard');

    // Zona de coordinación
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::view('/', 'admin.dashboard')->name('dashboard');
            Route::resource('grupos', ClassroomController::class)
            ->parameters(['grupos' => 'classroom'])
            ->except(['show'])
            ->names('classrooms');
            Route::resource('ninos', AdminChildController::class)
            ->parameters(['ninos' => 'child'])
            ->except(['show'])
            ->names('children');
            Route::resource('familias', GuardianController::class)
            ->parameters(['familias' => 'guardian'])
            ->except(['show'])
            ->names('guardians');
            Route::resource('educadoras', EducatorController::class)
            ->parameters(['educadoras' => 'educator'])
            ->except(['show'])
            ->names('educators');
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
            Route::get('/ninos/{child}', [ChildController::class, 'show'])->name('children.show');
        });

    // Perfil: lo comparten todos los roles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';