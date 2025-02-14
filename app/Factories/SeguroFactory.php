<?php

namespace App\Factories;

use App\Services\SeguroServiceInterface;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;


class SeguroServiceFactory
{
    protected $servicios;

    public function __construct(array $servicios)
    {
        $this->servicios = $servicios;
    }

    public function createFromRequest(Request $request): SeguroServiceInterface
    {
        $slug = $request->input('compania_slug'); // slug de la compañía del request

        if (!isset($this->servicios[$slug])) {
            throw new \InvalidArgumentException("Compañía no soportada: " . $slug);
        }

        $serviceClass = $this->servicios[$slug];
        return app($serviceClass); //  app() para instanciar, permitiendo inyección de dependencias
    }
}