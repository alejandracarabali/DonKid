<?php

// Protección de datos personales (Ley 1581 de 2012).
return [

    // Versión del texto de autorización que aceptan las familias. Cámbiala cada vez
    // que el texto cambie: queda guardada con cada autorización como prueba.
    'policy_version' => env('PRIVACY_POLICY_VERSION', '2026-11-borrador'),

    // ¿El carné de vacunas y el certificado de EPS son obligatorios en la inscripción?
    // Son datos de salud (sensibles): si el abogado confirma que no se pueden exigir,
    // cambia esto a false y pasan a ser opcionales.
    'health_documents_required' => (bool) env('ENROLLMENT_HEALTH_DOCS_REQUIRED', true),

];
