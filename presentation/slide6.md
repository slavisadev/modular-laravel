# Scenario 4: Database & Eloquent Extensions

- **Custom database connections**
  - Multiple database support
  - Read/write splitting for high-load applications
  - Geographic sharding implementations
  - Non-relational database integrations
  - Legacy database system adapters
  
  ```php
  // Example of read/write connection setup in config/database.php
  'mysql' => [
      'read' => [
          'host' => [
              env('DB_READ_HOST_1', '192.168.1.1'),
              env('DB_READ_HOST_2', '192.168.1.2'),
          ],
      ],
      'write' => [
          'host' => env('DB_WRITE_HOST', '192.168.1.3'),
      ],
      'sticky' => true,
      'driver' => 'mysql',
      'database' => env('DB_DATABASE', 'forge'),
      'username' => env('DB_USERNAME', 'forge'),
      'password' => env('DB_PASSWORD', ''),
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
  ],
  
  // Implementing a custom database connection
  namespace YourVendor\DatabaseExtensions;
  
  use Illuminate\Database\Connection;
  
  class CustomConnection extends Connection
  {
      public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
      {
          parent::__construct($pdo, $database, $tablePrefix, $config);
          
          // Custom initialization for your connection
      }
      
      protected function getDefaultQueryGrammar()
      {
          return new CustomQueryGrammar();
      }
      
      protected function getDefaultSchemaGrammar()
      {
          return new CustomSchemaGrammar();
      }
  }
  
  // Register in service provider
  $this->app->singleton('db.connector.custom', function () {
      return new CustomConnector();
  });
  
  $this->app->singleton('db.connection.custom', function ($app, $config) {
      return new CustomConnection(
          $config['pdo'],
          $config['database'],
          $config['prefix'],
          $config
      );
  });
  ```

- **Eloquent model traits**
  - Reusable behavior extensions (SoftDeletes, HasUuid, etc.)
  - Automatic data transformation (encryption, formatting)
  - Audit logging and change tracking
  - Complex relationship management
  - Performance enhancement traits
  
  ```php
  // UUID trait example
  namespace YourVendor\EloquentExtensions\Traits;
  
  use Illuminate\Support\Str;
  
  trait HasUuid
  {
      protected static function bootHasUuid()
      {
          static::creating(function ($model) {
              if (! $model->{$model->getUuidColumn()}) {
                  $model->{$model->getUuidColumn()} = (string) Str::uuid();
              }
          });
      }
      
      public function getUuidColumn()
      {
          return $this->uuidColumn ?? 'uuid';
      }
      
      public function scopeWhereUuid($query, $uuid)
      {
          return $query->where($this->getUuidColumn(), $uuid);
      }
  }
  
  // Auditable trait for tracking changes
  trait Auditable
  {
      public static function bootAuditable()
      {
          static::created(function ($model) {
              $model->recordActivity('created');
          });
          
          static::updated(function ($model) {
              $model->recordActivity('updated');
          });
          
          static::deleted(function ($model) {
              $model->recordActivity('deleted');
          });
      }
      
      protected function recordActivity($event)
      {
          Activity::create([
              'user_id' => auth()->id() ?? null,
              'model_type' => get_class($this),
              'model_id' => $this->getKey(),
              'event' => $event,
              'changes' => $this->getChanges(),
              'ip_address' => request()->ip(),
              'user_agent' => request()->userAgent(),
          ]);
      }
  }
  ```

- **Query builder extensions**
  - Custom macros for frequent query patterns
  - Advanced filtering and search capabilities
  - Complex joining and subquery abstractions
  - Geographic/spatial query helpers
  - Analytics and reporting query generators
  
  ```php
  // Register query builder macros in service provider
  use Illuminate\Database\Query\Builder;
  use Illuminate\Support\ServiceProvider;
  
  class QueryExtensionServiceProvider extends ServiceProvider
  {
      public function boot()
      {
          // Add a "whereLike" macro for easier "like" searches
          Builder::macro('whereLike', function ($attributes, $searchTerm) {
              $this->where(function ($query) use ($attributes, $searchTerm) {
                  foreach (array_wrap($attributes) as $attribute) {
                      $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                  }
              });
              
              return $this;
          });
          
          // Add a "whereJsonContains" macro for JSON queries
          Builder::macro('whereJsonContains', function ($column, $value) {
              return $this->whereRaw(
                  "JSON_CONTAINS({$this->grammar->wrap($column)}, ?)",
                  [json_encode($value)]
              );
          });
          
          // Add a "toRawSql" macro to help with debugging
          Builder::macro('toRawSql', function () {
              $bindings = $this->getBindings();
              $sql = $this->toSql();
              
              foreach ($bindings as $binding) {
                  $value = is_numeric($binding) ? $binding : "'".$binding."'";
                  $sql = preg_replace('/\?/', $value, $sql, 1);
              }
              
              return $sql;
          });
      }
  }
  ```

- **Migration packages**
  - Schema modification templates
  - Data migration utilities
  - Database version control strategies
  - Zero-downtime migration patterns
  - Migration testing frameworks
  
  ```php
  // Example of a migration class with safe methods for zero-downtime deployment
  namespace YourVendor\MigrationPatterns;
  
  use Illuminate\Database\Schema\Blueprint;
  use Illuminate\Support\Facades\Schema;
  
  class SafeMigration
  {
      /**
       * Add a column safely in multiple steps to avoid locking the table.
       */
      public static function addColumn($table, $column, $type, $options = [])
      {
          // Step 1: Add the column as nullable regardless of final state
          Schema::table($table, function (Blueprint $table) use ($column, $type, $options) {
              $table->$type($column)->nullable()->default(null);
          });
          
          // Step 2: If the column should not be nullable or has a default value, update it
          if (isset($options['nullable']) && $options['nullable'] === false) {
              // First populate any existing rows
              DB::table($table)->whereNull($column)->update([
                  $column => $options['default'] ?? null,
              ]);
              
              // Then update the column to be non-nullable
              Schema::table($table, function (Blueprint $table) use ($column) {
                  $table->$type($column)->nullable(false)->change();
              });
          }
          
          // Step 3: Set the final default if needed (separate operation)
          if (isset($options['default'])) {
              Schema::table($table, function (Blueprint $table) use ($column, $type, $options) {
                  $table->$type($column)
                      ->default($options['default'])
                      ->change();
              });
          }
      }
  }
  ```

- **Database tools & utilities**
  - Data anonymization for development/testing
  - Database schema visualization
  - Performance analysis and query profiling
  - Backup and restoration systems
  - Data integrity validation tools
  
  ```php
  // Data anonymizer example
  namespace YourVendor\DatabaseTools;
  
  class DataAnonymizer
  {
      protected $faker;
      protected $rules = [];
      
      public function __construct(\Faker\Generator $faker)
      {
          $this->faker = $faker;
      }
      
      public function setRules(array $rules)
      {
          $this->rules = $rules;
          return $this;
      }
      
      public function anonymize($model, $applyRules = true)
      {
          $table = $model->getTable();
          $primaryKey = $model->getKeyName();
          
          if (!isset($this->rules[$table]) && $applyRules) {
              throw new \InvalidArgumentException("No anonymization rules for table {$table}");
          }
          
          $rules = $applyRules ? $this->rules[$table] : [];
          
          $query = DB::table($table);
          
          // Process in chunks to avoid memory issues
          $query->orderBy($primaryKey)->chunk(1000, function ($records) use ($table, $rules) {
              foreach ($records as $record) {
                  $updates = [];
                  
                  foreach ($rules as $column => $rule) {
                      $updates[$column] = $this->processRule($rule);
                  }
                  
                  if (!empty($updates)) {
                      DB::table($table)
                          ->where('id', $record->id)
                          ->update($updates);
                  }
              }
          });
      }
      
      protected function processRule($rule)
      {
          if (is_callable($rule)) {
              return $rule($this->faker);
          }
          
          if (is_string($rule) && method_exists($this->faker, $rule)) {
              return $this->faker->$rule();
          }
          
          return $rule;
      }
  }
  
  // Using the anonymizer
  $anonymizer = new DataAnonymizer(Faker\Factory::create());
  
  $anonymizer->setRules([
      'users' => [
          'email' => function ($faker) {
              return $faker->safeEmail();
          },
          'name' => 'name',
          'phone' => 'phoneNumber',
          'address' => 'address',
          'password' => bcrypt('password'),
          // Keep some fields like id, created_at, etc.
      ],
      // Rules for other tables
  ]);
  
  $anonymizer->anonymize(new \App\Models\User());
  ```