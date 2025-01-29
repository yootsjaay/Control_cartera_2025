<?php
namespace App\Factories;

use App\Services\HdiSegurosService;
use App\Services\BanorteSeguroService;
use App\Services\QualitasSeguroService;
use App\Services\GmxSeguroService;
use App\Services\SeguroServiceInterface;
use Exception;

class SeguroFactory
{
    // Definir el mapeo de slug a su clase correspondiente
    private const SERVICIOS = [
        'hdi_seguros'      => HdiSegurosService::class,
        'banorte'          => BanorteSeguroService::class,
        'qualitas_seguros' => QualitasSeguroService::class,
        'gmx_seguro'       => GmxSeguroService::class,
    ];

    public static function crearSeguroService(string $slug): SeguroServiceInterface
    {
        if (!array_key_exists($slug, self::SERVICIOS)) {
            throw new Exception("Compañía con slug '{$slug}' no está soportada.");
        }

        $serviceClass = self::SERVICIOS[$slug];
        return new $serviceClass();
    }
}
