<?php

return [
    'servicios' => [
        // Servicios de aseguradoras
        'qualitas_seguros' => App\Services\QualitasSeguroService::class,  // Servicio para Qualitas Seguros
        'hdi_seguros'      => App\Services\HdiSegurosService::class,       // Servicio para HDI Seguros
        'banorte'          => App\Services\BanorteSeguroService::class,   // Servicio para Banorte Seguros
        'gmx_seguro'       => App\Services\GmxSeguroService::class,       // Servicio para GMX Seguros
    ],
];
