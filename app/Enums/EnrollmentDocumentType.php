<?php

namespace App\Enums;

// Documentos obligatorios de la inscripción (RF-34).
enum EnrollmentDocumentType: string
{
    case BirthCertificate = 'birth_certificate';
    case VaccinationCard = 'vaccination_card';
    case EpsCertificate = 'eps_certificate';
    case Photo = 'photo';

    public function label(): string
    {
        return match ($this) {
            self::BirthCertificate => 'Registro civil',
            self::VaccinationCard => 'Carné de vacunas',
            self::EpsCertificate => 'Certificado de la EPS',
            self::Photo => 'Foto del niño o niña',
        };
    }

    // Formatos aceptados: la foto solo como imagen; los demás también en PDF.
    /** @return list<string> */
    public function mimes(): array
    {
        return match ($this) {
            self::Photo => ['jpg', 'jpeg', 'png', 'webp'],
            default => ['pdf', 'jpg', 'jpeg', 'png'],
        };
    }

    // Para el atributo accept del <input type="file">.
    public function accept(): string
    {
        return collect($this->mimes())->map(fn ($m) => '.'.$m)->implode(',');
    }
}
