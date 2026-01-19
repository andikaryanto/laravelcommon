<?php

use Illuminate\Support\Facades\Route;
use LaravelCommon\App\Http\Controllers\AuthController;
use LaravelCommon\App\Http\Controllers\UserController;
use LaravelCommon\App\Http\Middleware\ApiResponseMiddleware;
use LaravelCommon\App\Http\Middleware\CheckTokenMiddleware;
use LaravelCommon\App\Http\Middleware\Hydrators\UserHydratorMiddleware;
use LaravelCommon\App\Http\Middleware\UnitOfWorkMiddleware;
use LaravelCommon\App\Routes\GroupuserRoute;
use LaravelCommon\App\Routes\ScopeRoute;

Route::middleware([ApiResponseMiddleware::class])->group(function () {
    Route::prefix('api')->group(function () {
        Route::post('/auth/generate_token', [AuthController::class, 'generateToken']);

        Route::middleware([CheckTokenMiddleware::class])->group(function () {
            Route::get('/users', [UserController::class, 'getAll']);
            Route::prefix('user')->group(function () {
                Route::post('', [UserController::class, 'store'])
                    ->middleware(
                        UserHydratorMiddleware::class . ':post',
                        UnitOfWorkMiddleware::class . ':persist'
                    );
            });
        });

        Route::post('/register-market-organizer', [UserController::class]);
    });
});

GroupuserRoute::register();
ScopeRoute::register();
