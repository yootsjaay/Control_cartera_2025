<?php

namespace App\Factories;

use App\Services\SeguroServiceInterface;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

/**
 * Factory para crear instancias de servicios de seguros.
 */
class SeguroFactory
{
    /**
     * Crea y retorna una instancia de SeguroServiceInterface basado en el slug de la compañía.
     *
     * @param string $slug Slug de la compañía aseguradora.
     * @return SeguroServiceInterface
     *
     * @throws InvalidArgumentException Si el slug no está configurado o el servicio no implementa la interfaz.
     */
    public static function crearSeguroService(string $slug): SeguroServiceInterface
{
    $servicios = Config::get('aseguradoras.servicios', []);

    if (!isset($servicios[$slug])) {
        throw new InvalidArgumentException(
            "Compañía con slug '{$slug}' no está soportada. Aseguradoras disponibles: " . implode(', ', array_keys($servicios))
        );
    }

    $serviceClass = $servicios[$slug];
    $service = resolve($serviceClass);

    if (!$service instanceof SeguroServiceInterface) {
        throw new InvalidArgumentException("El servicio '{$serviceClass}' no implementa SeguroServiceInterface.");
    }

   

    return $service;
}

}
