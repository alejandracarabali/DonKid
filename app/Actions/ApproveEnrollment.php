<?php

namespace App\Actions;

use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\EnrollmentApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * RF-35: aprobar una solicitud de inscripción.
 * Crea el niño en el grupo elegido y la cuenta del acudiente (o reutiliza la que ya
 * existe en el colegio, por ejemplo si ya tiene un hijo matriculado).
 *
 * @return array{child: Child, guardian: User, temporal: ?string} temporal = clave nueva, o null si la cuenta ya existía
 */
class ApproveEnrollment
{
    public function handle(EnrollmentApplication $application, Classroom $classroom, User $reviewer): array
    {
        return DB::transaction(function () use ($application, $classroom, $reviewer) {
            $temporal = null;
            $guardian = $this->existingGuardian($application);

            if (! $guardian) {
                $temporal = Str::password(10, symbols: false);
                $guardian = new User([
                    'name' => $application->guardian_name,
                    'email' => $application->guardian_email,
                    'password' => $temporal,
                ]);
                // role y school_id no son asignables: se ponen en código que controlamos.
                $guardian->role = Role::Guardian;
                $guardian->school_id = $application->school_id;
                $guardian->must_change_password = true;
                $guardian->email_verified_at = now();
                $guardian->save();
            }

            $child = Child::create([
                'classroom_id' => $classroom->id,
                'first_name' => $application->child_first_name,
                'last_name' => $application->child_last_name,
                'birth_date' => $application->child_birth_date,
            ]);
            $child->guardians()->attach($guardian->id, ['relationship' => $application->guardian_relationship]);

            $application->forceFill([
                'status' => EnrollmentStatus::Approved,
                'child_id' => $child->id,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
            ])->save();

            return ['child' => $child, 'guardian' => $guardian, 'temporal' => $temporal];
        });
    }

    // Hermanos: si el correo ya es de una familia de este colegio, se reutiliza la cuenta.
    // Si el correo es de otro rol u otro colegio, no se puede: los correos son únicos en DonKid.
    private function existingGuardian(EnrollmentApplication $application): ?User
    {
        $user = User::withoutGlobalScopes()->where('email', $application->guardian_email)->first();

        if (! $user) {
            return null;
        }

        if ($user->role !== Role::Guardian || $user->school_id !== $application->school_id) {
            throw ValidationException::withMessages([
                'classroom_id' => 'El correo de esta familia ya tiene otra cuenta en DonKid. Pídele otro correo antes de aprobar.',
            ]);
        }

        return $user;
    }
}
