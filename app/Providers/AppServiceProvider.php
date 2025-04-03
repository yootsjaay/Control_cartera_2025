<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Smalot\PdfParser\Parser;
use App\Services\Factories\SeguroServiceFactory;
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
 

    $this->app->singleton(SeguroServiceFactory::class, function ($app) {
                return new SeguroServiceFactory([]);
    });

    }

    public function boot(): void
    {
        //
    }
}