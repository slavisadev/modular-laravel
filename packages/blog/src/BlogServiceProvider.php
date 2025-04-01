<?php

namespace App\Blog;

use Illuminate\Support\ServiceProvider;
use Illuminate\Container\Container;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/blog.php', 'blog'
        );
        
        // Register the container as a singleton for dependency injection
        $this->app->singleton('blog.container', function ($app) {
            return $app;
        });
        
        // Register basic services related to the blog package
        $this->app->singleton('blog.config', function () {
            return config('blog');
        });
        
        // Register the Post model in the container
        $this->app->singleton('blog.post', function () {
            return new Models\Post();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'blog');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        $this->publishes([
            __DIR__.'/../config/blog.php' => config_path('blog.php'),
        ], 'blog-config');
        
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/blog'),
        ], 'blog-views');
    }
}