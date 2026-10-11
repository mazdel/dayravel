<?php

namespace Mazdel\DayRavel\Tests\Feature;

use Illuminate\Support\Facades\File;
use Mazdel\DayRavel\Tests\TestCase;

class ModuleCommandsTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory(app_path('Modules'));

        parent::tearDown();
    }

    public function test_it_creates_an_api_module(): void
    {
        $this->artisan('module:init', ['moduleName' => 'Catalog', '--only-api' => true])
            ->assertSuccessful();

        $modulePath = app_path('Modules/Catalog');

        $this->assertFileExists($modulePath . '/Controllers/MainController.php');
        $this->assertFileExists($modulePath . '/Models');
        $this->assertFileExists($modulePath . '/Validations');
        $this->assertFileExists($modulePath . '/Routes/api.php');
        $this->assertFileDoesNotExist($modulePath . '/Routes/web.php');
        $this->assertStringContainsString('namespace App\\Modules\\Catalog\\Controllers;', File::get($modulePath . '/Controllers/MainController.php'));
    }

    public function test_it_creates_module_models_from_the_package_stub(): void
    {
        File::ensureDirectoryExists(app_path('Modules/Catalog'));

        $this->artisan('module:make:model', ['module' => 'Catalog', 'modelName' => 'Product'])
            ->assertSuccessful();

        $model = File::get(app_path('Modules/Catalog/Models/Product.php'));

        $this->assertStringContainsString('namespace App\\Modules\\Catalog\\Models;', $model);
        $this->assertStringContainsString("protected \$table = 'products';", $model);
    }

    public function test_it_registers_api_module_routes_with_the_configured_group(): void
    {
        File::ensureDirectoryExists(app_path('Modules/Catalog/Routes'));
        File::put(app_path('Modules/Catalog/Routes/api.php'), "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::get('products', fn () => 'products')->name('products');\n");

        $this->refreshApplication();

        $route = collect($this->app['router']->getRoutes()->getRoutes())
            ->first(fn($route) => $route->getName() === 'api.v1.products');

        $this->assertNotNull($route);
        $this->assertSame('api/v1/products', $route->uri());
    }
}
