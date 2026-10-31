---
name: mazdel-dayravel
description: 'Use when creating, structuring, or maintaining modular code using the mazdel/dayravel package. Covers scaffolding modules (module:init), generating modular classes (controllers, models, requests, middleware, traits), auto-discovery rules for web and API routing, and namespaced Blade view resolution.'
license: MIT
metadata:
  author: mazdel
---

# Mazdel DayRavel Modular Architecture

Guidelines and conventions for building modular applications in Laravel using the `mazdel/dayravel` package.

---

## 0. Setup & Configuration Check (Pre-flight)

Before working with modules, scaffolding classes, or adjusting routing behavior:

1. **Check if `config/dayravel.php` exists**:
   - Inspect whether `config/dayravel.php` is present in the application root.
2. **Publish configuration if not yet published**:
   - If `config/dayravel.php` is missing, publish the provider's configuration immediately:
     ```bash
     php artisan vendor:publish --tag=dayravel-config
     ```
   - Alternatively by provider:
     ```bash
     php artisan vendor:publish --provider="Mazdel\DayRavel\DayRavelServiceProvider"
     ```
   - This publishes `config/dayravel.php` (and optionally `config/cors.php`), enabling explicit control over `modules_path`, web route middleware, and API route grouping (`version`, `filename`, `middleware`).

---

## 1. Core Concepts & Auto-Discovery

`mazdel/dayravel` implements a modular structure under `app/Modules/<ModuleName>/`. The package automatically handles:

1. **Web Routes Auto-Discovery**:
   - Files matching `app/Modules/*/Routes/web.php` are auto-loaded.
   - Wrapped with middleware group: `config('dayravel.routes.web.middleware')` (default: `['web']`).
   - No automatic URL prefix is enforced for web routes (define paths explicitly in `Routes/web.php`).

2. **API Routes Auto-Discovery**:
   - Files matching `app/Modules/*/Routes/{filename}.php` (default: `api.php`) are auto-loaded.
   - Automatically wrapped with:
     - URL Prefix: `api/{version}` (default: `api/v1`)
     - Route Name Prefix: `api.{version}.` (default: `api.v1.`)
     - Middleware: `config('dayravel.routes.api.*.middleware')` (default: `['api']`)
   - **Important**: Do **not** duplicate `/api` or `/v1` in `Routes/api.php`. A route defined as `Route::get('/items', ...)->name('items.index')` automatically becomes `GET /api/v1/items` with route name `api.v1.items.index`.

3. **Views Namespacing**:
   - Folders matching `app/Modules/*/views/` are auto-registered using the module name as the Blade namespace.
   - Example: A view at `app/Modules/Inventory/views/dispense.blade.php` is rendered using:
     ```php
     return view('Inventory::dispense', $data);
     ```

---

## 2. Directory Structure Conventions

Standard anatomy of a module managed with `mazdel/dayravel`:

```text
app/Modules/
└── <ModuleName>/
    ├── Controllers/
    │   ├── MainController.php
    │   └── Api/                 # Dedicated API controllers (optional)
    ├── Models/
    ├── Requests/                # Form requests
    ├── Validations/             # Custom validation classes
    ├── Middlewares/             # Module-scoped middleware (plural by DayRavel convention)
    ├── Traits/                  # Reusable domain traits
    ├── Routes/
    │   ├── web.php              # Auto-discovered web routes
    │   └── api.php              # Auto-discovered API routes (api/v1/*)
    └── views/                   # Views rendered via ModuleName::view-name
        └── index.blade.php
```

---

## 3. Artisan Commands

Always use DayRavel commands when scaffolding modular components:

### A. Initialize a Module

```bash
# Full module (Web + API + Views + Controller)
php artisan module:init <ModuleName>

# API-only module (No web routes or views, uses API controller stub)
php artisan module:init <ModuleName> --only-api

# Web-only module (No API routes)
php artisan module:init <ModuleName> --only-web
```

### B. Generate Classes Inside a Module

All generator commands require the target `<ModuleName>` as the first argument:

```bash
# Controllers (default name is MainController if omitted)
php artisan module:make:controller <ModuleName> <ControllerName>
php artisan module:make:controller <ModuleName> <ControllerName> --api

# Models
php artisan module:make:model <ModuleName> <ModelName>

# Form Requests
php artisan module:make:request <ModuleName> <RequestName>
php artisan module:make:request <ModuleName> <RequestName> --api

# Validations
php artisan module:make:validation <ModuleName> <ValidationName>
php artisan module:make:validation <ModuleName> <ValidationName> --api

# Middleware & Traits
php artisan module:make:middleware <ModuleName> <MiddlewareName>
php artisan module:make:trait <ModuleName> <TraitName>
```

### C. Templates (Optional)

```bash
# List available remote templates
php artisan module:template:ls

# Fetch remote template
php artisan module:template:get <template-name>
```

---

## 4. Best Practices & Rules

1. **Keep Root `routes/api.php` and `routes/web.php` Lean**:
   - Do not register module routes in the root files.
   - Let `DayRavelServiceProvider` discover routes automatically from `app/Modules/*/Routes/`.
2. **Follow Route Name Consistency**:
   - For web routes: Use conventional dot-notation e.g. `Route::get('/inventory/dispense', ...)->name('inventory.dispense')`.
   - For API routes: Use relative names e.g. `Route::get('/medicines', ...)->name('medicines.index')`. DayRavel automatically prefixes this as `api.v1.medicines.index`.
3. **Module View Rendering**:
   - Always reference module views with `ModuleName::view_name`.
   - Do not place global layout views inside modules; reference shared layouts (e.g. `layout.blade.php` in `resources/views/`) from module views using `@extends('layout')`.
   - Keep `resources/views/stubs/view.blade.stub` in the application so that `php artisan module:init` uses the standardized application view layout template rather than failing when `welcome.blade.php` is absent.
4. **Middlewares Naming Convention**:
   - DayRavel generates and expects module-scoped middleware under the plural folder `app/Modules/<ModuleName>/Middlewares/` with namespace `App\Modules\<ModuleName>\Middlewares`.
5. **Targeting Non-Existent Modules**:
   - `module:make:*` commands will fail if the module directory does not exist. Ensure the module is created with `module:init <ModuleName>` first.
