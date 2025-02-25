<?php

return [
    'servicios' => [
        // Servicios de aseguradoras
        'qualitas-seguros' => App\Services\QualitasSeguroService::class,  // Servicio para Qualitas Seguros
        'hdi-seguros'      => App\Services\HdiSegurosService::class,       // Servicio para HDI Seguros
        'banorte-seguros'       => App\Services\BanorteSeguroService::class,   // Servicio para Banorte Seguros
        'gmx-seguros'       => App\Services\GmxSeguroService::class,       // Servicio para GMX Seguros
    ],
];
