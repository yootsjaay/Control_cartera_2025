<?php

namespace App\Services;

use App\Services\SeguroServiceInterface; // Importa la interfaz
use Illuminate\Http\Request; // Si necesitas Request
use Illuminate\Support\Facades\Config;

class SeguroServiceFactory
{
    protected $servicios;

    public function __construct(array $servicios)
    {
        $this->servicios = $servicios;
    }

    public function crearSeguroService(string $nombreCompania): SeguroServiceInterface
    {
        // Usamos el nombre de la compañía para acceder al servicio correspondiente
        if (!isset($this->servicios[$nombreCompania])) {
            throw new \InvalidArgumentException("Compañía '{$nombreCompania}' no está soportada.");
        }

        $serviceClass = $this->servicios[$nombreCompania];
        $service = app($serviceClass); // Usar app() para instanciar

        if (!$service instanceof SeguroServiceInterface) {
            throw new \InvalidArgumentException("El servicio '{$serviceClass}' no implementa SeguroServiceInterface.");
        }

        return $service;
    }

    public function createFromRequest(Request $request): SeguroServiceInterface
    {
        // Obtienes el nombre de la compañía del request
        $nombreCompania = $request->input('compania_nombre'); // Asegúrate que este campo exista en el request
        return $this->crearSeguroService($nombreCompania);
    }
}
