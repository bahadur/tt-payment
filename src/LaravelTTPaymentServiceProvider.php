<?php

namespace TTPayment\LaravelTTPayment;

use Illuminate\Support\ServiceProvider;

class LaravelTTPaymentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->mergeConfigFrom(__DIR__ . '/config/laravel-tt-payment.php', 'laravel-tt-payment');

        $this->publishes([
            __DIR__ . '/config/laravel-tt-payment.php' => config_path('laravel-tt-payment.php')
        ]);

    }

    public function register()
    {
        //$this->mergeConfigFrom(__DIR__ . '/../config/tt-payment.php', 'laravel-tt-payment');

    }

}
