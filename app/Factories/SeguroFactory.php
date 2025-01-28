<?php
namespace App\Factories;

use App\Services\HdiSegurosService;
use App\Services\BanorteSeguroService;
use App\Services\QualitasSeguroService;
use App\Services\GmxSeguroService;
use App\Services\SeguroServiceInterface;

class SeguroFactory
{
    public static function crearSeguroService($companiaId): SeguroServiceInterface
    {
        // Selecciona el servicio adecuado según el ID de la compañía
        switch ($companiaId) {
            case 1:  // HDI Seguros
                return new HdiSegurosService();
            case 2:  // Automovil Familiar (Banorte)
                return new BanorteSeguroService();
            case 3:  // Qualitas Seguro
                return new QualitasSeguroService();
            case 4:  // GMX Seguro
                return new GmxSeguroService();
            default:
                // Lanzar una excepción si el ID de la compañía no está soportado
                throw new \Exception('Compañía no soportada');
        }
    }
}
