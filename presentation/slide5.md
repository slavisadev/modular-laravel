# Scenario 3: UI Components & Themes

- **Blade component packages**
  - Reusable form elements and validators
  - Data presentation components (tables, charts, etc.)
  - Layout components (modals, tabs, cards)
  - Component attributes and slots
  - JavaScript interactions and animations
  
  ```php
  // Button component class
  namespace YourVendor\UiComponents;
  
  use Illuminate\View\Component;
  
  class Button extends Component
  {
      public $type;
      public $size;
      public $color;
      public $disabled;
      
      public function __construct(
          $type = 'button',
          $size = 'md',
          $color = 'primary',
          $disabled = false
      ) {
          $this->type = $type;
          $this->size = $size;
          $this->color = $color;
          $this->disabled = $disabled;
      }
      
      public function render()
      {
          return view('ui-components::button');
      }
      
      public function classes()
      {
          return [
              'btn',
              'btn-' . $this->color,
              'btn-' . $this->size,
              $this->disabled ? 'disabled' : '',
          ];
      }
  }
  
  // Button component view (button.blade.php)
  <button
      type="{{ $type }}"
      {{ $attributes->merge(['class' => implode(' ', $classes())]) }}
      {{ $disabled ? 'disabled' : '' }}
  >
      {{ $slot }}
  </button>
  
  // Usage in application
  <x-ui::button
      type="submit"
      color="success"
      size="lg"
      class="mt-4 w-full"
  >
      Save Changes
  </x-ui::button>
  ```

- **Admin panel packages**
  - CRUD generation for models
  - Dashboard widgets and analytics
  - User and permission management
  - Activity logging and audit trails
  - Customization and white-labeling options
  
  ```php
  // Admin panel resource definition
  namespace App\Admin\Resources;
  
  use YourVendor\AdminPanel\Resources\Resource;
  
  class UserResource extends Resource
  {
      public static $model = 'App\Models\User';
      
      public function fields()
      {
          return [
              ID::make()->sortable(),
              
              Text::make('Name')
                  ->sortable()
                  ->rules('required', 'max:255'),
                  
              Email::make('Email')
                  ->sortable()
                  ->rules('required', 'email', 'max:255')
                  ->creationRules('unique:users,email')
                  ->updateRules('unique:users,email,{{resourceId}}'),
                  
              Password::make('Password')
                  ->onlyOnForms()
                  ->creationRules('required', 'string', 'min:8')
                  ->updateRules('nullable', 'string', 'min:8'),
                  
              BelongsToMany::make('Roles'),
          ];
      }
      
      public function cards()
      {
          return [
              new UserStats,
              new NewUsers,
          ];
      }
      
      public function filters()
      {
          return [
              new RoleFilter,
          ];
      }
      
      public function actions()
      {
          return [
              new ResetPassword,
              new DeactivateUsers,
          ];
      }
  }
  ```

- **Theme systems**
  - Theme inheritance and overriding
  - Multiple theme support and switching
  - Asset compilation integration
  - Dynamic theme application
  - Responsive design considerations
  
  ```php
  // Theme manager service
  namespace YourVendor\ThemeManager;
  
  class ThemeManager
  {
      protected $theme;
      protected $themes = [];
      protected $defaultTheme;
      
      public function __construct(array $themes, $defaultTheme)
      {
          $this->themes = $themes;
          $this->defaultTheme = $defaultTheme;
          $this->theme = $defaultTheme;
      }
      
      public function setTheme($theme)
      {
          if (!isset($this->themes[$theme])) {
              throw new \InvalidArgumentException("Theme [{$theme}] not found.");
          }
          
          $this->theme = $theme;
          
          return $this;
      }
      
      public function current()
      {
          return $this->theme;
      }
      
      public function path($file = '')
      {
          $themePath = $this->themes[$this->theme]['path'];
          
          return $file ? $themePath . '/' . $file : $themePath;
      }
      
      public function asset($file)
      {
          return asset($this->path('assets/' . $file));
      }
      
      public function viewNamespace()
      {
          return $this->themes[$this->theme]['namespace'];
      }
  }
  
  // Theme service provider
  class ThemeServiceProvider extends ServiceProvider
  {
      public function register()
      {
          $this->app->singleton('theme', function ($app) {
              return new ThemeManager(
                  config('themes.themes'),
                  config('themes.default')
              );
          });
      }
      
      public function boot()
      {
          // Register theme view namespaces
          $themes = config('themes.themes');
          
          foreach ($themes as $name => $theme) {
              $this->loadViewsFrom($theme['path'] . '/views', $theme['namespace']);
          }
          
          // Register blade directives
          Blade::directive('theme', function ($expression) {
              return "<?php echo theme()->asset($expression); ?>";
          });
      }
  }
  ```

- **Asset management**
  - Webpack/Vite integration
  - Asset versioning and cache busting
  - SASS/LESS/PostCSS processing
  - Image optimization pipelines
  - CDN integration for asset delivery
  
  ```javascript
  // vite.config.js
  import { defineConfig } from 'vite';
  import laravel from 'laravel-vite-plugin';
  
  export default defineConfig({
      plugins: [
          laravel({
              input: [
                  'resources/css/app.css',
                  'resources/js/app.js',
                  // Theme assets
                  'resources/themes/default/css/theme.scss',
                  'resources/themes/default/js/theme.js',
                  'resources/themes/dark/css/theme.scss',
                  'resources/themes/dark/js/theme.js',
              ],
              refresh: true,
          }),
      ],
      resolve: {
          alias: {
              '@': '/resources/js',
              '@css': '/resources/css',
              '@themes': '/resources/themes',
          },
      },
  });
  
  // PHP asset helper (in theme manager)
  public function assetUrl($path, $secure = null)
  {
      $manifestPath = public_path('build/manifest.json');
      
      static $manifest;
      
      if (!$manifest && file_exists($manifestPath)) {
          $manifest = json_decode(file_get_contents($manifestPath), true);
      }
      
      $themePath = "resources/themes/{$this->theme}/";
      $assetPath = $themePath . $path;
      
      if (isset($manifest[$assetPath])) {
          return asset('build/' . $manifest[$assetPath], $secure);
      }
      
      return asset($path, $secure);
  }
  ```

- **Livewire/Inertia component libraries**
  - Stateful component lifecycle management
  - Client-server synchronization patterns
  - Progressive enhancement strategies
  - Performance optimization techniques
  - Testing strategies for reactive components
  
  ```php
  // Livewire data table component
  namespace YourVendor\LiveComponents;
  
  use Livewire\Component;
  
  class DataTable extends Component
  {
      public $model;
      public $columns = [];
      public $search = '';
      public $perPage = 15;
      public $sortField = 'id';
      public $sortDirection = 'asc';
      
      protected $listeners = ['refresh' => '$refresh'];
      
      public function sortBy($field)
      {
          if ($this->sortField === $field) {
              $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
          } else {
              $this->sortField = $field;
              $this->sortDirection = 'asc';
          }
      }
      
      public function updatingSearch()
      {
          $this->resetPage();
      }
      
      public function getRowsQueryProperty()
      {
          $query = $this->model::query();
          
          if ($this->search) {
              $query->where(function($subQuery) {
                  foreach ($this->columns as $column) {
                      if (isset($column['searchable']) && $column['searchable']) {
                          $subQuery->orWhere($column['field'], 'like', '%' . $this->search . '%');
                      }
                  }
              });
          }
          
          return $query->orderBy($this->sortField, $this->sortDirection);
      }
      
      public function getRowsProperty()
      {
          return $this->rowsQuery->paginate($this->perPage);
      }
      
      public function render()
      {
          return view('live-components::data-table', [
              'items' => $this->rows,
          ]);
      }
  }
  ```