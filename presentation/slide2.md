# Package Development Basics

- **Structure of a Laravel package**
  - src/ directory for PHP classes
  - config/ for configuration files
  - database/ for migrations and seeders
  - resources/ for views, translations, and assets
  - routes/ for defining package routes
  
  ```
  your-package/
  ├── src/
  │   ├── YourPackageServiceProvider.php
  │   └── Facades/
  ├── config/
  │   └── your-package.php
  ├── database/
  │   └── migrations/
  ├── resources/
  │   ├── views/
  │   └── lang/
  ├── routes/
  │   └── web.php
  ├── tests/
  ├── composer.json
  └── README.md
  ```

- **composer.json configuration**
  - Package namespace and autoloading
  - Dependencies and version constraints
  - Extra section for Laravel-specific metadata
  - Scripts for installation and testing
  - License and author information
  
  ```json
  {
      "name": "your-vendor/your-package",
      "description": "Your package description",
      "type": "library",
      "license": "MIT",
      "authors": [
          {
              "name": "Your Name",
              "email": "your.email@example.com"
          }
      ],
      "autoload": {
          "psr-4": {
              "YourVendor\\YourPackage\\": "src/"
          }
      },
      "require": {
          "php": "^8.1",
          "illuminate/support": "^10.0"
      },
      "require-dev": {
          "orchestra/testbench": "^8.0",
          "phpunit/phpunit": "^10.0"
      },
      "extra": {
          "laravel": {
              "providers": [
                  "YourVendor\\YourPackage\\YourPackageServiceProvider"
              ],
              "aliases": {
                  "YourPackage": "YourVendor\\YourPackage\\Facades\\YourPackage"
              }
          }
      }
  }
  ```

- **Service providers**
  - Boot and register methods for initialization
  - Binding services to the container
  - Publishing configuration, views, and migrations
  - Registering routes, commands, and middleware
  - Optional deferred loading for performance
  
  ```php
  namespace YourVendor\YourPackage;

  use Illuminate\Support\ServiceProvider;

  class YourPackageServiceProvider extends ServiceProvider
  {
      public function register()
      {
          // Register bindings in the container
          $this->app->singleton('your-package', function ($app) {
              return new YourPackage($app['config']['your-package']);
          });
          
          // Merge configuration
          $this->mergeConfigFrom(
              __DIR__.'/../config/your-package.php', 'your-package'
          );
      }

      public function boot()
      {
          // Publish configuration
          $this->publishes([
              __DIR__.'/../config/your-package.php' => config_path('your-package.php'),
          ], 'config');
          
          // Publish migrations
          $this->publishes([
              __DIR__.'/../database/migrations/' => database_path('migrations'),
          ], 'migrations');
          
          // Load routes
          $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
          
          // Load views
          $this->loadViewsFrom(__DIR__.'/../resources/views', 'your-package');
      }
  }
  ```

- **Package auto-discovery**
  - Automatic registration of service providers
  - Adding to extra section in composer.json
  - Defining facades and commands for discovery
  - User override options in app config
  - Backwards compatibility for manual registration

- **Facades and contracts**
  - Providing convenient static interfaces
  - Defining clear contracts for implementation
  - Dependency injection alternatives
  - Balancing ease of use and testability
  - Laravel's pattern of facade + contract pairs
  
  ```php
  // Contract (Interface)
  namespace YourVendor\YourPackage\Contracts;

  interface YourPackageInterface
  {
      public function doSomething($param);
  }

  // Implementation
  namespace YourVendor\YourPackage;

  use YourVendor\YourPackage\Contracts\YourPackageInterface;

  class YourPackage implements YourPackageInterface
  {
      public function doSomething($param)
      {
          return "Did something with {$param}";
      }
  }

  // Facade
  namespace YourVendor\YourPackage\Facades;

  use Illuminate\Support\Facades\Facade;

  class YourPackage extends Facade
  {
      protected static function getFacadeAccessor()
      {
          return 'your-package';
      }
  }
  ```