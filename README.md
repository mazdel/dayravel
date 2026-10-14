# Laravel Modular

A Laravel package for scaffolding modules and automatically loading their web and API route files.

## Requirements

- PHP 8.4+
- Laravel 11, 12, or 13

## Installation

```bash
composer require mazdel/dayravel
```

Laravel discovers the package service provider automatically. Publish the configuration before changing the module path or route groups:

```bash
php artisan vendor:publish --tag=dayravel-config
```

This creates `config/dayravel.php` in your application.

## Commands

### Create a module

Create a module with both web and API routes:

```bash
php artisan module:init Catalog
```

Create an API-only or web-only module:

```bash
php artisan module:init Catalog --only-api
php artisan module:init Catalog --only-web
```

By default, a module is created in `app/Modules/<ModuleName>` with `Controllers`, `Models`, `Validations`, and `Routes` directories. Web modules also receive a `views` directory and an `index.blade.php` view. `module:init` creates a `MainController` automatically; use `--only-api` to create it from the API controller stub.

### Generate module classes

All generators require an existing module name as their first argument.

```bash
# The controller name is optional and defaults to MainController.
php artisan module:make:controller Catalog ProductController
php artisan module:make:controller Catalog ProductController --api

php artisan module:make:model Catalog Product

php artisan module:make:request Catalog StoreProductRequest
php artisan module:make:request Catalog StoreProductRequest --api

php artisan module:make:validation Catalog ProductValidation
php artisan module:make:validation Catalog ProductValidation --api

php artisan module:make:middleware Catalog EnsureCatalogAccess
php artisan module:make:trait HasCatalog
```

The `--api` option selects the API stub for controllers, requests, and validations.

## Routing and configuration

The package loads each module's `Routes/web.php` and `Routes/api.php` files beneath the configured modules path. Web routes use the configured web middleware; API routes use the configured prefix, route-name prefix, and API middleware.

The published `config/dayravel.php` file contains these defaults:

```php
return [
    // The base directory in which modules are created and discovered.
    'modules_path' => app_path('Modules'),

    'routes' => [

        // Settings applied to every module Routes/web.php file.
        'web' => [
            'middleware' => ['web'],
        ],

        // Settings applied to every module Routes/api.php file.
        'api' => [
            [
                'version' => 'v1',
                'filename' => 'api',
                'middleware' => ['api'],
            ],
        ],
    ],
];
```

For example, a route named `products.index` in a module's API route file is available as `api.v1.products.index` by default, and its URI starts with `/api/v1`.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
