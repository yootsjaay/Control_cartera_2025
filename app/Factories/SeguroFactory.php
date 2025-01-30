<?php
namespace App\Factories;

use App\Services\SeguroServiceInterface;
use Illuminate\Support\Facades\App;
use Exception;

class SeguroFactory
{
    private const SERVICIOS = [
        'hdi_seguros'      => \App\Services\HdiSegurosService::class,
        'banorte'          => \App\Services\BanorteSeguroService::class,
        'qualitas_seguros' => \App\Services\QualitasSeguroService::class,
        'gmx_seguro'       => \App\Services\GmxSeguroService::class,
    ];

    public static function crearSeguroService(string $slug): SeguroServiceInterface
    {
        if (!array_key_exists($slug, self::SERVICIOS)) {
            throw new Exception("Compañía con slug '{$slug}' no está soportada.");
        }

        return App::make(self::SERVICIOS[$slug]); // Laravel inyecta dependencias automáticamente
    }
}
