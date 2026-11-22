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

    // Carné de vacunas y certificado de EPS son datos de salud (sensibles, Ley 1581 art. 5).
    public function isHealth(): bool
    {
        return in_array($this, [self::VaccinationCard, self::EpsCertificate], true);
    }

    // ¿Es obligatorio en el formulario? Los de salud dependen de config/privacy.php.
    public function isRequired(): bool
    {
        return ! $this->isHealth() || config('privacy.health_documents_required');
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
