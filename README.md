<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Modular Laravel Application

This project demonstrates how to organize a Laravel application using internal packages for modular code organization.

## Project Structure

The application is organized with internal packages in the `packages` directory:

```
packages/
  ├── blog/          # Blog functionality package
  │   ├── config/
  │   ├── database/
  │   ├── resources/
  │   ├── routes/
  │   ├── src/
  │   └── composer.json
  │
  └── admin/         # Admin functionality package
      ├── config/
      ├── database/
      ├── resources/
      ├── routes/
      ├── src/
      └── composer.json
```

## Key Benefits of Modular Organization

1. **Separation of Concerns**: Each package focuses on a specific feature set
2. **Maintainability**: Easier to maintain and understand isolated code
3. **Reusability**: Packages can be reused across multiple projects
4. **Scalability**: Easier to scale and extend functionality
5. **Testing**: Isolated packages are easier to test
6. **Team Collaboration**: Different teams can work on different packages

## How It Works

1. Each package has its own `composer.json` file defining its dependencies and PSR-4 autoloading
2. The main application's `composer.json` includes the packages as dependencies using path repositories
3. Service providers from each package are registered to bootstrap their functionality
4. Packages can have their own routes, views, controllers, models, and migrations

## Getting Started

1. Clone the repository
2. Install dependencies:
   ```
   composer install
   ```
3. Set up your environment file:
   ```
   cp .env.example .env
   php artisan key:generate
   ```
4. Run migrations and seeders:
   ```
   php artisan migrate
   php artisan db:seed
   ```
5. Start the development server:
   ```
   php artisan serve
   ```

## Creating Your Own Package

To create a new package:

1. Create a new directory in the `packages` folder
2. Set up a `composer.json` file with appropriate namespacing
3. Create a service provider for your package
4. Add your package to the main application's `composer.json`
5. Run `composer require app/your-package-name`

## Example Use Cases

This modular approach is particularly beneficial for:

- Large applications with multiple feature sets
- Applications that need to share code across multiple projects
- Teams with specialized developers working on different parts of the application
- Applications that may need to spin off certain features into their own projects in the future

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
