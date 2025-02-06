<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use App\Services\{
    SeguroServiceInterface,
    SeguroServiceFactory,
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
        // Verificamos si el Factory está bien construido antes de inyectar servicios
        $this->app->bind(SeguroServiceInterface::class, function ($app) {
            $request = $app->make(Request::class); // Aseguramos que Request está disponible
            return $app->make(SeguroServiceFactory::class)
                       ->createFromRequest($request);
        });

        // Definimos los servicios disponibles con la inyección de dependencias
        $this->app->singleton(HdiSegurosService::class, function ($app) {
            return new HdiSegurosService($app->make(Parser::class));
        });

        $this->app->singleton(BanorteSeguroService::class, function ($app) {
            return new BanorteSeguroService($app->make(Parser::class));
        });

        $this->app->singleton(QualitasSeguroService::class, function ($app) {
            return new QualitasSeguroService($app->make(Parser::class));
        });

        $this->app->singleton(GmxSeguroService::class, function ($app) {
            return new GmxSeguroService($app->make(Parser::class));
        });
    }

    /**
     * Bootstrap cualquier servicio necesario.
     */
    public function boot(): void
    {
        //
    }
}
