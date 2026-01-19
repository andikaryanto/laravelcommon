<?php

namespace LaravelCommon\App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use LaravelCommon\App\Console\Commands\CreateLoggingName;
use LaravelCommon\App\Console\Commands\CreateScope;
use LaravelCommon\App\Console\Commands\EnableLoggingName;
use LaravelCommon\App\Console\Commands\GenerateEntity;
use LaravelCommon\App\Http\Middleware\CheckScopeMiddleware;
use LaravelCommon\App\Http\Middleware\CheckTokenMiddleware;
use LaravelCommon\App\Http\Middleware\ApiResponseMiddleware;
use LaravelCommon\App\Http\Middleware\UnitOfWorkMiddleware;
use LaravelCommon\App\Http\Middleware\Hydrators\UserHydratorMiddleware;
use LaravelCommon\App\Http\Middleware\ResourceValidationMiddleware;
use LaravelCommon\System\Database\Schema\Blueprint as SchemaBlueprint;
use Illuminate\Contracts\Http\Kernel;
use LaravelCommon\App\Services\IncomingRequestService;
use LaravelCommon\System\Http\Request;
use LaravelCommon\Utilities\Database\UnitOfWork as DatabaseUnitOfWork;

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
        $this->app->singleton(DatabaseUnitOfWork::class, function ($app) {
            $incomingRequestService = $app->make(IncomingRequestService::class);
            return new DatabaseUnitOfWork($incomingRequestService);
        });
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
        $router->aliasMiddleware(CheckTokenMiddleware::NAME, CheckTokenMiddleware::class);
        $router->aliasMiddleware(CheckScopeMiddleware::NAME, CheckScopeMiddleware::class);
        $router->aliasMiddleware(UnitOfWorkMiddleware::NAME, UnitOfWorkMiddleware::class);
        $router->aliasMiddleware(ResourceValidationMiddleware::NAME, ResourceValidationMiddleware::class);
        $router->aliasMiddleware(UserHydratorMiddleware::NAME, UserHydratorMiddleware::class);

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
