<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Smalot\PdfParser\Parser;
use App\Services\{
    SeguroServiceInterface,
    HdiSegurosService,
    BanorteSeguroService,
    QualitasSeguroService,
    GmxSeguroService
};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registrar servicios de seguros.
     */
    public function register(): void
    {
    
            $this->app->bind(SeguroServiceInterface::class, function ($app) {
                $request = $app->make(Request::class);
                return $app->make(SeguroServiceFactory::class)
                           ->createFromRequest($request);
            });
    
        $seguros = [
            'hdi_seguros'      => HdiSegurosService::class,
            'banorte'          => BanorteSeguroService::class,
            'qualitas_seguros' => QualitasSeguroService::class,
            'gmx_seguro'       => GmxSeguroService::class,
        ];

        foreach ($seguros as $key => $service) {
            // Registramos los servicios como singletons, inyectando Parser donde sea necesario
            $this->app->singleton($key, function () use ($service) {
                // Dependiendo del servicio, le pasamos el Parser para manejar los PDFs
                return new $service(app(Parser::class));
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

