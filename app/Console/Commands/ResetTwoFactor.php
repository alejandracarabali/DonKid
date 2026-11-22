<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Si se pierde el celular y los códigos de recuperación, se desactiva la verificación
 * en dos pasos desde el servidor. Al volver a ingresar, se configura de nuevo.
 *
 * Uso: php artisan donkid:reset-2fa tu@correo.com
 */
class ResetTwoFactor extends Command
{
    protected $signature = 'donkid:reset-2fa {email}';

    protected $description = 'Desactiva la verificación en dos pasos de una cuenta (para volver a configurarla)';

    public function handle(): int
    {
        $user = User::withoutGlobalScopes()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No hay ninguna cuenta con ese correo.');

            return self::FAILURE;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_step' => null,
        ])->save();

        $this->info("Listo: {$user->email} configurará la verificación en dos pasos al volver a ingresar.");

        return self::SUCCESS;
    }
}
