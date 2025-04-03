# Advanced Package Techniques

- **Package configuration cascading**
  - Environment-specific configuration
  - User-overridable defaults
  - Runtime configuration changes
  - Configuration validation
  - Configuration caching strategies
  
  ```php
  namespace YourVendor\YourPackage;
  
  class ConfigurationManager
  {
      protected $config;
      protected $defaultConfig;
      protected $runtimeOverrides = [];
      
      public function __construct(array $config, array $defaultConfig)
      {
          $this->defaultConfig = $defaultConfig;
          $this->config = array_replace_recursive($defaultConfig, $config);
          $this->validateConfig();
      }
      
      public function get($key = null, $default = null)
      {
          // Check runtime overrides first
          if ($key !== null && isset($this->runtimeOverrides[$key])) {
              return $this->runtimeOverrides[$key];
          }
          
          // Get from config with dot notation support
          if ($key === null) {
              return $this->config;
          }
          
          return data_get($this->config, $key, $default);
      }
      
      public function set($key, $value)
      {
          // Set a runtime configuration override
          $this->runtimeOverrides[$key] = $value;
          
          return $this;
      }
      
      public function reset($key = null)
      {
          if ($key === null) {
              // Reset all runtime overrides
              $this->runtimeOverrides = [];
          } else {
              // Reset specific key
              unset($this->runtimeOverrides[$key]);
          }
          
          return $this;
      }
      
      public function toArray()
      {
          // Merge runtime overrides with config
          return array_replace_recursive($this->config, $this->runtimeOverrides);
      }
      
      protected function validateConfig()
      {
          // Validate required configuration options
          $requiredKeys = ['api_key', 'connection'];
          
          foreach ($requiredKeys as $key) {
              if (empty($this->config[$key])) {
                  throw new \InvalidArgumentException("Missing required configuration: {$key}");
              }
          }
          
          // Validate specific values
          $allowedModes = ['sync', 'async', 'queue'];
          
          if (!in_array($this->config['mode'], $allowedModes)) {
              throw new \InvalidArgumentException(
                  "Invalid mode: {$this->config['mode']}. Allowed values: " . implode(', ', $allowedModes)
              );
          }
      }
  }
  ```

- **Package discovery & extension**
  - Building extensible packages
  - Plugin systems for packages
  - Event-based extension points
  - Service container extension
  - Macro and mixin patterns
  
  ```php
  namespace YourVendor\YourPackage;
  
  class ExtensionManager
  {
      protected $extensions = [];
      protected $app;
      
      public function __construct($app)
      {
          $this->app = $app;
      }
      
      public function register($name, $extension)
      {
          $this->extensions[$name] = $extension;
          
          // If extension is a class name, resolve it from the container
          if (is_string($extension) && class_exists($extension)) {
              $this->extensions[$name] = $this->app->make($extension);
          }
          
          return $this;
      }
      
      public function extend($name, $callback)
      {
          if (!isset($this->extensions[$name])) {
              throw new \InvalidArgumentException("Extension [{$name}] not registered.");
          }
          
          $extension = $this->extensions[$name];
          
          // Apply the callback to the extension
          $callback($extension);
          
          return $this;
      }
      
      public function all()
      {
          return $this->extensions;
      }
      
      public function get($name)
      {
          if (!isset($this->extensions[$name])) {
              throw new \InvalidArgumentException("Extension [{$name}] not registered.");
          }
          
          return $this->extensions[$name];
      }
  }
  
  // Using macros for extending functionality
  namespace YourVendor\YourPackage;
  
  use Illuminate\Support\Traits\Macroable;
  
  class QueryBuilder
  {
      use Macroable;
      
      // Core methods...
  }
  
  // Registering macros to extend functionality
  QueryBuilder::macro('whereLike', function ($attributes, $searchTerm) {
      return $this->where(function ($query) use ($attributes, $searchTerm) {
          foreach ((array) $attributes as $attribute) {
              $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
          }
      });
  });
  ```

- **Artisan command generators**
  - Custom stubs and templates
  - Interactive command prompts
  - Code generation strategies
  - Scaffold generation for repetitive tasks
  - Post-generation hooks and actions
  
  ```php
  namespace YourVendor\YourPackage\Commands;
  
  use Illuminate\Console\Command;
  use Illuminate\Support\Str;
  use Illuminate\Filesystem\Filesystem;
  
  class GenerateServiceCommand extends Command
  {
      protected $signature = 'make:your-service {name} {--interface : Create an interface for this service}'
          . ' {--test : Create a test for this service}'
          . ' {--force : Overwrite existing files}'
          . ' {--namespace= : The namespace for the service}';
          
      protected $description = 'Generate a new service class with optional interface and test';
      
      protected $files;
      
      public function __construct(Filesystem $files)
      {
          parent::__construct();
          $this->files = $files;
      }
      
      public function handle()
      {
          $name = $this->argument('name');
          $namespace = $this->option('namespace') ?: 'App\\Services';
          $createInterface = $this->option('interface');
          $createTest = $this->option('test');
          $force = $this->option('force');
          
          // Generate the service class
          $this->createService($name, $namespace, $createInterface, $force);
          
          // Generate the interface if requested
          if ($createInterface) {
              $this->createInterface($name, $namespace, $force);
          }
          
          // Generate the test if requested
          if ($createTest) {
              $this->createTest($name, $namespace, $force);
          }
          
          $this->info(Str::studly($name) . ' service generated successfully!');
          
          // Run post-generation hooks
          $this->runPostGenerationHooks($name, $namespace);
          
          return Command::SUCCESS;
      }
      
      protected function createService($name, $namespace, $hasInterface, $force)
      {
          $className = Str::studly($name) . 'Service';
          $interfaceName = $hasInterface ? Str::studly($name) . 'ServiceInterface' : null;
          
          $stubPath = $hasInterface 
              ? __DIR__ . '/../stubs/service-with-interface.stub' 
              : __DIR__ . '/../stubs/service.stub';
              
          $stub = $this->files->get($stubPath);
          
          $stub = str_replace(
              ['{{ namespace }}', '{{ class }}', '{{ interface }}'],
              [$namespace, $className, $interfaceName],
              $stub
          );
          
          $path = $this->getPath($namespace, $className);
          
          if (!$force && $this->files->exists($path)) {
              $this->error($className . ' already exists!');
              return;
          }
          
          $this->makeDirectory($path);
          $this->files->put($path, $stub);
          
          $this->info($className . ' created successfully.');
      }
      
      // Other methods for creating interfaces, tests, etc.
  }
  ```

- **Module systems**
  - Domain-driven module organization
  - Module dependency management
  - Namespaced routing and middleware
  - Inter-module communication
  - Module activation/deactivation systems
  
  ```php
  namespace YourVendor\ModuleSystem;
  
  use Illuminate\Support\Facades\File;
  use Illuminate\Contracts\Container\Container;
  
  class ModuleManager
  {
      protected $app;
      protected $modules = [];
      protected $booted = false;
      
      public function __construct(Container $app)
      {
          $this->app = $app;
      }
      
      public function register($path)
      {
          $moduleConfig = $this->loadModuleConfig($path);
          
          if (!$moduleConfig) {
              return false;
          }
          
          $name = $moduleConfig['name'];
          
          // Check dependencies
          if (isset($moduleConfig['depends']) && is_array($moduleConfig['depends'])) {
              foreach ($moduleConfig['depends'] as $dependency) {
                  if (!isset($this->modules[$dependency])) {
                      throw new \RuntimeException("Module {$name} depends on {$dependency} which is not loaded.");
                  }
              }
          }
          
          // Register the module
          $this->modules[$name] = [
              'config' => $moduleConfig,
              'path' => $path,
              'active' => true,
          ];
          
          // Register service provider if exists
          if (isset($moduleConfig['provider'])) {
              $this->app->register($moduleConfig['provider']);
          }
          
          return true;
      }
      
      public function boot()
      {
          if ($this->booted) {
              return;
          }
          
          foreach ($this->modules as $name => $module) {
              if (!$module['active']) {
                  continue;
              }
              
              $this->bootModule($name, $module);
          }
          
          $this->booted = true;
      }
      
      protected function bootModule($name, $module)
      {
          $config = $module['config'];
          
          // Load translations
          if (is_dir($module['path'] . '/resources/lang')) {
              $this->app['translator']->addNamespace($name, $module['path'] . '/resources/lang');
          }
          
          // Load views
          if (is_dir($module['path'] . '/resources/views')) {
              $this->app['view']->addNamespace($name, $module['path'] . '/resources/views');
          }
          
          // Load routes
          if (isset($config['routes']) && is_array($config['routes'])) {
              foreach ($config['routes'] as $file) {
                  $path = $module['path'] . '/' . $file;
                  if (File::exists($path)) {
                      require $path;
                  }
              }
          }
          
          // Fire module booted event
          if (isset($config['listeners']['boot'])) {
              $listener = $config['listeners']['boot'];
              if (is_callable($listener)) {
                  $listener($this->app);
              }
          }
      }
      
      protected function loadModuleConfig($path)
      {
          $configPath = $path . '/module.json';
          
          if (!File::exists($configPath)) {
              return null;
          }
          
          return json_decode(File::get($configPath), true);
      }
  }
  ```

- **Integrating with Laravel ecosystem**
  - Nova/Livewire/Inertia compatibility
  - Horizon job monitoring integration
  - Telescope debugging support
  - Vapor/Forge deployment considerations
  - Event-driven interactions with other packages
  
  ```php
  namespace YourVendor\YourPackage;
  
  use Laravel\Nova\Nova;
  use Laravel\Nova\Tool;
  use Illuminate\Support\Facades\Event;
  use Illuminate\Support\Facades\Gate;
  use Laravel\Horizon\Horizon;
  
  class YourPackageServiceProvider extends ServiceProvider
  {
      public function register()
      {
          // Register core services
          $this->app->singleton('your-package', function ($app) {
              return new YourPackage($app['config']['your-package']);
          });
      }
      
      public function boot()
      {
          // Nova integration
          $this->bootNova();
          
          // Horizon integration
          $this->bootHorizon();
          
          // Telescope integration
          $this->bootTelescope();
          
          // Listen for events from other packages
          $this->registerEventListeners();
      }
      
      protected function bootNova()
      {
          if (!class_exists(Nova::class)) {
              return;
          }
          
          Nova::serving(function () {
              Nova::tools([
                  new class extends Tool {
                      public function boot()
                      {
                          Nova::script('your-package', __DIR__.'/../dist/js/tool.js');
                          Nova::style('your-package', __DIR__.'/../dist/css/tool.css');
                      }
                      
                      public function menu(Request $request)
                      {
                          return MenuItem::make('Your Tool')
                              ->path('/your-package')
                              ->icon('chart-bar');
                      }
                  },
              ]);
              
              // Register resource
              Nova::resources([
                  YourResource::class,
              ]);
          });
      }
      
      protected function bootHorizon()
      {
          if (!class_exists(Horizon::class)) {
              return;
          }
          
          // Add custom Horizon metrics
          Horizon::auth(function ($request) {
              return Gate::check('viewHorizon', [$request->user()]);
          });
          
          // Tag jobs from this package
          Horizon::tag(function ($job) {
              if ($job instanceof YourPackageJob) {
                  return ['your-package'];
              }
              
              return [];
          });
      }
      
      protected function bootTelescope()
      {
          if (!class_exists(\Laravel\Telescope\Telescope::class)) {
              return;
          }
          
          // Configure Telescope watcher for package
          \Laravel\Telescope\Telescope::tag(function ($entry) {
              if ($entry->type === 'request' && Str::startsWith($entry->content['uri'], '/your-package')) {
                  return ['your-package'];
              }
              
              return [];
          });
      }
      
      protected function registerEventListeners()
      {
          // Listen for events from other packages
          Event::listen('other-package.event', function ($event) {
              // React to the event
          });
          
          // Provide your own events for others
          $this->app['events']->listen('your-package.*', function ($eventName, array $data) {
              // Log events for debugging
              if (config('your-package.debug')) {
                  logger("Event fired: {$eventName}", $data);
              }
          });
      }
  }
  ```