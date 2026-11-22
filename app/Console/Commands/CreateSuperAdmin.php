<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Crea una cuenta de superadministrador. No hay pantalla para esto a propósito:
 * solo quien tiene acceso al servidor puede ejecutar el comando.
 *
 * Uso: php artisan donkid:superadmin tu@correo.com --name="Tu nombre"
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'donkid:superadmin {email} {--name=Equipo DonKid}';

    protected $description = 'Crea una cuenta de superadministrador de la plataforma DonKid';

    public function handle(): int
    {
        $email = $this->argument('email');

        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email', 'unique:users,email']], [
            'email.unique' => 'Ese correo ya tiene una cuenta en DonKid.',
            'email.email' => 'Ese correo no es válido.',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));

            return self::FAILURE;
        }

        $temporal = Str::password(14, symbols: false);

        $user = new User(['name' => $this->option('name'), 'email' => $email, 'password' => $temporal]);
        $user->role = Role::SuperAdmin;
        $user->school_id = null; // no pertenece a ningún colegio
        $user->must_change_password = true;
        $user->email_verified_at = now();
        $user->save();

        $this->info("Superadministrador creado: {$email}");
        $this->line("Contraseña temporal (se muestra una sola vez): {$temporal}");
        $this->line('Deberás cambiarla al iniciar sesión.');

        return self::SUCCESS;
    }
}
