<?php

namespace LaravelCommon\App\Routes;

use Illuminate\Support\Facades\Route;
use LaravelCommon\App\Http\Controllers\ScopeController;

class ScopeRoute extends CommonRoute
{
    public static function register()
    {
        Route::prefix('api')->group(function () {
            Route::get('/scopes', [ScopeController::class, 'getAll']);
        });
    }
}
