<?php

namespace LaravelCommon\App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use LaravelCommon\App\Console\Commands\CreateLoggingName;
use LaravelCommon\App\Console\Commands\CreateScope;
use LaravelCommon\App\Console\Commands\EnableLoggingName;
use LaravelCommon\App\Console\Commands\GenerateEntity;
use LaravelCommon\App\Http\Middleware\CheckScope;
use LaravelCommon\App\Http\Middleware\CheckToken;
use LaravelCommon\App\Http\Middleware\ApiResponseMiddleware;
use LaravelCommon\App\Http\Middleware\ModelUnit;
use LaravelCommon\App\Http\Middleware\Hydrators\UserHydrator;
use LaravelCommon\App\Http\Middleware\ResourceValidation;
use LaravelCommon\System\Database\Schema\Blueprint as SchemaBlueprint;
use Illuminate\Contracts\Http\Kernel;

class CommonAppServiceProvider extends ServiceProvider
{
    public $bindings = [
        Blueprint::class => SchemaBlueprint::class
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/migrations');

        $this->loadRoutesFrom(__DIR__ . '/../../Routes/api.php');

        $this->publishConfig();

        $this->registerMiddleware();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateLoggingName::class,
                CreateScope::class,
                EnableLoggingName::class,
                GenerateEntity::class
            ]);
        }
    }

    /**
     * Publish all config
     *
     * @return void
     */
    private function publishConfig()
    {
        $this->publishes([

            __DIR__ . '/../../Config/common-config.php' => config_path('common-config.php'),
        ], 'laravel-common-config');
    }

    /**
     * register middlewares
     *
     * @return void
     */
    private function registerMiddleware()
    {
        $router = $this->app['router'];

        $router->aliasMiddleware(ApiResponseMiddleware::NAME, ApiResponseMiddleware::class);
        $router->aliasMiddleware(CheckToken::NAME, CheckToken::class);
        $router->aliasMiddleware(CheckScope::NAME, CheckScope::class);
        $router->aliasMiddleware(ModelUnit::NAME, ModelUnit::class);
        $router->aliasMiddleware(ResourceValidation::NAME, ResourceValidation::class);
        $router->aliasMiddleware(UserHydrator::NAME, UserHydrator::class);

        // Apply middleware to the 'api' middleware group
    //    $router->middlewareGroup('api', [
    //        ApiResponseMiddleware::class,
    //    ]);
        // $router->middleware('api', [
        //     ApiResponseMiddleware::class,
        // ]);

        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(ApiResponseMiddleware::class);
    }
}
