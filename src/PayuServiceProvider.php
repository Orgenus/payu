<?php

namespace Orgenus\Payu;



use Illuminate\Support\ServiceProvider;

class PayuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__.'/config/payu.php' => config_path('payu.php')]);
    }

    public function register()
    {
        //Register Our Package routes
        include __DIR__.'/routes.php';

        $this->app->bind(
            'payu',
            'Orgenus\Payu\Payu'
        );

    }

}