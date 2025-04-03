<?php

namespace App\Services\Factories;

use App\Models\Compania; // Importamos el modelo de compañía
use App\Services\Contracts\SeguroServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SeguroServiceFactory
{
    public function crearSeguroService(string $nombreCompania): SeguroServiceInterface
    {
        // Obtener la compañía desde la base de datos
        $compania = Compania::where('nombre', $nombreCompania)->first();

        if (!$compania || empty($compania->servicio_clase)) {
            throw new \InvalidArgumentException("Compañía '{$nombreCompania}' no está soportada o no tiene un servicio asociado.");
        }

        $serviceClass = $compania->servicio_clase;

        // Intentar instanciar el servicio
        try {
            $service = app($serviceClass);

            if (!$service instanceof SeguroServiceInterface) {
                throw new \InvalidArgumentException("El servicio '{$serviceClass}' no implementa SeguroServiceInterface.");
            }

            return $service;
        } catch (\Exception $e) {
            Log::error("Error al crear el servicio de seguro para {$nombreCompania}: " . $e->getMessage());
            throw new \InvalidArgumentException("Error al crear el servicio de seguro para '{$nombreCompania}'.");
        }
    }

    public function createFromRequest(Request $request): SeguroServiceInterface
    {
        // Obtener el nombre de la compañía del request
        $nombreCompania = $request->input('nombre');

        // Crear el servicio a partir del nombre de la compañía
        return $this->crearSeguroService($nombreCompania);
    }
}
