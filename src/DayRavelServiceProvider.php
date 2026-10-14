<?php

namespace Mazdel\DayRavel;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Mazdel\DayRavel\Commands\ModuleInit;
use Mazdel\DayRavel\Commands\ModuleMakeController;
use Mazdel\DayRavel\Commands\ModuleMakeMiddleware;
use Mazdel\DayRavel\Commands\ModuleMakeModel;
use Mazdel\DayRavel\Commands\ModuleMakeRequest;
use Mazdel\DayRavel\Commands\ModuleMakeTrait;
use Mazdel\DayRavel\Commands\ModuleMakeValidation;
use Mazdel\DayRavel\Commands\ModuleTemplateGet;
use Mazdel\DayRavel\Commands\ModuleTemplateList;

class DayRavelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/dayravel.php', 'dayravel');
        // $this->mergeConfigFrom(__DIR__ . '/../config/view.php', 'view');
        $this->mergeConfigFrom(__DIR__ . '/../config/cors.php', 'cors');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/dayravel.php' => config_path('dayravel.php'),
            // __DIR__ . '/../config/view.php' => config_path('view.php'),
            __DIR__ . '/../config/cors.php' => config_path('cors.php'),
        ], 'dayravel-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleInit::class,
                ModuleMakeController::class,
                ModuleMakeMiddleware::class,
                ModuleMakeModel::class,
                ModuleMakeRequest::class,
                ModuleMakeTrait::class,
                ModuleMakeValidation::class,
                ModuleTemplateGet::class,
                ModuleTemplateList::class,
            ]);
        }

        $this->loadModuleRoutes();
        $this->loadModuleViews();
    }

    private function loadModuleRoutes(): void
    {
        $modulesPath = config('dayravel.modules_path', app_path('Modules'));

        foreach (File::glob($modulesPath . '/*/Routes/web.php') as $route) {
            $this->app['router']->middleware(config('dayravel.routes.web.middleware'))
                ->group($route);
        }

        $apiRoutes = config('dayravel.routes.api');
        foreach ($apiRoutes as $apiRoute) {
            foreach (File::glob($modulesPath . "/*/Routes/{$apiRoute['filename']}.php") as $route) {
                $this->app['router']->prefix("api/{$apiRoute['version']}")
                    ->as("api.{$apiRoute['version']}.")
                    ->middleware($apiRoute['middleware'])
                    ->group($route);
            }
        }
    }

    private function loadModuleViews(): void
    {
        $modulesPath = config('dayravel.modules_path', app_path('Modules'));

        foreach (File::glob($modulesPath . '/*/views') as $viewsPath) {
            $moduleName = basename(dirname($viewsPath));

            $this->loadViewsFrom($viewsPath, $moduleName);
        }
    }
}
