<?php
namespace App\Factories;

use App\Services\HdiSegurosService;
use App\Services\BanorteSeguroService;
use App\Services\SeguroServiceInterface;

class SeguroFactory
{
    public static function crearSeguroService($companiaId): SeguroServiceInterface
    {
        
        switch ($companiaId) {
            case 1:  // ID de HDI Seguros
                return new HdiSegurosService();
            case 2:  // ID de Automovil Familiar
                return new BanorteSeguroService();
            default:
                throw new \Exception('Compañía no soportada');
        }
    }
}
