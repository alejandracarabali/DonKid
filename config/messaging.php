<?php

// RF-27: mensajería entre familias y director de grupo.
return [

    // Las familias pueden escribir a cualquier hora, pero el docente recibe los mensajes
    // solo dentro de este horario (RNF-11). Hora local del colegio.
    'timezone' => env('MESSAGING_TIMEZONE', 'America/Bogota'),
    'opens_at' => 6,   // 6:00 a. m.
    'closes_at' => 19, // 7:00 p. m.

];
