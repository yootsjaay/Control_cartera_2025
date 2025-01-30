<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SeguroServiceInterface;
use App\Services\HdiSegurosService;
use App\Services\BanorteSeguroService;
use App\Services\QualitasSeguroService;
use App\Services\GmxSeguroService;
use Smalot\PdfParser\Parser;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       /*if(config('app.env') == 'local'){
            $this->app['request']->server->set('HTTPS', 'true');
        }*/
        $this->app->singleton('seguro.hdi', function () {
            return new HdiSegurosService(app(Parser::class)); // Inyectando dependencias necesarias
        });

        $this->app->singleton('seguro.banorte', function () {
            return new BanorteSeguroService(app(Parser::class));
        });

        $this->app->singleton('seguro.qualitas', function () {
            return new QualitasSeguroService(app(Parser::class));
        });

        $this->app->singleton('seguro.gmx', function () {
            return new GmxSeguroService(app(Parser::class));
        });
    }

    /**
     * Realizar cualquier trabajo de inicialización necesario.
     *
     * @return void
     */

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
