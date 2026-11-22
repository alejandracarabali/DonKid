<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El superadministrador no tiene permiso sobre ningún dato de un colegio
        // (niños, bitácoras, avisos, actividades): toda policy le responde "no".
        Gate::before(fn (User $user) => $user->role === Role::SuperAdmin ? false : null);
    }
}
