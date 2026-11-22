{{--
    BORRADOR. Resumen de la política de tratamiento de datos para mostrar a las familias.
    Reemplazar por el texto aprobado por el abogado y actualizar PRIVACY_POLICY_VERSION.
--}}
<x-guest-layout>
    <h1 class="text-2xl font-semibold">Política de tratamiento de datos</h1>
    <p class="mt-1 text-sm text-soft">{{ $school->name }} · Versión {{ config('privacy.policy_version') }}</p>

    <div class="mt-4 space-y-4 text-sm">
        <section>
            <h2 class="text-lg font-semibold">Quién trata tus datos</h2>
            <p>{{ $school->name }} es el responsable de los datos que recoge en DonKid. Para consultas y reclamos, escribe a coordinación.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold">Para qué</h2>
            <ul class="list-disc space-y-1 ps-5">
                <li>Tramitar la inscripción y la matrícula.</li>
                <li>Llevar el registro académico: asistencia, notas, informes y certificados.</li>
                <li>Cuidar la salud y la seguridad de tu hijo o hija durante la jornada.</li>
                <li>Comunicarnos contigo: avisos, mensajes y notificaciones.</li>
                <li>Verificar quién puede recoger a tu hijo o hija.</li>
            </ul>
            <p class="mt-1">No vendemos tus datos ni los usamos con fines comerciales.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold">Datos de niños y datos de salud</h2>
            <p>Tratamos los datos de tu hijo o hija respetando sus derechos prevalentes. Los datos de salud son sensibles: no estás obligado a entregarlos, y solo los ven coordinación y las docentes de su grupo.</p>
        </section>

        <section>
            <h2 class="text-lg font-semibold">Tus derechos</h2>
            <p>Puedes conocer, actualizar, rectificar y suprimir tus datos, pedir prueba de tu autorización y revocarla.
               Respondemos consultas en máximo 10 días hábiles y reclamos en máximo 15 días hábiles (Ley 1581 de 2012).</p>
        </section>
    </div>
</x-guest-layout>
