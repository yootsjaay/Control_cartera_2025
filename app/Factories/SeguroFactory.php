<?php

namespace App\Factories;

use App\Services\SeguroServiceInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class SeguroFactory
{
    public static function crearSeguroService(string $slug): SeguroServiceInterface
    {
        // Obtener la configuración desde config/aseguradoras.php
        $servicios = Config::get('aseguradoras.servicios', []);

        if (!isset($servicios[$slug])) {
            throw new InvalidArgumentException("Compañía con slug '{$slug}' no está soportada. Aseguradoras disponibles: " . implode(', ', array_keys($servicios)));
        }

        $serviceClass = $servicios[$slug];  
        $service = resolve($serviceClass);

        if (!$service instanceof SeguroServiceInterface) {
            throw new InvalidArgumentException("El servicio '{$serviceClass}' no implementa SeguroServiceInterface.");
        }

        return $service;
    }
}
