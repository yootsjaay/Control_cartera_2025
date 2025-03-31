<?php

namespace App\Services;

use App\Services\SeguroServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SeguroServiceFactory
{
    protected $servicios;

    public function __construct(array $servicios = [])
    {
        $this->servicios = $servicios;
    }

    public function crearSeguroService(string $nombreCompania): SeguroServiceInterface
    {
        if (!isset($this->servicios[$nombreCompania])) {
            throw new \InvalidArgumentException("Compañía '{$nombreCompania}' no está soportada.");
        }

        $serviceClass = $this->servicios[$nombreCompania];
        $service = app($serviceClass);

        if (!$service instanceof SeguroServiceInterface) {
            throw new \InvalidArgumentException("El servicio '{$serviceClass}' no implementa SeguroServiceInterface.");
        }

        return $service;
    }



    public function createFromRequest(Request $request): SeguroServiceInterface
    {
        // Obtienes el nombre de la compañía del request
        $nombreCompania = $request->input('compania_nombre');

        // Crear el servicio a partir del nombre de la compañía
        return $this->crearSeguroService($nombreCompania);
    }
}
