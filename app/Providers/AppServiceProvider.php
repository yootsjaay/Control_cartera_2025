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

     /*   if(config('app.env') ==='local'){
            $this->app['request']->server->set('HTTPS', true);
        }*/
        // Bind del parser PDF
        $this->app->singleton(Parser::class, function ($app) {
            return new Parser(); // Puedes agregar opciones de configuración aquí si es necesario
        });

        // Bind del Factory
        $this->app->singleton(SeguroServiceFactory::class, function ($app) {
            return new SeguroServiceFactory(config('aseguradoras.servicios')); // Inyectar configuración
        });

        // Bind de la interfaz al factory (IMPORTANTE)
        $this->app->bind(SeguroServiceInterface::class, SeguroServiceFactory::class);


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