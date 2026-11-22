<?php

use App\Http\Controllers\Educator\GroupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Educator\AnnouncementController;
use App\Http\Controllers\Guardian\AnnouncementController as GuardianAnnouncementController;
use App\Http\Controllers\Guardian\DailyReportController as GuardianReportController;
use App\Http\Controllers\Educator\DailyReportController;
use App\Http\Controllers\Admin\EducatorController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\RenewalController as AdminRenewalController;
use App\Http\Controllers\Guardian\RenewalController as GuardianRenewalController;
use App\Http\Controllers\ForcePasswordController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\ChildController as AdminChildController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Guardian\ChildController;
use App\Http\Controllers\EnrollmentApplicationController;
use App\Http\Controllers\Platform\SchoolController as PlatformSchoolController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// La raíz no tiene página propia: /dashboard ya sabe adónde mandar a cada quien.
Route::redirect('/', '/dashboard');

// Estas rutas quedan FUERA de 'password.changed'; si no, habría un bucle infinito.
// RF-34: inscripción pública, sin iniciar sesión. El colegio se identifica por su slug.
// throttle: máximo 5 envíos por hora desde la misma conexión (protección contra abuso).
Route::get('/inscripcion/{school:slug}', [EnrollmentApplicationController::class, 'create'])->name('enrollment.create');
Route::post('/inscripcion/{school:slug}', [EnrollmentApplicationController::class, 'store'])
    ->middleware('throttle:5,60')->name('enrollment.store');
Route::get('/inscripcion/{school:slug}/enviada', [EnrollmentApplicationController::class, 'sent'])->name('enrollment.sent');
Route::get('/politica-de-datos/{school:slug}', [EnrollmentApplicationController::class, 'policy'])->name('enrollment.policy');

Route::middleware('auth')->group(function () {
    Route::get('/cambiar-clave', [ForcePasswordController::class, 'edit'])->name('password.force');
    Route::put('/cambiar-clave', [ForcePasswordController::class, 'update'])->name('password.force.update');
});

Route::middleware(['auth', 'password.changed', 'school.active', 'guardian.active'])->group(function () {

    // RF-55 a RF-57: zona del superadministrador (equipo DonKid).
    Route::middleware('role:superadmin')
        ->prefix('plataforma')
        ->name('platform.')
        ->group(function () {
            Route::get('/colegios', [PlatformSchoolController::class, 'index'])->name('schools.index');
            Route::get('/colegios/nuevo', [PlatformSchoolController::class, 'create'])->name('schools.create');
            Route::post('/colegios', [PlatformSchoolController::class, 'store'])->name('schools.store');
            Route::post('/colegios/{school}/suspender', [PlatformSchoolController::class, 'suspend'])->name('schools.suspend');
            Route::post('/colegios/{school}/reactivar', [PlatformSchoolController::class, 'reactivate'])->name('schools.reactivate');
        });


    // Punto de entrada único: manda a cada rol a su zona.
    Route::get('/dashboard', function () {
        return redirect()->route(request()->user()->role->homeRoute());
    })->name('dashboard');
    
    // Guía de estilos: catálogo visual de los componentes base del diseño.
    Route::view('/estilos', 'estilos')->middleware('auth')->name('estilos');

    // Zona de coordinación
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', DashboardController::class)->name('dashboard');            
            Route::resource('grupos', ClassroomController::class)
            ->parameters(['grupos' => 'classroom'])
            ->except(['show'])
            ->names('classrooms');
            // RF-53: no hay "destroy"; un niño retirado se inhabilita, nunca se borra.
            Route::get('ninos/retirados', [AdminChildController::class, 'withdrawn'])->name('children.withdrawn');
            Route::patch('ninos/retirados/{child}/reactivar', [AdminChildController::class, 'reactivate'])
                ->whereNumber('child')->name('children.reactivate');
            Route::patch('ninos/{child}/retirar', [AdminChildController::class, 'withdraw'])->name('children.withdraw');
            Route::resource('ninos', AdminChildController::class)
            ->parameters(['ninos' => 'child'])
            ->except(['show', 'destroy'])
            ->names('children');
            Route::resource('familias', GuardianController::class)
            ->parameters(['familias' => 'guardian'])
            ->except(['show'])
            ->names('guardians');
            // RF-36: renovación de matrícula.
            Route::get('renovaciones', [AdminRenewalController::class, 'index'])->name('renewals.index');
            Route::post('renovaciones/abrir', [AdminRenewalController::class, 'open'])->name('renewals.open');
            Route::post('renovaciones/cerrar', [AdminRenewalController::class, 'close'])->name('renewals.close');

            // RF-35: bandeja de inscripciones.
            Route::get('inscripciones', [EnrollmentController::class, 'index'])->name('enrollments.index');
            Route::get('inscripciones/{application}', [EnrollmentController::class, 'show'])->name('enrollments.show');
            Route::get('inscripciones/{application}/documentos/{document}', [EnrollmentController::class, 'document'])->name('enrollments.document');
            Route::post('inscripciones/{application}/aprobar', [EnrollmentController::class, 'approve'])->name('enrollments.approve');
            Route::post('inscripciones/{application}/rechazar', [EnrollmentController::class, 'reject'])->name('enrollments.reject');
            Route::resource('docentes', EducatorController::class)
            ->parameters(['docentes' => 'educator'])
            ->except(['show'])
            ->names('educators');
        });

    // Zona de educadoras (la coordinación también puede entrar)
    Route::middleware('role:educator,admin')
        ->prefix('docente')
        ->name('educator.')
        ->group(function () {
            // La docente no tiene "Inicio": su pantalla principal es Bitácora.
            // El director de grupo empieza en la Bitácora; el docente solo por materia, en Grupos.
            Route::get('/', fn () => redirect()->route(
                \App\Support\Navigation::offersDailyLog(request()->user()) ? 'educator.reports.index' : 'educator.groups.index'
            ))->name('dashboard');

            // Bitácora
            Route::get('/bitacora', [DailyReportController::class, 'index'])->name('reports.index');
            Route::get('/bitacora/{child}', [DailyReportController::class, 'create'])->name('reports.create');
            Route::post('/bitacora/{child}', [DailyReportController::class, 'store'])->name('reports.store');

            // Grupos y asistencia
            Route::get('/grupos', [GroupController::class, 'index'])->name('groups.index');
            Route::get('/grupos/{classroom}/asistencia', [GroupController::class, 'attendance'])->name('groups.attendance');
            Route::post('/grupos/{classroom}/asistencia', [GroupController::class, 'storeAttendance'])->name('groups.attendance.store');

            // Avisos
            Route::get('/avisos/crear', [AnnouncementController::class, 'create'])->name('announcements.create');
            Route::post('/avisos', [AnnouncementController::class, 'store'])->name('announcements.store');

            // Actividades
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
            Route::get('/', \App\Http\Controllers\Guardian\DashboardController::class)->name('dashboard');
            Route::get('/ninos/{child}', [ChildController::class, 'show'])->name('children.show');
            Route::get('/ninos/{child}/bitacora', [GuardianReportController::class, 'index'])->name('reports.index');
            Route::get('/ninos/{child}/renovar', [GuardianRenewalController::class, 'create'])->name('renewals.create');
            Route::post('/ninos/{child}/renovar', [GuardianRenewalController::class, 'store'])->name('renewals.store');
            Route::get('/avisos', [GuardianAnnouncementController::class, 'index'])->name('announcements.index');
            Route::post('/avisos/{announcement}/leido', [GuardianAnnouncementController::class, 'read'])->name('announcements.read');
            Route::get('/actividades', [\App\Http\Controllers\Guardian\ActivityController::class, 'index'])->name('activities.index');
        });

    // Perfil: lo comparten todos los roles
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';