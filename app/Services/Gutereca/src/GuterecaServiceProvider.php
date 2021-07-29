<?php

namespace Aceroster\Gutereca;

use Illuminate\Support\ServiceProvider;

class GuterecaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/config/gutereca.php' => config_path('gutereca.php')], 'config');
        require __DIR__ . '/Http/routes.php';
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->publishes([__DIR__ . '/../public' => public_path('vendor/gutereca')], 'public');
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Gutereca::class, function () {
            return new Gutereca();
        });
        $this->app->alias(Gutereca::class, 'gutereca');
    }
}

