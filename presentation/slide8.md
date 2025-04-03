# Package Testing Strategies

- **PHPUnit testing setup**
  - Test suite organization for packages
  - Mocking framework dependencies
  - Environment setup for tests
  - Database testing considerations
  - Code coverage goals and metrics
  
  ```php
  // phpunit.xml configuration for a package
  <?xml version="1.0" encoding="UTF-8"?>
  <phpunit
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      bootstrap="vendor/autoload.php"
      backupGlobals="false"
      colors="true"
      processIsolation="false"
      stopOnFailure="false"
      xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
      cacheDirectory=".phpunit.cache"
  >
      <coverage>
          <include>
              <directory suffix=".php">src/</directory>
          </include>
          <exclude>
              <directory suffix=".php">src/config</directory>
          </exclude>
          <report>
              <clover outputFile="build/logs/clover.xml"/>
              <html outputDirectory="build/coverage"/>
          </report>
      </coverage>
      <testsuites>
          <testsuite name="Unit">
              <directory suffix="Test.php">./tests/Unit</directory>
          </testsuite>
          <testsuite name="Feature">
              <directory suffix="Test.php">./tests/Feature</directory>
          </testsuite>
      </testsuites>
      <php>
          <env name="APP_ENV" value="testing"/>
          <env name="DB_CONNECTION" value="testing"/>
          <env name="CACHE_DRIVER" value="array"/>
      </php>
  </phpunit>
  ```

- **Orchestra Testbench**
  - Laravel application simulation for package testing
  - Service provider configuration
  - Environment variable management
  - Facade and helper testing
  - Artisan command testing
  
  ```php
  // Base test case using Orchestra Testbench
  namespace YourVendor\YourPackage\Tests;
  
  use Orchestra\Testbench\TestCase as BaseTestCase;
  use YourVendor\YourPackage\YourPackageServiceProvider;
  
  abstract class TestCase extends BaseTestCase
  {
      protected function getPackageProviders($app)
      {
          return [
              YourPackageServiceProvider::class,
          ];
      }
      
      protected function getPackageAliases($app)
      {
          return [
              'YourFacade' => 'YourVendor\YourPackage\Facades\YourFacade',
          ];
      }
      
      protected function getEnvironmentSetUp($app)
      {
          // Setup default database
          $app['config']->set('database.default', 'testbench');
          $app['config']->set('database.connections.testbench', [
              'driver' => 'sqlite',
              'database' => ':memory:',
              'prefix' => '',
          ]);
          
          // Package configuration
          $app['config']->set('your-package.key', 'value');
      }
      
      protected function setUpTraits()
      {
          parent::setUpTraits();
          
          $uses = array_flip(class_uses_recursive(static::class));
          
          if (isset($uses[RefreshDatabase::class])) {
              $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
          }
      }
  }
  ```

- **Feature vs. Unit tests**
  - Isolated unit testing approaches
  - Integration testing with Laravel services
  - Browser testing for UI components
  - API testing for endpoints
  - Performance testing considerations
  
  ```php
  // Unit test example
  namespace YourVendor\YourPackage\Tests\Unit;
  
  use PHPUnit\Framework\TestCase;
  use YourVendor\YourPackage\Calculator;
  
  class CalculatorTest extends TestCase
  {
      public function test_it_adds_two_numbers()
      {
          $calculator = new Calculator();
          $result = $calculator->add(5, 10);
          
          $this->assertEquals(15, $result);
      }
      
      public function test_it_subtracts_two_numbers()
      {
          $calculator = new Calculator();
          $result = $calculator->subtract(15, 10);
          
          $this->assertEquals(5, $result);
      }
  }
  
  // Feature test example
  namespace YourVendor\YourPackage\Tests\Feature;
  
  use YourVendor\YourPackage\Tests\TestCase;
  use YourVendor\YourPackage\Models\Post;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  
  class PostControllerTest extends TestCase
  {
      use RefreshDatabase;
      
      public function test_it_creates_a_post()
      {
          $response = $this->postJson('/api/posts', [
              'title' => 'Test Title',
              'body' => 'Test content here',
          ]);
          
          $response->assertStatus(201)
              ->assertJsonStructure([
                  'id',
                  'title',
                  'body',
                  'created_at',
              ]);
                  
          $this->assertDatabaseHas('posts', [
              'title' => 'Test Title',
              'body' => 'Test content here',
          ]);
      }
  }
  ```

- **Testing with different Laravel versions**
  - Matrix testing across Laravel versions
  - Version-specific test cases
  - Dependency management for tests
  - Handling deprecated features
  - Compatibility layer strategies
  
  ```php
  // composer.json example with version constraints
  {
      "name": "your-vendor/your-package",
      "require": {
          "php": "^8.1",
          "illuminate/support": "^9.0|^10.0"
      },
      "require-dev": {
          "orchestra/testbench": "^7.0|^8.0",
          "phpunit/phpunit": "^9.5|^10.0"
      }
  }
  
  // GitHub workflow for testing multiple Laravel versions
  name: Tests
  
  on: [push, pull_request]
  
  jobs:
    test:
      runs-on: ubuntu-latest
      strategy:
        fail-fast: false
        matrix:
          php: [8.1, 8.2, 8.3]
          laravel: [9.*, 10.*]
          dependency-version: [prefer-lowest, prefer-stable]
          include:
            - laravel: 10.*
              testbench: 8.*
            - laravel: 9.*
              testbench: 7.*
          exclude:
            - laravel: 10.*
              php: 8.0
  
      name: P${{ matrix.php }} - L${{ matrix.laravel }} - ${{ matrix.dependency-version }}
  
      steps:
        - name: Checkout code
          uses: actions/checkout@v3
  
        - name: Setup PHP
          uses: shivammathur/setup-php@v2
          with:
            php-version: ${{ matrix.php }}
            extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath
            coverage: xdebug
  
        - name: Install dependencies
          run: |
            composer require "laravel/framework:${{ matrix.laravel }}" "orchestra/testbench:${{ matrix.testbench }}" --no-interaction --no-update
            composer update --${{ matrix.dependency-version }} --prefer-dist --no-interaction
  
        - name: Execute tests
          run: vendor/bin/phpunit --coverage-clover=coverage.xml
  ```

- **CI/CD for packages**
  - GitHub Actions workflow templates
  - Automated test runs on pull requests
  - Deployment to Packagist
  - Release version management
  - Semantic versioning automation
  
  ```yaml
  # GitHub Actions workflow for release automation
  name: Release
  
  on:
    push:
      tags:
        - "v*"
  
  jobs:
    tests:
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v3
        - uses: shivammathur/setup-php@v2
          with:
            php-version: 8.2
            coverage: xdebug
        - run: composer install --prefer-dist --no-interaction
        - run: vendor/bin/phpunit
  
    build-docs:
      needs: tests
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v3
        - name: Build API docs
          run: |
            composer require phpdocumentor/phpdocumentor
            vendor/bin/phpdoc -d src/ -t docs/api/
        - name: Deploy docs
          uses: peaceiris/actions-gh-pages@v3
          with:
            github_token: ${{ secrets.GITHUB_TOKEN }}
            publish_dir: ./docs
  
    release:
      needs: [tests, build-docs]
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v3
          with:
            fetch-depth: 0
  
        - name: Build Changelog
          id: github_release
          uses: metcalfc/changelog-generator@v4.0.1
          with:
            myToken: ${{ secrets.GITHUB_TOKEN }}
  
        - name: Create GitHub Release
          uses: actions/create-release@v1
          env:
            GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          with:
            tag_name: ${{ github.ref }}
            release_name: Release ${{ github.ref }}
            body: |
              ${{ steps.github_release.outputs.changelog }}
            draft: false
            prerelease: false
  ```