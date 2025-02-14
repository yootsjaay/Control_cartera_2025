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

    public function crearSeguroService(string $slug): SeguroServiceInterface
    {
        if (!isset($this->servicios[$slug])) {
            throw new \InvalidArgumentException("Compañía con slug '{$slug}' no está soportada.");
        }

        $serviceClass = $this->servicios[$slug];
        $service = app($serviceClass); // Usar app() para instanciar

        if (!$service instanceof SeguroServiceInterface) {
            throw new \InvalidArgumentException("El servicio '{$serviceClass}' no implementa SeguroServiceInterface.");
        }

        return $service;
    }

    public function createFromRequest(Request $request): SeguroServiceInterface
    {
        $slug = $request->input('compania_slug'); // Asegúrate que este campo exista en el request
        return $this->crearSeguroService($slug);
    }
}