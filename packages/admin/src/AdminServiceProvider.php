<?php

namespace App\Admin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Container\Container;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/admin.php', 'admin'
        );
        
        // Register the container as a singleton for dependency injection
        $this->app->singleton('admin.container', function ($app) {
            return $app;
        });
        
        // Register basic services related to the admin package
        $this->app->singleton('admin.config', function () {
            return config('admin');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'admin');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        $this->publishes([
            __DIR__.'/../config/admin.php' => config_path('admin.php'),
        ], 'admin-config');
        
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/admin'),
        ], 'admin-views');
    }
}