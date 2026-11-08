<?php

use App\Http\Controllers\Educator\AnnouncementController;
use App\Http\Controllers\Guardian\AnnouncementController as GuardianAnnouncementController;
use App\Http\Controllers\Guardian\DailyReportController as GuardianReportController;
use App\Http\Controllers\Educator\DailyReportController;
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
            Route::get('/bitacora', [DailyReportController::class, 'index'])->name('reports.index');
            Route::get('/bitacora/{child}', [DailyReportController::class, 'create'])->name('reports.create');
            Route::post('/bitacora/{child}', [DailyReportController::class, 'store'])->name('reports.store');
            Route::get('/avisos/crear', [AnnouncementController::class, 'create'])->name('announcements.create');
            Route::post('/avisos', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::get('/actividades', [\App\Http\Controllers\Educator\ActivityController::class, 'index'])->name('activities.index');
            Route::get('/actividades/crear', [\App\Http\Controllers\Educator\ActivityController::class, 'create'])->name('activities.create');
            Route::post('/actividades', [\App\Http\Controllers\Educator\ActivityController::class, 'store'])->name('activities.store');
            Route::get('/actividades/{activity}/editar', [\App\Http\Controllers\Educator\ActivityController::class, 'edit'])->name('activities.edit');
            Route::put('/actividades/{activity}', [\App\Http\Controllers\Educator\ActivityController::class, 'update'])->name('activities.update');
            Route::delete('/actividades/{activity}', [\App\Http\Controllers\Educator\ActivityController::class, 'destroy'])->name('activities.destroy');            
        });

    // Zona de familias
    Route::middleware('role:guardian')
        ->prefix('familia')
        ->name('guardian.')
        ->group(function () {
            Route::view('/', 'guardian.dashboard')->name('dashboard');
            Route::get('/ninos/{child}', [ChildController::class, 'show'])->name('children.show');
            Route::get('/ninos/{child}/bitacora', [GuardianReportController::class, 'index'])->name('reports.index');
            Route::get('/avisos', [GuardianAnnouncementController::class, 'index'])->name('announcements.index');
            Route::post('/avisos/{announcement}/leido', [GuardianAnnouncementController::class, 'read'])->name('announcements.read');
            Route::get('/actividades', [\App\Http\Controllers\Guardian\ActivityController::class, 'index'])->name('activities.index');
        });

    // Perfil: lo comparten todos los roles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';