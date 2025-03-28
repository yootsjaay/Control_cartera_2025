<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
     * Registrar servicios.
     */
    public function register(): void
    {
        // Bind del parser PDF
        $this->app->singleton(Parser::class, function ($app) {
            return new Parser();
        });

        // Definir los servicios por nombre de compañía
        $this->app->singleton(SeguroServiceFactory::class, function ($app) {
            return new SeguroServiceFactory([
                'HDI Seguros' => HdiSegurosService::class,
                'Banorte Seguros' => BanorteSeguroService::class,
                'Qualitas' => QualitasSeguroService::class,
                'GMX Seguros' => GmxSeguroService::class,
                'Thona Seguros'=> ThonaSeguroService::class,
                // Agrega más servicios según sea necesario
            ]);
        });
    

     

        // Registrar los servicios (sin instanciarlos directamente)
        $this->app->bind(HdiSegurosService::class, function ($app) {
          return new HdiSegurosService($app->make(Parser::class));
        });

        $this->app->bind(BanorteSeguroService::class, function ($app) {
            return new BanorteSeguroService($app->make(Parser::class));
        });

        $this->app->bind(QualitasSeguroService::class, function ($app) {
            return new QualitasSeguroService($app->make(Parser::class));
        });

        $this->app->bind(GmxSeguroService::class, function ($app) {
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