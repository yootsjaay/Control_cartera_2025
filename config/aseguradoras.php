<?php

return [
    'servicios' => [
        App\Services\QualitasSeguroService::class => 'Qualitas Seguro',
        App\Services\HdiSegurosService::class => 'HDI Seguro',
        App\Services\BanorteSeguroService::class => 'Banorte Seguro',
        App\Services\GmxSeguroService::class => 'GMX Seguro'
    ],
    
    // Puedes agregar más configuraciones relacionadas aquí
    'opciones' => [
        'max_aseguradoras' => 10,
        'formato_fecha' => 'Y-m-d'
    ]
];